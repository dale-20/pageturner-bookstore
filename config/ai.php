<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    */
    'default_provider' => env('AI_DEFAULT_PROVIDER', 'ollama'),

    /*
    |--------------------------------------------------------------------------
    | Fallback
    |--------------------------------------------------------------------------
    */
    'fallback_enabled' => env('AI_FALLBACK_ENABLED', true),

    'fallback_chain'   => ['ollama', 'gemini', 'huggingface'],

    /*
    |--------------------------------------------------------------------------
    | Providers
    |--------------------------------------------------------------------------
    */
    'providers' => [

        'gemini' => [
            'api_key' => env('GEMINI_API_KEY'),
            'endpoint' => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent',
            'model' => 'gemini-2.5-flash',
        ],

        'huggingface' => [
            'api_key' => env('HF_API_KEY'),
            'sentiment_model' => 'cardiffnlp/twitter-roberta-base-sentiment-latest',
            'endpoint' => 'https://api-inference.huggingface.co/models/',
        ],

        'ollama' => [              // remove the inner 'ollama' => [ wrapper
            'enabled' => env('OLLAMA_ENABLED', true),
            'base_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
            'model' => env('OLLAMA_MODEL', 'llama3.2'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Feature → Provider Mapping
    |--------------------------------------------------------------------------
    */
    'features' => [
        'review_summary' => 'gemini',
        'sentiment' => 'huggingface',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limits (free tier protection)
    |--------------------------------------------------------------------------
    */
    'rate_limits' => [
        'gemini' => ['requests_per_day' => 1500],
        'huggingface' => ['requests_per_day' => 1000],
        'ollama' => ['requests_per_day' => 999999],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cost Estimates (USD per 1K tokens — free tier = $0)
    |--------------------------------------------------------------------------
    */
    'cost_per_1k_tokens' => [
        'gemini' => 0.0,
        'huggingface' => 0.0,
        'ollama' => 0.0,
    ],

];