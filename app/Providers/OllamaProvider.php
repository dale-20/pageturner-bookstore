<?php

namespace App\Services\AI;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class OllamaProvider
{
    protected Client $client;
    protected string $baseUrl;
    protected string $model;

    public function __construct()
    {
        $this->client = new Client(['timeout' => 300]); // Ollama can be slower locally
        $this->baseUrl = config('ai.providers.ollama.base_url');
        $this->model = config('ai.providers.ollama.model');
    }

    public function generate(string $prompt): string
    {
        $response = $this->client->post("{$this->baseUrl}/api/generate", [
            'json' => [
                'model' => $this->model,
                'prompt' => $prompt,
                'stream' => false,
                'options' => [
                    'temperature' => 0.3,
                    'num_predict' => 200,
                ],
            ],
        ]);

        $body = json_decode($response->getBody()->getContents(), true);

        $text = $body['response'] ?? null;

        if (!$text) {
            throw new \RuntimeException('Ollama returned an empty response.');
        }

        return trim($text);
    }

    public function analyzeSentiment(string $text): array
    {
        $prompt = "Analyze the sentiment of the following text and respond with ONLY a JSON object 
                   in this exact format: {\"label\": \"positive\", \"score\": 0.95}
                   Label must be one of: positive, negative, neutral.
                   Score must be a float between 0 and 1 representing confidence.
                   Do not include any explanation or extra text. JSON only.
                   Text: {$text}";

        $result = $this->generate($prompt);

        // Strip markdown code fences if present
        $result = preg_replace('/```json|```/', '', $result);

        // Extract JSON from response in case Ollama adds extra text
        preg_match('/\{.*\}/s', $result, $matches);
        $json = $matches[0] ?? $result;

        $decoded = json_decode(trim($json), true);

        if (!$decoded || !isset($decoded['label'], $decoded['score'])) {
            // Safe fallback if Ollama response is unparseable
            Log::warning('Ollama sentiment parse failed, using neutral fallback.');
            return ['label' => 'neutral', 'score' => 0.5];
        }

        return $decoded;
    }
}