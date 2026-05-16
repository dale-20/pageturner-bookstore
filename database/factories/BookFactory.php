<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    protected $model = Book::class;

    private static array $categoryIds = [];

    /**
     * Monotonic counter used to build collision-free ISBN-13s.
     *
     * We encode the counter into the 9 "free" digits of the ISBN body
     * (after the 3-digit prefix). That gives us 10^9 = 1 billion unique
     * values per prefix — more than enough for 1 M rows.
     *
     * Prefix alternates between 978 and 979 purely for realism; uniqueness
     * is guaranteed by the counter regardless of prefix.
     */
    private static int $counter = 0;

    private static function nextCounter(): int
    {
        return ++self::$counter;
    }

    private static function loadCategoryIds(): void
    {
        if (empty(self::$categoryIds)) {
            self::$categoryIds = Category::pluck('id')->toArray();

            if (empty(self::$categoryIds)) {
                throw new \RuntimeException(
                    'No categories found. Run CategorySeeder before MassBookSeeder.'
                );
            }
        }
    }

    public function definition(): array
    {
        self::loadCategoryIds();

        return [
            'category_id'    => $this->faker->randomElement(self::$categoryIds),
            'title'          => $this->faker->sentence(rand(2, 6)),
            'author'         => $this->faker->name(),
            'isbn'           => $this->generateSequentialIsbn13(),
            'price'          => $this->faker->randomFloat(2, 9.99, 4999.99),
            'stock_quantity' => $this->faker->numberBetween(0, 1000),
            'description'    => null,
            'cover_image'    => null,
            'created_at'     => now(),
            'updated_at'     => now(),
        ];
    }

    private function generateSequentialIsbn13(): string
    {
        $n      = self::nextCounter();
        $prefix = ($n % 2 === 0) ? '978' : '979';

        // Zero-pad the counter to exactly 9 digits
        $body   = $prefix . str_pad($n, 9, '0', STR_PAD_LEFT);

        // Calculate ISBN-13 check digit
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int) $body[$i] * ($i % 2 === 0 ? 1 : 3);
        }

        $checkDigit = (10 - ($sum % 10)) % 10;

        return $body . $checkDigit;
    }
}