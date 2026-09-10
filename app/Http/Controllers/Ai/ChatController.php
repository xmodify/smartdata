<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AiChatSession;
use App\Models\AiChatMessage;
use App\Models\AiKnowledgeDoc;
use App\Services\Ai\AiManager;
use App\Services\Ai\TextToSqlService;
use App\Services\Ai\VectorRagService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Exception;

class ChatController extends Controller
{
    protected TextToSqlService $sqlService;
    protected VectorRagService $ragService;

    public function __construct(TextToSqlService $sqlService, VectorRagService $ragService)
    {
        $this->sqlService = $sqlService;
        $this->ragService = $ragService;
    }

    /**
     * Main Chat UI
     */
    public function index(Request $request)
    {
        if (!\App\Models\AiSetting::isCopilotEnabled()) {
            return redirect()->route('ai.knowledge.index')->with('warning', 'ระบบ SmartData Copilot ปิดให้บริการชั่วคราวโดยผู้ดูแลระบบ');
        }

        if (!auth()->check() || !auth()->user()->hasAccessCopilot()) {
            return redirect()->route('ai.knowledge.index')->with('warning', 'คุณไม่มีสิทธิ์เข้าใช้งานระบบ SmartData Copilot กรุณาติดต่อผู้ดูแลระบบ');
        }

        $userId = auth()->id();
        $sessions = AiChatSession::where('user_id', $userId)
            ->orderBy('updated_at', 'desc')
            ->take(25)
            ->get();

        $currentSession = null;
        $sessionUuid = $request->query('session');

        if ($sessionUuid) {
            $currentSession = AiChatSession::where('session_uuid', $sessionUuid)
                ->where('user_id', $userId)
                ->with('messages')
                ->first();
        }

        if (!$currentSession) {
            $currentSession = $sessions->first();
        }

        if (!$currentSession) {
            $currentSession = AiChatSession::create([
                'user_id' => $userId,
                'session_uuid' => (string) Str::uuid(),
                'title' => 'การสนทนาใหม่',
                'target_db' => 'hosxp',
            ]);
            $sessions = collect([$currentSession]);
        }

        $docs = AiKnowledgeDoc::where('status', 'indexed')->select(['id', 'title'])->get();

        return view('ai.chat', compact('sessions', 'currentSession', 'docs'));
    }

