<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_index_queue', function (Blueprint $table) {
            $table->id();
            $table->string('model_type');            // e.g. App\Models\Book
            $table->unsignedBigInteger('model_id');  // book id
            $table->string('action')->default('index'); // index | delete
            $table->string('status')->default('pending'); // pending | processing | done | failed
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'model_type'], 'idx_search_queue_status');
            $table->index(['model_type', 'model_id'], 'idx_search_queue_model');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_index_queue');
    }
};