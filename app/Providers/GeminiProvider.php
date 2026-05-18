<?php

namespace App\Services\AI;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class GeminiProvider
{
    protected Client $client;
    protected string $apiKey;
    protected string $endpoint;

    public function __construct()
    {
        $this->client   = new Client(['timeout' => 30]);
        $this->apiKey   = config('ai.providers.gemini.api_key');
        $this->endpoint = config('ai.providers.gemini.endpoint');
    }

    public function generate(string $prompt): string
    {
        $response = $this->client->post("{$this->endpoint}?key={$this->apiKey}", [
            'json' => [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature'     => 0.4,
                    'maxOutputTokens' => 1024,
                ],
            ],
        ]);

        $body = json_decode($response->getBody()->getContents(), true);

        $text = $body['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (! $text) {
            throw new \RuntimeException('Gemini returned an empty response.');
        }

        return trim($text);
    }

    public function analyzeSentiment(string $text): array
    {
        $prompt = "Analyze the sentiment of the following text and respond with ONLY a JSON object 
                   in this exact format: {\"label\": \"positive\", \"score\": 0.95}
                   Label must be one of: positive, negative, neutral.
                   Score must be a float between 0 and 1 representing confidence.
                   Text: {$text}";

        $result = $this->generate($prompt);

        // Strip markdown code fences if present
        $result = preg_replace('/```json|```/', '', $result);

        $decoded = json_decode(trim($result), true);

        if (! $decoded || ! isset($decoded['label'], $decoded['score'])) {
            throw new \RuntimeException('Gemini sentiment response was not valid JSON.');
        }

        return $decoded;
    }
}