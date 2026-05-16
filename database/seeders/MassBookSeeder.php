<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MassBookSeeder extends Seeder
{
    private const TOTAL      = 1_000_000;
    private const CHUNK_SIZE = 2_000; // smaller chunks = lower peak memory

    public function run(): void
    {
        $this->command->info('Dropping indexes before bulk insert for faster seeding…');
        $this->dropIndexes();

        $categoryIds = DB::table('categories')->pluck('id')->toArray();

        if (empty($categoryIds)) {
            throw new \RuntimeException('No categories found. Run CategorySeeder first.');
        }

        $chunks = (int) ceil(self::TOTAL / self::CHUNK_SIZE);
        $now    = now()->toDateTimeString();

        $this->command->getOutput()->progressStart($chunks);

        $counter = 0;

        for ($i = 0; $i < $chunks; $i++) {
            $count = ($i === $chunks - 1)
                ? self::TOTAL - ($i * self::CHUNK_SIZE)
                : self::CHUNK_SIZE;

            $rows = [];

            for ($j = 0; $j < $count; $j++) {
                $counter++;
                $rows[] = [
                    'category_id'    => $categoryIds[array_rand($categoryIds)],
                    'title'          => $this->fakeTitle(),
                    'author'         => $this->fakeName(),
                    'isbn'           => $this->makeIsbn($counter),
                    'price'          => round(mt_rand(999, 499999) / 100, 2),
                    'stock_quantity' => mt_rand(0, 1000),
                    'description'    => null,
                    'cover_image'    => null,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ];
            }

            // Single multi-row INSERT — one round-trip per chunk, no Eloquent overhead
            DB::table('books')->insert($rows);

            unset($rows); // release the array immediately after insert
            gc_collect_cycles();

            $this->command->getOutput()->progressAdvance();
        }

        $this->command->getOutput()->progressFinish();

        $this->command->info('Restoring indexes…');
        $this->restoreIndexes();

        $this->command->info('Done. ' . number_format(self::TOTAL) . ' books seeded.');
    }

    // -------------------------------------------------------------------------
    // Lightweight data generators (no Faker — avoids per-call object overhead)
    // -------------------------------------------------------------------------

    private static array $words = [
        'the', 'great', 'lost', 'last', 'secret', 'dark', 'bright', 'silent',
        'broken', 'golden', 'wild', 'hidden', 'forgotten', 'rising', 'falling',
        'shadow', 'light', 'storm', 'fire', 'wind', 'stone', 'river', 'dream',
        'path', 'world', 'heart', 'mind', 'soul', 'king', 'queen', 'knight',
        'dawn', 'dusk', 'night', 'day', 'sea', 'sky', 'land', 'war', 'peace',
    ];

    private static array $firstNames = [
        'James','Mary','John','Patricia','Robert','Jennifer','Michael','Linda',
        'William','Barbara','David','Elizabeth','Richard','Susan','Joseph','Jessica',
        'Thomas','Sarah','Charles','Karen','Emily','Daniel','Laura','Matthew',
        'Anna','Mark','Olivia','Paul','Emma','Andrew','Isabella','Ryan','Sophia',
    ];

    private static array $lastNames = [
        'Smith','Johnson','Williams','Brown','Jones','Garcia','Miller','Davis',
        'Wilson','Taylor','Anderson','Thomas','Jackson','White','Harris','Martin',
        'Thompson','Young','Robinson','Lewis','Walker','Hall','Allen','King',
        'Scott','Green','Baker','Adams','Nelson','Carter','Mitchell','Roberts',
    ];

    private function fakeTitle(): string
    {
        $w = self::$words;
        $count = mt_rand(2, 5);
        $parts = [];
        for ($i = 0; $i < $count; $i++) {
            $parts[] = $w[array_rand($w)];
        }
        return ucwords(implode(' ', $parts));
    }

    private function fakeName(): string
    {
        return self::$firstNames[array_rand(self::$firstNames)]
            . ' '
            . self::$lastNames[array_rand(self::$lastNames)];
    }

    private function makeIsbn(int $n): string
    {
        $prefix = ($n % 2 === 0) ? '978' : '979';
        $body   = $prefix . str_pad($n, 9, '0', STR_PAD_LEFT);

        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int) $body[$i] * ($i % 2 === 0 ? 1 : 3);
        }

        return $body . ((10 - ($sum % 10)) % 10);
    }

    // -------------------------------------------------------------------------
    // Index helpers
    // -------------------------------------------------------------------------

    private function dropIndexes(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_books_fulltext');
        DB::statement('DROP INDEX IF EXISTS idx_books_catalog_filter');
        DB::statement('DROP INDEX IF EXISTS idx_books_price_stock');
        DB::statement('DROP INDEX IF EXISTS idx_books_isbn_lookup');
    }

    private function restoreIndexes(): void
    {
        DB::statement('CREATE INDEX IF NOT EXISTS idx_books_catalog_filter ON books (category_id, id)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_books_price_stock ON books (price, stock_quantity, id)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_books_isbn_lookup ON books (isbn)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_books_fulltext ON books USING GIN (search_vector)');
    }
}