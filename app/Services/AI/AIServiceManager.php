<?php

namespace App\Services\AI;

use App\Models\AIUsageLog;
use Illuminate\Support\Facades\Log;

class AIServiceManager
{
    protected array $providers = [];
    public string $lastUsedProvider = 'unknown';

    public function __construct(
        GeminiProvider $gemini,
        HuggingFaceProvider $huggingface,
        OllamaProvider $ollama
    ) {
        $this->providers = [
            'gemini' => $gemini,
            'huggingface' => $huggingface,
            'ollama' => $ollama,
        ];
    }

    /**
     * Generate text using the default provider with fallback.
     */
    public function generate(string $prompt, string $feature = 'review_summary'): string
    {
        $chain = config('ai.fallback_chain', ['ollama', 'gemini', 'huggingface']);

        // Start with the feature-mapped provider
        $primary = config("ai.features.{$feature}", config('ai.default_provider'));
        $chain = array_unique(array_merge([$primary], $chain));

        foreach ($chain as $providerName) {
            try {
                $provider = $this->providers[$providerName] ?? null;

                if (!$provider) {
                    continue;
                }

                $result = $provider->generate($prompt);

                $this->logUsage($providerName, $feature, $prompt, $result, 'success');

                // Store last used provider so services can read it
                $this->lastUsedProvider = $providerName;

                return $result;

            } catch (\Exception $e) {
                Log::warning("AI provider [{$providerName}] failed: " . $e->getMessage());
                $this->logUsage($providerName, $feature, $prompt, '', 'failed', $e->getMessage());
                continue;
            }
        }

        throw new \RuntimeException('All AI providers are unavailable. Please try again later.');
    }

    /**
     * Run sentiment analysis — always tries Hugging Face first.
     */
    public function analyzeSentiment(string $text): array
    {
        $chain = ['huggingface', 'gemini', 'ollama'];

        foreach ($chain as $providerName) {
            try {
                $provider = $this->providers[$providerName] ?? null;

                if (!$provider) {
                    continue;
                }

                $result = $provider->analyzeSentiment($text);

                $this->logUsage($providerName, 'sentiment', $text, json_encode($result), 'success');

                return $result;

            } catch (\Exception $e) {
                Log::warning("Sentiment provider [{$providerName}] failed: " . $e->getMessage());
                continue;
            }
        }

        // Final fallback — return neutral if everything fails
        return ['label' => 'neutral', 'score' => 0.5];
    }

    protected function logUsage(
        string $provider,
        string $feature,
        string $input,
        string $output,
        string $status,
        ?string $error = null
    ): void {
        try {
            AIUsageLog::create([
                'provider' => $provider,
                'feature' => $feature,
                'model' => config("ai.providers.{$provider}.model", $provider),
                'tokens_used' => (int) (strlen($input) / 4 + strlen($output) / 4),
                'cost_estimate' => 0.0,
                'input_hash' => md5($input),
                'output_hash' => md5($output),
                'confidence' => null,
                'status' => $status,
                'user_id' => auth()->id(),
                'meta' => $error ? ['error' => $error] : null,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log AI usage: ' . $e->getMessage());
        }
    }
}