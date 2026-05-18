<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_review_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->text('summary');                      // AI-generated summary
            $table->string('overall_sentiment');          // positive, negative, neutral
            $table->float('sentiment_score')->default(0); // 0-1 confidence
            $table->integer('reviews_analyzed')->default(0); // how many reviews were used
            $table->string('provider_used');              // which AI generated this
            $table->string('status')->default('pending'); // pending, completed, failed
            $table->timestamp('generated_at')->nullable();
            $table->json('sentiment_breakdown')->nullable(); // per-review sentiment data
            $table->softDeletes();
            $table->timestamps();

            $table->index(['book_id', 'status']);
            $table->index('overall_sentiment');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_review_summaries');
    }
};