    /**
     * Handle incoming chat message from AJAX
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'session_uuid' => 'required|string',
        ]);

        if (!\App\Models\AiSetting::isCopilotEnabled()) {
            return response()->json([
                'success' => false,
                'content' => 'ระบบ SmartData Copilot ปิดให้บริการชั่วคราวโดยผู้ดูแลระบบ'
            ], 403);
        }

        if (!auth()->check() || !auth()->user()->hasAccessCopilot()) {
            return response()->json([
                'success' => false,
                'content' => 'คุณไม่มีสิทธิ์เข้าใช้งานระบบ SmartData Copilot กรุณาติดต่อผู้ดูแลระบบ'
            ], 403);
        }

        $userId = auth()->id();
        $messageText = trim($request->input('message'));
        $sessionUuid = $request->input('session_uuid');
        $mode = $request->input('mode', 'smart'); // smart, sql, rag, general
        $targetDb = $request->input('target_db', 'auto'); // auto, hosxp, backoffice, mysql
        $docId = $request->filled('doc_id') ? (int) $request->input('doc_id') : null;

        // 1. User Anti-Spam / In-Flight Lock: Prevent double clicks or rapid spamming by the same user
        $userLock = Cache::lock("copilot_user_active_{$userId}", 45);
        if (!$userLock->get()) {
            return response()->json([
                'success' => false,
                'content' => 'ระบบกำลังประมวลผลคำถามก่อนหน้าของคุณอยู่ กรุณารอสักครู่ครับ ⏳'
            ], 429);
        }

        // 2. System-wide Concurrency Limiter: Limit simultaneous AI queries across the entire hospital server
        $maxConcurrent = (int) \App\Models\AiSetting::get('copilot_max_concurrent', 5);
        if ($maxConcurrent < 1) {
            $maxConcurrent = 5;
        }

        $slotLock = null;
        $waitStart = microtime(true);
        $maxWaitSeconds = 6.0; // Wait up to 6 seconds for a slot to free up

        while ((microtime(true) - $waitStart) < $maxWaitSeconds) {
            for ($i = 1; $i <= $maxConcurrent; $i++) {
                $candidate = Cache::lock("copilot_concurrency_slot_{$i}", 45);
                if ($candidate->get()) {
                    $slotLock = $candidate;
                    break 2;
                }
            }
            usleep(200000); // Backoff 200ms
        }

        if (!$slotLock) {
            $userLock->release();
            return response()->json([
                'success' => false,
                'content' => 'ขณะนี้มีผู้ใช้งาน SmartData Copilot พร้อมกันจำนวนมาก ระบบกำลังจัดคิวให้บริการ กรุณารอสักครู่ (ประมาณ 3-5 วินาที) แล้วลองส่งใหม่อีกครั้งครับ 🙏 ⏳'
            ], 429);
        }

        try {
            $session = AiChatSession::firstOrCreate(
                ['session_uuid' => $sessionUuid, 'user_id' => $userId],
                ['title' => mb_substr($messageText, 0, 35) . '...', 'target_db' => $targetDb]
            );

        // Load recent conversation messages before this new message for context
        $recentMessages = $session->messages()
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get()
            ->reverse()
            ->values();

        // Check if there was an active target_db from previous SQL query in this session
        $lastSqlMsg = $recentMessages->where('message_type', 'sql_query')->last();
        $previousDb = $lastSqlMsg?->target_db ?? ($session->target_db !== 'auto' ? $session->target_db : null);

        // Update session title if first message
        if ($session->messages()->count() === 0) {
            $session->update([
                'title' => mb_substr($messageText, 0, 35) . (mb_strlen($messageText) > 35 ? '...' : ''),
                'target_db' => $targetDb
            ]);
        }

        // Save User Message
        $userMsg = AiChatMessage::create([
            'session_id' => $session->id,
            'role' => 'user',
            'content' => $messageText,
            'message_type' => 'text',
            'target_db' => $targetDb
        ]);

        // Decide execution routing with multi-turn context
        $detectedMode = $mode;
        if ($mode === 'smart') {
            $detectedMode = $this->detectIntent($messageText, $session, $recentMessages);
        }

        if ($detectedMode === 'sql') {
                // Determine effective target database connection (with multi-turn inheritance)
                $effectiveTargetDb = $targetDb;
                if ($effectiveTargetDb === 'auto' && !empty($previousDb)) {
                    $hosxpSpecific = [
                        'คนไข้', 'ผู้ป่วย', 'opd', 'ipd', 'er', 'hn', 'vn', 'an', 'โรค', 'เตียง', 'คลินิก', 'วอร์ด', 'ยา', 'หมอ', 'แพทย์',
                        'admit', 'refer', 'ทันตกรรม', 'ฟัน', 'กายภาพ', 'แผนไทย', 'แพทย์แผนไทย', 'คลอด', 'ห้องคลอด', 'ฉุกเฉิน', 'แล็บ', 'lab',
                        'xray', 'ct scan', 'เสียชีวิต', 'นัด', 'ใบนัด', 'แพ้ยา', 'เบาหวาน', 'ความดัน', 'ไต', 'ครองเตียง', 'revisit', 'readmit', 'ems',
                        '43 แฟ้ม', '43แฟ้ม', '43 file', 'provis', 'provis_', 'tmt', 'billcode', 'adp', 'สิทธิการรักษา', 'กองทุน', 'person', 'typearea', 'type_area', 'หัตถการ', 'หมวดรายได้',
                        'pttype_price_group', 'price_group', 'price_type', 'กลุ่มราคา', 'ราคาตามสิทธิ', 'unitprice2', 'price2',
                        'pttype_items_price', 'ราคาเฉพาะสิทธิ', 'ตั้งราคาเฉพาะรายการ'
                    ];
                    $hasHosxpKeyword = false;
                    $qLower = mb_strtolower($messageText);
                    foreach ($hosxpSpecific as $hkw) {
                        if (mb_strpos($qLower, $hkw) !== false) {
                            $hasHosxpKeyword = true;
                            break;
                        }
                    }

                    $boSpecific = [
                        'พัสดุ', 'บุคลากร', 'พนักงาน', 'เจ้าหน้าที่', 'เงินเดือน', 'วันลา', 'ครุภัณฑ์', 'จัดซื้อ', 'จัดจ้าง',
                        'คลัง', 'คลังพัสดุ', 'คลังยา', 'สต็อก', 'คงคลัง', 'เบิกพัสดุ', 'เบิกยา', 'รับเข้าคลัง', 'จ่ายออกจากคลัง', 'คลังย่อย',
                        'ซ่อม', 'แจ้งซ่อม', 'คอมพิวเตอร์', 'ไอที', 'รถยนต์', 'ขอใช้รถ', 'ห้องประชุม', 'ความเสี่ยง', 'ค่าเสื่อม'
                    ];
                    $hasBoKeyword = false;
                    foreach ($boSpecific as $bkw) {
                        if (mb_strpos($qLower, $bkw) !== false) {
                            $hasBoKeyword = true;
                            break;
                        }
                    }

                    if ($previousDb === 'backoffice' && !$hasHosxpKeyword) {
                        $effectiveTargetDb = 'backoffice';
                    } elseif ($previousDb === 'hosxp' && !$hasBoKeyword) {
                        $effectiveTargetDb = 'hosxp';
                    }
                }

                // Prepare history payload for Text-to-SQL context
                $sqlHistory = [];
                foreach ($recentMessages->take(-4) as $m) {
                    $sqlHistory[] = [
                        'role' => $m->role,
                        'content' => $m->content,
                        'sql' => $m->generated_sql,
                        'target_db' => $m->target_db
                    ];
                }

                // Run Text-to-SQL with context and inherited DB
                $sqlResult = $this->sqlService->query($messageText, $effectiveTargetDb, $sqlHistory);

                if ($sqlResult['success']) {
                    $assistantMsg = AiChatMessage::create([
                        'session_id' => $session->id,
                        'role' => 'assistant',
                        'content' => $sqlResult['explanation'],
                        'message_type' => 'sql_query',
                        'generated_sql' => $sqlResult['sql'],
                        'query_result' => $sqlResult['rows'],
                        'target_db' => $sqlResult['target_db'],
                    ]);

                    $session->touch();

                    return response()->json([
                        'success' => true,
                        'mode' => 'sql',
                        'target_db' => $sqlResult['target_db'],
                        'content' => $sqlResult['explanation'],
                        'sql' => $sqlResult['sql'],
                        'columns' => $sqlResult['columns'],
                        'rows' => $sqlResult['rows'],
                        'count' => $sqlResult['count'],
                        'execution_ms' => $sqlResult['execution_ms'] ?? 0,
                    ]);
                } else {
                    // SQL execution returned error or empty
                    $displayMsg = $sqlResult['polite_message'] ?? $sqlResult['error'];
                    $rawError = $sqlResult['raw_error'] ?? null;

                    $assistantMsg = AiChatMessage::create([
                        'session_id' => $session->id,
                        'role' => 'assistant',
                        'content' => $displayMsg,
                        'message_type' => 'sql_query',
                        'generated_sql' => $sqlResult['sql'] ?? null,
                        'target_db' => $sqlResult['target_db'] ?? null,
                    ]);

                    return response()->json([
                        'success' => false,
                        'mode' => 'sql',
                        'content' => $displayMsg,
                        'sql' => $sqlResult['sql'] ?? null,
                        'raw_error' => $rawError,
                        'target_db' => $sqlResult['target_db'] ?? null,
                    ]);
                }
            } elseif ($detectedMode === 'rag') {
                // Run Vector RAG search
                $ragResult = $this->ragService->searchAndAnswer($messageText, $docId);

                $assistantMsg = AiChatMessage::create([
                    'session_id' => $session->id,
                    'role' => 'assistant',
                    'content' => $ragResult['answer'],
                    'message_type' => 'rag_result',
                    'sources' => $ragResult['sources']
                ]);

                $session->touch();

                return response()->json([
                    'success' => true,
                    'mode' => 'rag',
                    'content' => $ragResult['answer'],
                    'sources' => $ragResult['sources']
                ]);
            } else {
                // General Conversation
                $provider = AiManager::getActiveProvider();

                // Prepare recent conversation history for context
                $recentMsgs = $session->messages()
                    ->orderBy('created_at', 'desc')
                    ->take(6)
                    ->get()
                    ->reverse()
                    ->map(function ($m) {
                        return ['role' => $m->role, 'content' => $m->content];
                    })
                    ->toArray();

                $systemPrompt = "คุณคือ SmartData Copilot ผู้ช่วยอัจฉริยะประจำระบบ SmartData และโรงพยาบาล
คุณสามารถตอบคำถาม ให้คำปรึกษา แนะนำการใช้งานระบบสุขภาพ วิเคราะห์ข้อมูล และเขียน SQL ได้อย่างคล่องแคล่ว
ตอบคำถามด้วยภาษาไทยที่สุภาพ กระชับ เป็นมืออาชีพ

หากผู้ใช้สอบถามว่าคุณเข้าถึงตารางไหนได้บ้าง หรือสืบค้นข้อมูลอะไรได้บ้าง ให้สรุปแจกแจงอย่างเป็นระเบียบตามกลุ่มข้อมูล:
1. ฐานข้อมูล HOSxP (ระบบบริการผู้ป่วย, เวชระเบียน และการเงิน):
   - ผู้ป่วยและประชากร: `patient` (ข้อมูลทั่วไป HN, CID, เพศ, อายุ, วันเกิด, ที่อยู่), `hospcode` (สถานพยาบาล/รพ.สต.)
   - ผู้ป่วยนอก (OPD): `ovst` (การมารับบริการ, คิวตรวจ, แผนก), `vn_stat` (สถิติค่ารักษา, สิทธิการรักษา, โรคหลัก, Re-visit 48 ชม.), `opdscreen` (สัญญาณชีพ, อาการสำคัญแรกรับ CC), `ovstdiag` (รหัสโรค ICD-10 OPD), `kskdepartment` (แผนกตรวจ)
   - ผู้ป่วยใน (IPD): `ipt` (การรับ Admit, วันที่รับ/จำหน่าย, สถานะครองเตียง confirm_discharge='N'), `an_stat` (สถิติผู้ป่วยใน, วันนอน LOS, CMI, AdjRW, ค่าใช้จ่าย), `iptadm` (เตียง, ห้อง), `iptbedmove` (ประวัติย้ายเตียง/ICU), `iptdiag` (โรคผู้ป่วยใน), `ipt_doctor_diag` (แพทย์บันทึกวินิจฉัย/สรุปชาร์ต EMR), `ward` (หอผู้ป่วยและจำนวนเตียง bedcount)
   - บริการเฉพาะทาง: `dtmain` (ทันตกรรม), `physic_list` (กายภาพบำบัด), `health_med_service` (แพทย์แผนไทย), `ipt_pregnancy` (การคลอด ห้องคลอด LR), `person_anc_service` (ฝากครรภ์ ANC)
   - คลินิกโรคเรื้อรัง (NCD): `clinic` (รหัสคลินิก เช่น เบาหวาน, ความดัน, ไตเรื้อรัง CKD, สุขภาพจิต, COPD, Stroke), `clinicmember` (ทะเบียนผู้ป่วย NCD, ค่าน้ำตาล FBS, HbA1c, ความดันล่าสุด)
   - งานอุบัติเหตุ-ฉุกเฉิน: `er_regist` (ผู้ป่วย ER, เวลาเข้าตรวจ, แพทย์), `er_emergency_type` (ระดับความเร่งด่วน Triage 1-5), รถพยาบาลกู้ชีพ EMS
   - เภสัชกรรม ยา และการแพ้ยา: `drugitems` (คลังยา), `opitemrece` (รายการสั่งยาและค่าบริการ), `opd_allergy` (ประวัติแพ้ยา, ชื่อยา, อาการแพ้, ความรุนแรง)
   - ชันสูตรและรังสี: `lab_head` (ใบสั่งแล็บ), `nondrugitems` (หมวดรายได้ ค่าแล็บ ค่าบริการ ค่าอุปกรณ์), `xray_items` (เอ็กซเรย์, CT Scan)
   - การนัดหมายและส่งต่อ: `oapp` (ใบนัดตรวจ, คลินิก, แพทย์ผู้นัด), `referout` (ส่งต่อไป รพ. อื่น), `referin` (รับเข้า), `death` (ข้อมูลการเสียชีวิตใน รพ.), `surveil_member` (ระบาดวิทยา 506)
   - สิทธิการรักษาและการกำหนดกลุ่มราคา: `pttype` (สิทธิบัตรทอง, ข้าราชการ, ประกันสังคม, อปท., จ่ายเงินเอง, ระดับราคา price_type), `pttype_price_group` (กลุ่มราคาตามสิทธิ), `pttype_items_price` (ตารางกำหนดราคาค่ายาและค่ารักษาเฉพาะรายสิทธิ x รายการ icode มีความสำคัญสูงสุด Override), `visit_pttype` (สิทธิรายครั้ง รพ. ต้นสังกัด)
   - ข้อมูลพื้นฐานและการตั้งค่า (Master Data): `drugitems` (คลังยา, ระดับราคาขาย unitprice1-5, ราคาทุน unitcost, รหัส 24 หลัก TMT, บัญชียาหลัก ED/NED), `nondrugitems` (ค่าบริการ, ระดับราคา price1-5, หมวดรายได้, รหัสเบิกตรง Billcode, ADP Code สปสช.), `income` (หมวดรายได้หลัก 16 หมวด), `icd9cm1` (หัตถการ ICD-9-CM), `lab_items` (รายการตรวจแล็บ, ค่าอ้างอิงปกติ Reference Range), `doctor` (แพทย์, เลขที่ใบประกอบวิชาชีพ ว.), `spclty` (สาขาความเชี่ยวชาญ), `opduser` (ผู้ใช้งานระบบ HOSxP)
   - การส่งออก 43 แฟ้ม และตารางมาตรฐาน provis_: `provis_instype` (การเชื่อมโยงสิทธิ pttype กับมาตรฐาน 43 แฟ้ม PERSON/CHARGE), `provis_vaccine` (รหัสวัคซีนมาตรฐาน แฟ้ม EPI), `provis_ncd_clinic` (รหัสคลินิกเรื้อรัง แฟ้ม CHRONIC), `provis_lab_code` (รหัสแล็บติดตามเรื้อรัง แฟ้ม LABFU), `provis_fp_type` (รหัสวิธีคุมกำเนิด แฟ้ม FP), `provis_typedis` (ประเภทความพิการ แฟ้ม DISABILITY), `person` (ข้อมูลประชากรในเขตรับผิดชอบ แฟ้ม PERSON), `type_area` (สถานะการอยู่อาศัย Type Area 1-4)
2. ฐานข้อมูล Backoffice (ระบบบริหารงานโรงพยาบาล v5.6.1.1):
   - คลังพัสดุและเบิกจ่าย: `warehouse_store` (คลังหลัก), `warehouse_treasury` (คลังย่อย), `warehouse_request` / `warehouse_request_sub` (ใบขอเบิกและรายการขอเบิกพัสดุ), `warehouse_treasury_pay` (การตัดจ่ายพัสดุ), `warehouse_check_receive` (การตรวจรับเข้าคลัง)
   - คลังยาและเวชภัณฑ์: `medicine_drug` (ทะเบียนยา), `medicine_warehouse_items` (สต็อกคงคลังยา), `medicine_warehouse_request` / `medicine_warehouse_request_list` (ใบขอเบิกยา), `medicine_warehouse_receive` (การรับยาเข้าคลัง), `medicine_warehouse_export` (การจ่ายยาออกจากคลัง)
   - งานพัสดุและจัดซื้อ: `supplies` (พัสดุ/ครุภัณฑ์), `supplies_con` / `supplies_con_list` (สัญญาและรายการจัดซื้อจัดจ้าง), `supplies_vendor` (บริษัทคู่ค้า)
   - ทรัพย์สินและครุภัณฑ์: `asset_article` (ทะเบียนครุภัณฑ์), `asset_depreciate` (ค่าเสื่อมราคา), `asset_dispose` (แทงจำหน่าย)
   - ซ่อมบำรุงและศูนย์คอมฯ: `informrepair_index` (แจ้งซ่อมบำรุงทั่วไป), `informcom_repair` (แจ้งซ่อมคอมพิวเตอร์และอุปกรณ์ไอที), `informcom_service` (บริการศูนย์คอม)
   - ยานพาหนะและห้องประชุม: `vehicle_car_reserve` (ขอใช้รถส่วนกลาง), `vehicle_car_refer` (รถส่งต่อผู้ป่วย Refer), `meetingroom_service` (จองห้องประชุม)
   - บุคลากร/ลงเวลา/การลา: `hrd_person` (ข้อมูลเจ้าหน้าที่), `hrd_position` (ตำแหน่งสายงาน), `hrd_department` (กลุ่มงาน/ฝ่าย), `checkin_device_time_attendance` (บันทึกเวลาสแกนนิ้ว/ใบหน้า), `gleave_register` / `gleave_over` (ประวัติการลา), `salary_all` (เงินเดือนและค่าตอบแทน)
   - ความเสี่ยง: `risk_rep` (รายงานอุบัติการณ์ความเสี่ยง)
3. ฐานข้อมูล SmartData:
   - `users` (ผู้ใช้งาน), `lend_items` / `lend_transactions` (ระบบยืมคืนอุปกรณ์), `customer_complains` (ข้อร้องเรียน)";

                array_unshift($recentMsgs, ['role' => 'system', 'content' => $systemPrompt]);

                $reply = $provider->chat($recentMsgs);

                $assistantMsg = AiChatMessage::create([
                    'session_id' => $session->id,
                    'role' => 'assistant',
                    'content' => $reply,
                    'message_type' => 'text'
                ]);

                $session->touch();

                return response()->json([
                    'success' => true,
                    'mode' => 'general',
                    'content' => $reply
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'content' => 'ขออภัยครับ ระบบเกิดข้อขัดข้องชั่วคราวในการประมวลผล: ' . $e->getMessage()
            ], 500);
        } finally {
            $slotLock?->release();
            $userLock?->release();
        }
    }

    /**
     * Create new chat session
     */
    public function newSession(Request $request)
    {
        if (!\App\Models\AiSetting::isCopilotEnabled() || !auth()->check() || !auth()->user()->hasAccessCopilot()) {
            return response()->json(['success' => false, 'content' => 'คุณไม่มีสิทธิ์เข้าใช้งานระบบ SmartData Copilot'], 403);
        }

        $userId = auth()->id();
        $targetDb = $request->input('target_db', 'hosxp');

        $session = AiChatSession::create([
            'user_id' => $userId,
            'session_uuid' => (string) Str::uuid(),
            'title' => 'การสนทนาใหม่',
            'target_db' => $targetDb
        ]);

        return response()->json([
            'success' => true,
            'session_uuid' => $session->session_uuid,
            'title' => $session->title
        ]);
    }

