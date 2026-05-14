<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    public function test_category_creation_rejects_similar_existing_name(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $existingName = 'Rare Category ' . random_int(100000, 999999);

        Category::create(['name' => $existingName]);

        $response = $this->actingAs($admin)
            ->from('/admin/categories/create')
            ->post('/admin/categories', [
                'name' => strtolower(str_replace(' ', '-', $existingName)),
                'description' => 'Duplicate by normalized name.',
            ]);

        $response->assertRedirect('/admin/categories/create');
        $response->assertSessionHasErrors('name');
    }

    public function test_category_update_allows_current_category_name(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Editable Category ' . random_int(100000, 999999)]);

        $response = $this->actingAs($admin)
            ->put("/admin/categories/{$category->id}", [
                'name' => $category->name,
                'description' => 'Updated description.',
            ]);

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'description' => 'Updated description.',
        ]);
    }

    public function test_category_creation_allows_distinct_names_that_share_a_word(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $suffix = random_int(100000, 999999);

        Category::create(['name' => "Fiction {$suffix}"]);

        $response = $this->actingAs($admin)
            ->post('/admin/categories', [
                'name' => "Science Fiction {$suffix}",
                'description' => 'A distinct category.',
            ]);

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('categories', ['name' => "Science Fiction {$suffix}"]);
    }
}
