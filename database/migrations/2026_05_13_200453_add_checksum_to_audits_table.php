<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('audits', function (Blueprint $table) {
            //
            $table->string('checksum', 64)->nullable()->after('tags');

            // Add indexes for better query performance
            $table->index('created_at');
            $table->index('event');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audits', function (Blueprint $table) {
            //
            $table->dropColumn('checksum');
            $table->dropIndex(['created_at']);
            $table->dropIndex(['event']);
        });
    }
};