    /**
     * Load messages for a session
     */
    public function loadSession(string $uuid)
    {
        if (!\App\Models\AiSetting::isCopilotEnabled() || !auth()->check() || !auth()->user()->hasAccessCopilot()) {
            return response()->json(['success' => false, 'content' => 'คุณไม่มีสิทธิ์เข้าใช้งานระบบ SmartData Copilot'], 403);
        }

        $session = AiChatSession::where('session_uuid', $uuid)
            ->where('user_id', auth()->id())
            ->with(['messages'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'session' => $session,
            'messages' => $session->messages
        ]);
    }

    /**
     * Delete session
     */
    public function deleteSession(string $uuid)
    {
        if (!\App\Models\AiSetting::isCopilotEnabled() || !auth()->check() || !auth()->user()->hasAccessCopilot()) {
            return response()->json(['success' => false, 'content' => 'คุณไม่มีสิทธิ์เข้าใช้งานระบบ SmartData Copilot'], 403);
        }

        $session = AiChatSession::where('session_uuid', $uuid)
            ->where('user_id', auth()->id())
            ->first();

        if ($session) {
            $session->messages()->delete();
            $session->delete();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Clear all chat sessions for current user
     */
    public function clearAllSessions()
    {
        if (!\App\Models\AiSetting::isCopilotEnabled() || !auth()->check() || !auth()->user()->hasAccessCopilot()) {
            return response()->json(['success' => false, 'content' => 'คุณไม่มีสิทธิ์เข้าใช้งานระบบ SmartData Copilot'], 403);
        }

        $userId = auth()->id();
        $sessions = AiChatSession::where('user_id', $userId)->get();

        foreach ($sessions as $session) {
            $session->messages()->delete();
            $session->delete();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Smart intent detector with multi-turn conversation context awareness
     */
    protected function detectIntent(string $text, ?AiChatSession $session = null, $recentMessages = null): string
    {
        $q = mb_strtolower(trim($text));

        // RAG Intent keywords (highest priority for guidelines, manuals, CPG, and policy docs)
        $ragKeywords = [
            'คู่มือ', 'ระเบียบ', 'แนวทาง', 'ขั้นตอน', 'cpg', 'เอกสาร', 'นโยบาย', 'เกณฑ์',
            'มาตรฐาน', 'วิธีปฏิบัติ', 'ประกาศ', 'คำสั่ง', 'ข้อกำหนด', 'นิยาม', 'หนังสือสั่งการ'
        ];

        foreach ($ragKeywords as $kw) {
            if (mb_strpos($q, $kw) !== false) {
                return 'rag';
            }
        }

        // Meta capability and schema overview questions -> General mode
        $metaKeywords = [
            'เข้าถึงตารางไหน', 'มีตารางอะไร', 'ตารางไหนได้บ้าง', 'ดึงตารางไหน', 'ดูตารางไหน',
            'สืบค้นตารางไหน', 'รายชื่อตาราง', 'ตารางทั้งหมด', 'ช่วยอะไรได้บ้าง', 'ทำอะไรได้บ้าง',
            'คุณคือใคร', 'คุณทำอะไรได้', 'สอบถามเรื่องอะไรได้บ้าง'
        ];
        foreach ($metaKeywords as $mkw) {
            if (mb_strpos($q, $mkw) !== false) {
                return 'general';
            }
        }

        // Broad SQL Intent keywords
        $sqlKeywords = [
            'กี่คน', 'กี่ราย', 'กี่ประเภท', 'กี่รายการ', 'กี่ใบ', 'กี่ตัว', 'กี่อัน', 'กี่เตียง', 'กี่ครั้ง', 'กี่เคส', 'กี่แห่ง',
            'จำนวน', 'สถิติ', 'ยอด', 'เท่าไหร่', 'รายชื่อ', 'คนไข้', 'ผู้ป่วย', 'บุคลากร', 'เจ้าหน้าที่', 'พนักงาน',
            'opd', 'ipd', 'er', 'vn', 'hn', 'an', 'โรค', 'icd', 'pttype', 'สิทธิ',
            'ค่ารักษา', 'วันนี้', 'เดือนนี้', 'ปีนี้', 'ปีงบ', 'refer', 'admit', 'เตียง',
            'ทันตกรรม', 'ทำฟัน', 'ฟัน', 'กายภาพ', 'กายภาพบำบัด', 'แผนไทย', 'แพทย์แผนไทย', 'คลอด', 'ห้องคลอด', 'ทารก',
            'ฉุกเฉิน', 'อุบัติเหตุ', 'ems', 'แล็บ', 'lab', 'xray', 'เอ็กซเรย์', 'ct scan', 'เสียชีวิต', 'ตาย',
            'นัด', 'ใบนัด', 'นัดหมาย', 'แพ้ยา', 'เบาหวาน', 'ความดัน', 'ไต', 'stroke', 'สโตรก', 'sepsis', 'หัวใจ', 'ปอด',
            'รีเฟอร์', 'ส่งต่อ', 'ครองเตียง', 'ชาร์ต', 'chart', 'revisit', 'readmit', 'cmi', 'adjrw', 'คิว', 'วัณโรค', 'หอบหืด',
            'พัสดุ', 'เงินเดือน', 'วันลา', 'ครุภัณฑ์', 'จัดซื้อ', 'จัดจ้าง', 'เบิกจ่าย', 'สต็อก', 'คงเหลือ',
            '43 แฟ้ม', '43แฟ้ม', 'provis', 'provis_', 'tmt', 'billcode', 'adp', 'eclaim', 'e-claim', 'csop', 'fdh',
            'สิทธิการรักษา', 'กองทุน', 'person', 'typearea', 'type_area', 'ค่ายา', 'ราคายา', 'หัตถการ', 'หมวดรายได้', 'เลข ว', 'ตั้งค่า',
            'pttype_price_group', 'price_group', 'price_type', 'กลุ่มราคา', 'ราคาตามสิทธิ', 'unitprice2', 'unitprice3', 'price2', 'price3', 'ราคานอกเวลา', 'ราคาต่างชาติ',
            'pttype_items_price', 'ราคาเฉพาะสิทธิ', 'ตั้งราคาเฉพาะรายการ',
            'แยกตาม', 'แบ่งตาม', 'ตามแผนก', 'ตามตำแหน่ง', 'ตามประเภท', 'ตามตึก', 'ตามวอร์ด', 'ตามกลุ่มงาน', 'ตามฝ่าย',
            'อันดับ', 'สูงสุด', 'ต่ำสุด', 'มากที่สุด', 'น้อยที่สุด', 'เฉลี่ย', 'รวมทั้งสิ้น', 'ทั้งหมด',
            'มีใครบ้าง', 'มีอะไรบ้าง', 'ใครบ้าง', 'อะไรบ้าง', 'ไหนบ้าง', 'คนไหน', 'ตึกไหน', 'ห้องไหน', 'กลุ่มไหน', 'ฝ่ายไหน'
        ];

        foreach ($sqlKeywords as $kw) {
            if (mb_strpos($q, $kw) !== false) {
                return 'sql';
            }
        }

        // Multi-turn context check:
        // If the session has a recent SQL query, short follow-ups or analytical questions should stay in SQL mode!
        if ($recentMessages && $recentMessages->isNotEmpty()) {
            $lastAssistant = $recentMessages->where('role', 'assistant')->last();
            if ($lastAssistant && $lastAssistant->message_type === 'sql_query') {
                $followUpPatterns = [
                    'กี่', 'ประเภท', 'กลุ่ม', 'แผนก', 'ฝ่าย', 'ตำแหน่ง', 'ใคร', 'อะไร', 'ไหน', 'แยก',
                    'ขอ', 'ดู', 'มี', 'แล้ว', 'สรุป', 'และ', 'อันดับ', 'top', 'ล่ะ', 'บ้าง', 'เท่าไร'
                ];
                foreach ($followUpPatterns as $pat) {
                    if (mb_strpos($q, $pat) !== false) {
                        return 'sql';
                    }
                }

                // If user typed a very short prompt in SQL mode (e.g. "พยาบาล", "ICU", "ปี 67"), treat as SQL follow-up
                if (mb_strlen($q) <= 25) {
                    return 'sql';
                }
            }
        }

        return 'general';
    }
}
