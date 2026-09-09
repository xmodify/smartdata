<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AiSetting;
use App\Services\Ai\AiManager;
use App\Services\Ai\Providers\GeminiProvider;
use App\Services\Ai\Providers\OpenAiProvider;
use App\Services\Ai\Providers\OllamaProvider;
use Exception;

class AiSettingController extends Controller
{
    public function index()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $settings = AiSetting::getAllSettings();

        return view('admin.ai.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $fields = [
            'active_provider',
            'gemini_api_key',
            'gemini_model',
            'gemini_embed_model',
            'openai_api_key',
            'openai_model',
            'openai_embed_model',
            'ollama_base_url',
            'ollama_model',
            'ollama_embed_model',
            'rag_top_k',
            'rag_min_score',
            'sql_default_db',
        ];

        foreach ($fields as $field) {
            if ($request->has($field)) {
                AiSetting::set($field, $request->input($field));
            }
        }

        return redirect()->route('admin.ai.settings')
            ->with('success', 'บันทึกการตั้งค่า SmartData Copilot สำเร็จเรียบร้อยแล้ว');
    }

    public function testConnection(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $provider = strtolower($request->input('provider', 'gemini'));

        try {
            switch ($provider) {
                case 'openai':
                    $key = $request->input('openai_api_key') ?: AiSetting::get('openai_api_key', '');
                    $model = $request->input('openai_model') ?: AiSetting::get('openai_model', 'gpt-4o-mini');
                    $embedModel = $request->input('openai_embed_model') ?: AiSetting::get('openai_embed_model', 'text-embedding-3-small');
                    $instance = new OpenAiProvider($key, $model, $embedModel);
                    break;

                case 'ollama':
                    $baseUrl = $request->input('ollama_base_url') ?: AiSetting::get('ollama_base_url', 'http://localhost:11434');
                    $model = $request->input('ollama_model') ?: AiSetting::get('ollama_model', 'deepseek-r1:latest');
                    $embedModel = $request->input('ollama_embed_model') ?: AiSetting::get('ollama_embed_model', 'nomic-embed-text');
                    $instance = new OllamaProvider($baseUrl, $model, $embedModel);
                    break;

                case 'gemini':
                default:
                    $key = $request->input('gemini_api_key') ?: AiSetting::get('gemini_api_key', '');
                    $model = $request->input('gemini_model') ?: AiSetting::get('gemini_model', 'gemini-2.0-flash');
                    $embedModel = $request->input('gemini_embed_model') ?: AiSetting::get('gemini_embed_model', 'text-embedding-004');
                    $instance = new GeminiProvider($key, $model, $embedModel);
                    break;
            }

            $res = $instance->testConnection();
            return response()->json($res);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'ข้อผิดพลาด: ' . $e->getMessage()
            ]);
        }
    }
}
