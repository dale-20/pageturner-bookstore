<?php

namespace App\Services\AI;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class HuggingFaceProvider
{
    protected Client $client;
    protected string $apiKey;
    protected string $endpoint;
    protected string $sentimentModel;

    public function __construct()
    {
        $this->client        = new Client(['timeout' => 15]);
        $this->apiKey        = config('ai.providers.huggingface.api_key');
        $this->endpoint      = 'https://router.huggingface.co/hf-inference/models/';
        $this->sentimentModel = config('ai.providers.huggingface.sentiment_model');
    }

    /**
     * Text generation via HuggingFace router (summarization model).
     */
    public function generate(string $prompt): string
    {
        $model = 'sshleifer/distilbart-cnn-12-6';

        $response = $this->client->post("{$this->endpoint}{$model}", [
            'headers' => [
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'inputs'     => $prompt,
                'parameters' => [
                    'max_length' => 300,
                    'min_length' => 50,
                    'do_sample'  => false,
                ],
            ],
        ]);

        $body = json_decode($response->getBody()->getContents(), true);

        $text = $body[0]['summary_text'] ?? $body[0]['generated_text'] ?? null;

        if (! $text) {
            throw new \RuntimeException('Hugging Face returned an empty response.');
        }

        return trim($text);
    }

    public function analyzeSentiment(string $text): array
    {
        $response = $this->client->post("{$this->endpoint}{$this->sentimentModel}", [
            'headers' => [
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'inputs' => $text,
            ],
        ]);

        $body = json_decode($response->getBody()->getContents(), true);

        // HF returns [[{label, score}, {label, score}]]
        $results = $body[0] ?? [];

        if (empty($results)) {
            throw new \RuntimeException('Hugging Face sentiment returned empty results.');
        }

        // Pick the highest scoring label
        usort($results, fn($a, $b) => $b['score'] <=> $a['score']);
        $top = $results[0];

        // Normalize HF labels (POSITIVE/NEGATIVE) to lowercase
        $label = strtolower($top['label']);

        // Map to our three-way system
        if (! in_array($label, ['positive', 'negative', 'neutral'])) {
            $label = $top['score'] >= 0.6 ? 'positive' : 'negative';
        }

        return [
            'label' => $label,
            'score' => round($top['score'], 4),
        ];
    }
}