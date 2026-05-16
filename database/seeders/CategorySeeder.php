<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Fiction',              'description' => 'Novels and short stories rooted in imagination and narrative.'],
            ['name' => 'Non-Fiction',          'description' => 'Factual works covering real events, people, and ideas.'],
            ['name' => 'Science Fiction',      'description' => 'Speculative fiction exploring futuristic science and technology.'],
            ['name' => 'Fantasy',              'description' => 'Stories set in magical or supernatural worlds.'],
            ['name' => 'Mystery & Thriller',   'description' => 'Suspenseful plots centered on crime, investigation, and danger.'],
            ['name' => 'Romance',              'description' => 'Stories focusing on love and romantic relationships.'],
            ['name' => 'Horror',               'description' => 'Fiction designed to frighten, unsettle, or disturb the reader.'],
            ['name' => 'Biography',            'description' => 'Life stories of real people written by another author.'],
            ['name' => 'Autobiography',        'description' => 'Life stories written by the subject themselves.'],
            ['name' => 'History',              'description' => 'Books documenting past events, civilizations, and figures.'],
            ['name' => 'Science & Nature',     'description' => 'Works covering the natural world, physics, biology, and more.'],
            ['name' => 'Technology',           'description' => 'Books on computing, engineering, and modern technology.'],
            ['name' => 'Business & Finance',   'description' => 'Guides and analyses on economics, investing, and entrepreneurship.'],
            ['name' => 'Self-Help',            'description' => 'Books aimed at personal development and mental well-being.'],
            ['name' => 'Health & Wellness',    'description' => 'Works on physical health, nutrition, and lifestyle.'],
            ['name' => 'Travel',               'description' => 'Guides, memoirs, and essays about exploring the world.'],
            ['name' => 'Cooking & Food',       'description' => 'Recipes, culinary history, and food culture.'],
            ['name' => 'Art & Photography',    'description' => 'Books celebrating visual arts, design, and photography.'],
            ['name' => 'Philosophy',           'description' => 'Explorations of existence, knowledge, ethics, and reason.'],
            ['name' => 'Religion & Spirituality', 'description' => 'Texts on faith, theology, and spiritual practice.'],
            ['name' => 'Politics & Society',   'description' => 'Analysis of governance, social movements, and public affairs.'],
            ['name' => 'Psychology',           'description' => 'Studies of human behavior, cognition, and mental health.'],
            ['name' => 'Education',            'description' => 'Academic texts, pedagogy, and learning resources.'],
            ['name' => 'Children\'s Books',    'description' => 'Picture books and early readers for young children.'],
            ['name' => 'Young Adult',          'description' => 'Fiction and non-fiction aimed at teenage readers.'],
            ['name' => 'Comics & Graphic Novels', 'description' => 'Sequential art storytelling for all ages.'],
            ['name' => 'Poetry',               'description' => 'Collections of verse and lyrical writing.'],
            ['name' => 'Drama & Plays',        'description' => 'Scripts and dramatic works written for performance.'],
            ['name' => 'Language & Linguistics', 'description' => 'Books on grammar, linguistics, and language learning.'],
            ['name' => 'Law',                  'description' => 'Legal texts, case studies, and jurisprudence.'],
        ];

        // insertOrIgnore prevents duplicate errors if seeder is run more than once
        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['name' => $category['name']],
                ['description' => $category['description']]
            );
        }

        $this->command->info('  CategorySeeder: ' . count($categories) . ' categories seeded.');
    }
}