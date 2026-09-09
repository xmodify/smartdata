<?php

namespace App\Services\Ai;

use App\Models\AiSetting;
use App\Services\Ai\Contracts\LlmProviderInterface;
use App\Services\Ai\Providers\GeminiProvider;
use App\Services\Ai\Providers\OpenAiProvider;
use App\Services\Ai\Providers\OllamaProvider;
use Exception;

class AiManager
{
    /**
     * Resolve the currently active LLM provider based on ai_settings.
     */
    public static function getActiveProvider(): LlmProviderInterface
    {
        $active = AiSetting::get('active_provider', 'gemini');

        return static::resolveProvider($active);
    }

    /**
     * Resolve a specific provider by name.
     */
    public static function resolveProvider(string $providerName): LlmProviderInterface
    {
        switch (strtolower($providerName)) {
            case 'openai':
                $key = AiSetting::get('openai_api_key', '');
                $model = AiSetting::get('openai_model', 'gpt-4o-mini');
                $embedModel = AiSetting::get('openai_embed_model', 'text-embedding-3-small');
                return new OpenAiProvider($key, $model, $embedModel);

            case 'ollama':
                $baseUrl = AiSetting::get('ollama_base_url', 'http://localhost:11434');
                $model = AiSetting::get('ollama_model', 'deepseek-r1:latest');
                $embedModel = AiSetting::get('ollama_embed_model', 'nomic-embed-text');
                return new OllamaProvider($baseUrl, $model, $embedModel);

            case 'gemini':
            default:
                $key = AiSetting::get('gemini_api_key', '');
                $model = AiSetting::get('gemini_model', 'gemini-2.0-flash');
                $embedModel = AiSetting::get('gemini_embed_model', 'text-embedding-004');
                return new GeminiProvider($key, $model, $embedModel);
        }
    }
}
