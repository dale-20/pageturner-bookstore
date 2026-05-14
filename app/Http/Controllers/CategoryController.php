<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('books')->latest()->paginate(10);
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string',
        ]);

        $this->validateCategoryNameSimilarity($validator, $request->input('name'));

        $validated = $validator->validate();

        Category::create($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully!');
    }

    public function show(Category $category)
    {
        // Option 1: Eager load books with pagination (Recommended)
        $category->load(['books' => function ($query) {
            $query->paginate(12);
        }]);
        
        // Option 2: Or you can paginate directly
        $books = $category->books()->paginate(12);
        
        return view('categories.show', compact('category', 'books'));
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
        ]);

        $this->validateCategoryNameSimilarity($validator, $request->input('name'), $category->id);

        $validated = $validator->validate();

        $category->update($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully!');
    }

    public function destroy(Category $category)
    {
        // Optional: Check if category has books before deleting
        if ($category->books()->count() > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Cannot delete category that has books!');
        }
        
        $category->delete();
        
        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully!');
    }

    private function validateCategoryNameSimilarity($validator, mixed $name, ?int $ignoreCategoryId = null): void
    {
        $validator->after(function ($validator) use ($name, $ignoreCategoryId) {
            $similarCategory = $this->findSimilarCategoryName($name, $ignoreCategoryId);

            if ($similarCategory) {
                $validator->errors()->add(
                    'name',
                    "The category name is too similar to the existing category '{$similarCategory->name}'."
                );
            }
        });
    }

    private function findSimilarCategoryName(mixed $name, ?int $ignoreCategoryId = null): ?Category
    {
        $normalizedName = $this->normalizeCategoryName($name);

        if ($normalizedName === '') {
            return null;
        }

        $categories = Category::query()
            ->when($ignoreCategoryId, function ($query) use ($ignoreCategoryId) {
                $query->where('id', '!=', $ignoreCategoryId);
            })
            ->get(['id', 'name']);

        foreach ($categories as $category) {
            if ($this->categoryNamesAreSimilar($normalizedName, $this->normalizeCategoryName($category->name))) {
                return $category;
            }
        }

        return null;
    }

    private function categoryNamesAreSimilar(string $normalizedName, string $existingName): bool
    {
        if ($existingName === '') {
            return false;
        }

        if ($normalizedName === $existingName) {
            return true;
        }

        $longerLength = max(strlen($normalizedName), strlen($existingName));

        $distance = levenshtein($normalizedName, $existingName);

        return $longerLength >= 4 && $distance <= 1;
    }

    private function normalizeCategoryName(mixed $name): string
    {
        $name = is_scalar($name) ? (string) $name : '';

        return preg_replace('/[^a-z0-9]/', '', strtolower(trim($name))) ?? '';
    }
}
