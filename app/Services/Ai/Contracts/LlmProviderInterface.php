<?php

namespace App\Services\Ai\Contracts;

interface LlmProviderInterface
{
    /**
     * Send chat messages to LLM and get the assistant response text.
     *
     * @param array $messages List of ['role' => 'user'|'assistant'|'system', 'content' => string]
     * @param array $options Model options (temperature, systemPrompt, etc.)
     * @return string
     */
    public function chat(array $messages, array $options = []): string;

    /**
     * Generate vector embedding for a given text.
     *
     * @param string $text
     * @return array<float>
     */
    public function embed(string $text): array;

    /**
     * Test connection to the AI provider with current credentials.
     *
     * @return array ['success' => bool, 'message' => string, 'latency_ms' => int]
     */
    public function testConnection(): array;

    /**
     * Provider identifier name.
     *
     * @return string
     */
    public function getProviderName(): string;
}
