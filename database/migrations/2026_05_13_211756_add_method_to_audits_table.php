<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audits', function (Blueprint $table) {
            // HTTP method (GET, POST, PUT, PATCH, DELETE)
            $table->string('method', 10)->nullable()->after('user_agent');

            // Index for filtering by method in the dashboard
            $table->index('method');
        });
    }

    public function down(): void
    {
        Schema::table('audits', function (Blueprint $table) {
            $table->dropIndex(['method']);
            $table->dropColumn('method');
        });
    }
};