<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Book;
use App\Observers\BookObserver;
use App\Services\AI\AIServiceManager;
use App\Services\AI\GeminiProvider;
use App\Services\AI\HuggingFaceProvider;
use App\Services\AI\OllamaProvider;
use App\Services\AI\ReviewSummaryService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind AI providers as singletons
        $this->app->singleton(GeminiProvider::class);
        $this->app->singleton(HuggingFaceProvider::class);
        $this->app->singleton(OllamaProvider::class);

        // Bind AIServiceManager with its dependencies
        $this->app->singleton(AIServiceManager::class, function ($app) {
            return new AIServiceManager(
                $app->make(GeminiProvider::class),
                $app->make(HuggingFaceProvider::class),
                $app->make(OllamaProvider::class),
            );
        });

        // Bind ReviewSummaryService
        $this->app->singleton(ReviewSummaryService::class, function ($app) {
            return new ReviewSummaryService(
                $app->make(AIServiceManager::class),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        Book::observe(BookObserver::class);
    }
}
