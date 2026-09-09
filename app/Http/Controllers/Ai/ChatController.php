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

        try {
            if ($detectedMode === 'sql') {
                // Determine effective target database connection (with multi-turn inheritance)
                $effectiveTargetDb = $targetDb;
                if ($effectiveTargetDb === 'auto' && !empty($previousDb)) {
                    $hosxpSpecific = ['คนไข้', 'ผู้ป่วย', 'opd', 'ipd', 'er', 'hn', 'vn', 'an', 'โรค', 'เตียง', 'คลินิก', 'วอร์ด', 'ยา', 'หมอ', 'แพทย์', 'admit', 'refer'];
                    $hasHosxpKeyword = false;
                    $qLower = mb_strtolower($messageText);
                    foreach ($hosxpSpecific as $hkw) {
                        if (mb_strpos($qLower, $hkw) !== false) {
                            $hasHosxpKeyword = true;
                            break;
                        }
                    }

                    $boSpecific = ['พัสดุ', 'บุคลากร', 'พนักงาน', 'เจ้าหน้าที่', 'เงินเดือน', 'วันลา', 'ครุภัณฑ์', 'จัดซื้อ', 'จัดจ้าง'];
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
                    // SQL execution returned error
                    $errorMsg = $sqlResult['error'];
                    $assistantMsg = AiChatMessage::create([
                        'session_id' => $session->id,
                        'role' => 'assistant',
                        'content' => $errorMsg,
                        'message_type' => 'text',
                        'generated_sql' => $sqlResult['sql'] ?? null,
                        'target_db' => $sqlResult['target_db'] ?? null,
                    ]);

                    return response()->json([
                        'success' => false,
                        'mode' => 'sql',
                        'content' => $errorMsg,
                        'sql' => $sqlResult['sql'] ?? null,
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
1. ฐานข้อมูล HOSxP (ระบบบริการผู้ป่วยและเวชระเบียน):
   - ผู้ป่วยและประชากร: `patient` (ข้อมูลทั่วไป HN, เพศ, อายุ, วันเกิด, ที่อยู่)
   - ผู้ป่วยนอก (OPD): `ovst` (การมารับบริการ, แผนกที่ตรวจ), `vn_stat` (สถิติค่ารักษา, สิทธิการรักษา, โรคหลัก), `ovstdiag` (รหัสโรคและประเภทวินิจฉัย)
   - ผู้ป่วยใน (IPD): `ipt` (การรับ Admit, วันที่รับ/จำหน่าย, สถานะจำหน่าย), `iptadm` (เตียง, ห้อง), `an_stat` (สถิติผู้ป่วยใน), `iptdiag` (การวินิจฉัยโรค IPD), `ward` (หอผู้ป่วยและจำนวนเตียง bedcount)
   - งานอุบัติเหตุ-ฉุกเฉิน: `er_regist` (ผู้ป่วย ER, ระดับความเร่งด่วน)
   - การส่งต่อผู้ป่วย: `referout` (ส่งต่อไป รพ. อื่น, รหัส รพ. ปลายทาง, การใช้รถพยาบาล)
   - คลินิกและสิทธิ: `clinic` (คลินิก/แผนก), `pttype` (สิทธิการรักษา)
   - รหัสโรคและยา: `icd101` (พจนานุกรมรหัสโรค ICD-10), `drugitems` (คลังยา), `opitemrece` (รายการยาและค่าบริการ)
   - บุคลากร: `doctor` (แพทย์และผู้ตรวจ)
2. ฐานข้อมูล Backoffice (งานบริหารโรงพยาบาล):
   - บุคลากร: `hrd_person` (ข้อมูลเจ้าหน้าที่), `hrd_position` (ตำแหน่งสายงาน), `hrd_department` (กลุ่มงาน/ฝ่าย)
   - การลา: `hrd_leave_over` (ประวัติการลา), `gleave_type` (ประเภทวันลา เช่น ลาป่วย ลากิจ ลาพักผ่อน)
   - พัสดุ: `supplies` (ครุภัณฑ์และพัสดุ), `supplies_types` (ประเภทครุภัณฑ์)
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
                'content' => 'เกิดข้อผิดพลาดในการประมวลผล: ' . $e->getMessage()
            ], 500);
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
            'พัสดุ', 'เงินเดือน', 'วันลา', 'ครุภัณฑ์', 'จัดซื้อ', 'จัดจ้าง', 'เบิกจ่าย', 'สต็อก', 'คงเหลือ',
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
