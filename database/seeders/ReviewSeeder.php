<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Book;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding reviews...');

        $books = Book::inRandomOrder()->limit(10)->get();

        $positiveReviews = [
            "Absolutely loved this book. The writing style is engaging and the story kept me hooked from start to finish.",
            "One of the best books I have read this year. Highly recommend to anyone who enjoys this genre.",
            "Brilliant storytelling. The characters felt real and the plot twists were unexpected. Will read again.",
            "A masterpiece. The author has a gift for making complex ideas accessible and entertaining.",
            "Could not put it down. Read it in one sitting. The ending was perfect and deeply satisfying.",
            "Exceeded all my expectations. Rich world-building and emotional depth make this a must-read.",
            "Fantastic book. Every chapter left me wanting more. The author's best work by far.",
        ];

        $negativeReviews = [
            "Very disappointing. The story started strong but completely fell apart in the second half.",
            "Struggled to finish this one. The pacing was painfully slow and the characters were flat.",
            "Not worth the price. The plot had too many holes and the ending felt rushed and unsatisfying.",
            "Expected much more based on the reviews. Found it boring and hard to follow.",
            "The writing felt amateur. Too much unnecessary detail and not enough actual story progression.",
        ];

        $neutralReviews = [
            "It was okay. Nothing groundbreaking but a decent read if you have nothing else available.",
            "Average book. Some parts were interesting but overall it did not leave a strong impression.",
            "Decent enough. The writing is competent but the story is fairly predictable throughout.",
            "Had its moments but overall felt like a missed opportunity. Middle of the road for me.",
        ];

        $reviews = [];
        $now = now();

        foreach ($books as $book) {
            // Shuffle user IDs 1-50 and take 20 — guarantees no duplicates per book
            $userIds = collect([16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,37,38,39,40,43,44,45,46,47,48,49,50,51,52,53,54,55])->shuffle()->take(20)->values();

            foreach ($userIds as $index => $userId) {
                $rand = rand(1, 10);

                if ($rand <= 5) {
                    $body = $positiveReviews[array_rand($positiveReviews)];
                    $rating = rand(4, 5);
                } elseif ($rand <= 7) {
                    $body = $negativeReviews[array_rand($negativeReviews)];
                    $rating = rand(1, 2);
                } else {
                    $body = $neutralReviews[array_rand($neutralReviews)];
                    $rating = 3;
                }

                $reviews[] = [
                    'book_id' => $book->id,
                    'user_id' => $userId,
                    'rating' => $rating,
                    'comment' => $body,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('reviews')->insert($reviews);

        $this->command->info('Done. ' . count($reviews) . ' reviews seeded across ' . $books->count() . ' books.');
    }
}