<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds performance indexes to the books table.
 *
 * All statements use IF NOT EXISTS / IF EXISTS so this migration is safe
 * to re-run and won't conflict with MassBookSeeder's index drop/restore cycle.
 *
 * The search_vector column covers title + author only (description is seeded
 * as NULL), keeping the GIN index ~60% smaller.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Using raw SQL throughout — Laravel's Schema::table index helpers
        // compile to plain CREATE INDEX with no IF NOT EXISTS guard, which
        // throws SQLSTATE 42P07 on PostgreSQL when the index already exists.

        DB::statement('
            CREATE INDEX IF NOT EXISTS idx_books_catalog_filter
            ON books (category_id, id)
        ');

        DB::statement('
            CREATE INDEX IF NOT EXISTS idx_books_price_stock
            ON books (price, stock_quantity, id)
        ');

        DB::statement('
            CREATE INDEX IF NOT EXISTS idx_books_isbn_lookup
            ON books (isbn)
        ');

        DB::statement("
            ALTER TABLE books
            ADD COLUMN IF NOT EXISTS search_vector tsvector
            GENERATED ALWAYS AS (
                to_tsvector('english', coalesce(title, '') || ' ' || coalesce(author, ''))
            ) STORED
        ");

        DB::statement('
            CREATE INDEX IF NOT EXISTS idx_books_fulltext
            ON books USING GIN (search_vector)
        ');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_books_fulltext');
        DB::statement('DROP INDEX IF EXISTS idx_books_catalog_filter');
        DB::statement('DROP INDEX IF EXISTS idx_books_price_stock');
        DB::statement('DROP INDEX IF EXISTS idx_books_isbn_lookup');
        DB::statement('ALTER TABLE books DROP COLUMN IF EXISTS search_vector');
    }
};