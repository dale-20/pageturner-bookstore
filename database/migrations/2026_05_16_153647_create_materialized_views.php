<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // ── 1. Bestseller stats per category ─────────────────────────────────
        DB::statement("
        CREATE MATERIALIZED VIEW mv_bestseller_stats AS
        SELECT
            category_id,
            COUNT(*)                                            AS total_books,
            ROUND(AVG(price)::numeric, 2)                      AS avg_price,
            SUM(stock_quantity)                                 AS total_inventory,
            COUNT(CASE WHEN stock_quantity > 500 THEN 1 END)   AS bestseller_count,
            MAX(created_at)                                     AS latest_addition
        FROM books
        GROUP BY category_id
        WITH DATA
    ");

        DB::statement("
        CREATE UNIQUE INDEX idx_mv_bestseller_category_id
        ON mv_bestseller_stats (category_id)
    ");

        // ── 2. Overall inventory summary ──────────────────────────────────────
        DB::statement("
        CREATE MATERIALIZED VIEW mv_inventory_summary AS
        SELECT
            c.id                                                AS category_id,
            c.name                                              AS category_name,
            COUNT(b.id)                                         AS book_count,
            SUM(b.stock_quantity)                               AS total_stock,
            SUM(b.price * b.stock_quantity)                     AS total_value,
            COUNT(CASE WHEN b.stock_quantity = 0 THEN 1 END)   AS out_of_stock_count,
            COUNT(CASE WHEN b.stock_quantity < 10 THEN 1 END)  AS low_stock_count
        FROM categories c
        LEFT JOIN books b ON b.category_id = c.id
        GROUP BY c.id, c.name
        WITH DATA
    ");

        DB::statement("
        CREATE UNIQUE INDEX idx_mv_inventory_category_id
        ON mv_inventory_summary (category_id)
    ");
    }

    public function down(): void
    {
        DB::statement('DROP MATERIALIZED VIEW IF EXISTS mv_inventory_summary');
        DB::statement('DROP MATERIALIZED VIEW IF EXISTS mv_bestseller_stats');
    }
};