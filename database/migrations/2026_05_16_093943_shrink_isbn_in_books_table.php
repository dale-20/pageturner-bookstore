<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Shrinks the `isbn` column from varchar(255) to char(13).
 *
 * ISBN-13 is always exactly 13 characters, so a fixed-width char column
 * is more appropriate and saves ~242 bytes of overhead per row on PostgreSQL
 * (postgres stores varchar length prefix + padding; char avoids both).
 * At 1 million rows that's ~230 MB reclaimed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->char('isbn', 13)->change();
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->string('isbn')->change();
        });
    }
};