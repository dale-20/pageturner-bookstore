<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Partitions the books table by id range (10 partitions x 100K rows each).
 *
 * PostgreSQL limitation on partitioned tables:
 *   UNIQUE constraints MUST include the partition key (id).
 *   Therefore isbn uniqueness is enforced per-partition only.
 *   Global ISBN uniqueness is maintained by the application layer
 *   (BookFactory sequential counter + validation in BookController).
 */
return new class extends Migration
{
    private const PARTITIONS = [
        ['name' => 'books_p1',  'from' => 1,       'to' => 100001],
        ['name' => 'books_p2',  'from' => 100001,   'to' => 200001],
        ['name' => 'books_p3',  'from' => 200001,   'to' => 300001],
        ['name' => 'books_p4',  'from' => 300001,   'to' => 400001],
        ['name' => 'books_p5',  'from' => 400001,   'to' => 500001],
        ['name' => 'books_p6',  'from' => 500001,   'to' => 600001],
        ['name' => 'books_p7',  'from' => 600001,   'to' => 700001],
        ['name' => 'books_p8',  'from' => 700001,   'to' => 800001],
        ['name' => 'books_p9',  'from' => 800001,   'to' => 900001],
        ['name' => 'books_p10', 'from' => 900001,   'to' => 1100001],
    ];

    public function up(): void
    {
        // ── Step 1: Create partitioned table ──────────────────────────────────
        DB::statement("
            CREATE TABLE books_partitioned (
                id             BIGSERIAL       NOT NULL,
                category_id    BIGINT          NOT NULL,
                title          VARCHAR(255)    NOT NULL,
                author         VARCHAR(255)    NOT NULL,
                isbn           CHAR(13)        NOT NULL,
                price          DECIMAL(10,2)   NOT NULL,
                stock_quantity INTEGER         NOT NULL DEFAULT 0,
                description    TEXT,
                cover_image    VARCHAR(255),
                search_vector  TSVECTOR
                    GENERATED ALWAYS AS (
                        to_tsvector('english', coalesce(title,'') || ' ' || coalesce(author,''))
                    ) STORED,
                created_at     TIMESTAMP,
                updated_at     TIMESTAMP,
                PRIMARY KEY (id)
            ) PARTITION BY RANGE (id)
        ");

        // ── Step 2: Create partitions ─────────────────────────────────────────
        foreach (self::PARTITIONS as $p) {
            DB::statement("
                CREATE TABLE {$p['name']}
                PARTITION OF books_partitioned
                FOR VALUES FROM ({$p['from']}) TO ({$p['to']})
            ");
        }

        // ── Step 3: Copy data in 100K chunks ──────────────────────────────────
        $maxId = DB::table('books')->max('id') ?? 0;

        for ($offset = 0; $offset <= $maxId; $offset += 100000) {
            $limit = $offset + 100000;
            DB::statement("
                INSERT INTO books_partitioned
                    (id, category_id, title, author, isbn, price,
                     stock_quantity, description, cover_image, created_at, updated_at)
                SELECT
                    id, category_id, title, author, isbn, price,
                    stock_quantity, description, cover_image, created_at, updated_at
                FROM books
                WHERE id >= {$offset} AND id < {$limit}
            ");
        }

        // ── Step 4: Recreate indexes ──────────────────────────────────────────
        // NOTE: On partitioned tables, UNIQUE indexes must include the partition
        // key (id). isbn+id together enforces per-partition uniqueness.
        // Global uniqueness is enforced at the application layer.
        DB::statement('
            CREATE UNIQUE INDEX idx_books_part_isbn
            ON books_partitioned (isbn, id)
        ');

        DB::statement('
            CREATE INDEX idx_books_part_catalog_filter
            ON books_partitioned (category_id, id)
        ');

        DB::statement('
            CREATE INDEX idx_books_part_price_stock
            ON books_partitioned (price, stock_quantity, id)
        ');

        DB::statement('
            CREATE INDEX idx_books_part_fulltext
            ON books_partitioned USING GIN (search_vector)
        ');

        // ── Step 5: Swap tables ───────────────────────────────────────────────
        DB::statement('ALTER TABLE books RENAME TO books_old');
        DB::statement('ALTER TABLE books_partitioned RENAME TO books');

        DB::statement('
            ALTER TABLE books
            ADD CONSTRAINT books_category_id_foreign
            FOREIGN KEY (category_id)
            REFERENCES categories (id)
            ON DELETE CASCADE
        ');

        DB::statement("
            SELECT setval(
                'books_id_seq',
                (SELECT MAX(id) FROM books)
            )
        ");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE books DROP CONSTRAINT IF EXISTS books_category_id_foreign');
        DB::statement('ALTER TABLE books RENAME TO books_partitioned');
        DB::statement('ALTER TABLE books_old RENAME TO books');
        DB::statement('DROP TABLE IF EXISTS books_partitioned CASCADE');
    }
};