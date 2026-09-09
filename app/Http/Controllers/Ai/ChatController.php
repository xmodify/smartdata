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
        if (!\App\Models\AiSetting::isCopilotEnabled() && auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'ระบบ SmartData Copilot ปิดให้บริการชั่วคราวโดยผู้ดูแลระบบ');
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

        if (!\App\Models\AiSetting::isCopilotEnabled() && auth()->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'content' => 'ระบบ SmartData Copilot ปิดให้บริการชั่วคราวโดยผู้ดูแลระบบ'
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

        // Decide execution routing
        $detectedMode = $mode;
        if ($mode === 'smart') {
            $detectedMode = $this->detectIntent($messageText);
        }

        try {
            if ($detectedMode === 'sql') {
                // Run Text-to-SQL
                $sqlResult = $this->sqlService->query($messageText, $targetDb);

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
ตอบคำถามด้วยภาษาไทยที่สุภาพ กระชับ เป็นมืออาชีพ";

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
        $session = AiChatSession::where('session_uuid', $uuid)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $session->messages()->delete();
        $session->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Smart intent detector
     */
    protected function detectIntent(string $text): string
    {
        $q = mb_strtolower($text);

        // SQL Intent keywords
        $sqlKeywords = [
            'กี่คน', 'จำนวน', 'สถิติ', 'ยอด', 'เท่าไหร่', 'รายชื่อ', 'คนไข้', 'ผู้ป่วย',
            'opd', 'ipd', 'er', 'vn', 'hn', 'an', 'โรค', 'icd', 'pttype', 'สิทธิ',
            'ค่ารักษา', 'วันนี้', 'เดือนนี้', 'ปีนี้', 'ปีงบ', 'refer', 'admit', 'เตียง',
            'พัสดุ', 'บุคลากร', 'เงินเดือน', 'วันลา', 'ครุภัณฑ์', 'จัดซื้อ'
        ];

        foreach ($sqlKeywords as $kw) {
            if (mb_strpos($q, $kw) !== false) {
                return 'sql';
            }
        }

        // RAG Intent keywords
        $ragKeywords = [
            'คู่มือ', 'ระเบียบ', 'แนวทาง', 'ขั้นตอน', 'cpg', 'เอกสาร', 'นโยบาย', 'เกณฑ์',
            'มาตรฐาน', 'วิธีปฏิบัติ', 'ประกาศ', 'คำสั่ง', 'ข้อกำหนด', 'นิยาม'
        ];

        foreach ($ragKeywords as $kw) {
            if (mb_strpos($q, $kw) !== false) {
                return 'rag';
            }
        }

        return 'general';
    }
}
