<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->string('provider');              // gemini, huggingface, ollama
            $table->string('feature');               // review_summary, sentiment
            $table->string('model')->nullable();     // specific model used
            $table->integer('tokens_used')->default(0);
            $table->decimal('cost_estimate', 10, 6)->default(0);
            $table->string('input_hash');            // md5 of input
            $table->string('output_hash');           // md5 of output
            $table->float('confidence')->nullable(); // 0-1 confidence score
            $table->string('status')->default('success'); // success, failed, fallback
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->json('meta')->nullable();        // extra context
            $table->timestamps();

            $table->index(['provider', 'created_at']);
            $table->index(['feature', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_usage_logs');
    }
};