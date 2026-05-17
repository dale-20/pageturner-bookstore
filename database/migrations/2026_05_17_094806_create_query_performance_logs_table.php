<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('query_performance_logs', function (Blueprint $table) {
            $table->id();
            $table->text('query');                          // Full SQL query
            $table->decimal('duration_ms', 10, 3);         // Execution time in ms
            $table->string('connection')->default('pgsql'); // DB connection used
            $table->string('query_type')->nullable();       // catalog | isbn | search | export
            $table->unsignedBigInteger('rows_examined')->nullable();
            $table->unsignedBigInteger('rows_returned')->nullable();
            $table->string('url')->nullable();              // Request URL that triggered it
            $table->string('user_id')->nullable();          // Authenticated user if any
            $table->boolean('is_slow')->default(false);     // true if above threshold
            $table->timestamps();

            // Indexes for monitoring queries
            $table->index(['is_slow', 'created_at'],  'idx_qpl_slow_queries');
            $table->index(['query_type', 'created_at'], 'idx_qpl_type');
            $table->index('duration_ms',               'idx_qpl_duration');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('query_performance_logs');
    }
};