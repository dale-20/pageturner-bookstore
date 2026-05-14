<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('export_logs', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->string('model_type');
            $table->string('format'); // xlsx, csv, pdf
            $table->json('filters')->nullable();
            $table->json('columns')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->string('status')->default('processing');
            $table->string('download_path')->nullable();
            $table->integer('rows_exported')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('export_logs');
    }
};