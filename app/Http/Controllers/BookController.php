<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $searchTerm = trim($request->get('search', ''));
        $categoryId = $request->get('category');
        $isbn       = trim($request->get('isbn', ''));

        // ── ISBN exact lookup — Redis cached, bypasses Scout ─────────────────
        if (!empty($isbn)) {
            $cacheKey = 'isbn:' . $isbn;
            $book = Cache::remember($cacheKey, 3600, function () use ($isbn) {
                return Book::with('category')
                    ->withAvg('reviews', 'rating')
                    ->withCount('reviews')
                    ->where('isbn', $isbn)
                    ->first();
            });

            $categories = Cache::remember('categories', 3600, fn() => Category::all());

            // Return a single-result listing so the view doesn't need changes
            $books = $book
                ? Book::with('category')
                    ->withAvg('reviews', 'rating')
                    ->withCount('reviews')
                    ->where('isbn', $isbn)
                    ->paginate(100)
                : Book::whereRaw('false')->paginate(100); // empty paginator

            $total = $books->total();

            return view('books.index', compact('books', 'categories', 'total', 'searchTerm'));
        }

        // ── Meilisearch Scout search ──────────────────────────────────────────
        if (!empty($searchTerm)) {
            $scoutOptions = [];

            // Apply category filter inside Meilisearch if provided
            if (!empty($categoryId)) {
                $scoutOptions['filter'] = "category_id = {$categoryId}";
            }

            $books = Book::search($searchTerm, function ($meilisearch, string $query, array $options) use ($scoutOptions) {
                    if (!empty($scoutOptions['filter'])) {
                        $options['filter'] = $scoutOptions['filter'];
                    }
                    return $meilisearch->search($query, $options);
                })
                ->query(fn($q) => $q
                    ->with('category:id,name')
                    ->withAvg('reviews', 'rating')
                    ->withCount('reviews')
                    ->select(['id', 'isbn', 'title', 'author', 'price', 'stock_quantity', 'category_id'])
                )
                ->paginate(100);

            $total      = $books->total();
            $categories = Cache::remember('categories', 3600, fn() => Category::all());

            return view('books.index', compact('books', 'categories', 'total', 'searchTerm'));
        }

        // ── Normal catalog listing — no search term ───────────────────────────
        // Uses idx_books_catalog_filter index; withAvg/withCount avoid N+1.
        $query = Book::with('category:id,name')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->select(['id', 'isbn', 'title', 'author', 'price', 'stock_quantity', 'category_id']);

        if (!empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }

        $books      = $query->orderBy('id')->paginate(100);
        $total      = $books->total();
        $categories = Cache::remember('categories', 3600, fn() => Category::all());

        return view('books.index', compact('books', 'categories', 'total', 'searchTerm'));
    }

    public function create()
    {
        $categories = Cache::remember('categories', 3600, fn() => Category::all());
        return view('books.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id'    => 'required|exists:categories,id',
            'title'          => 'required|string|max:255',
            'author'         => 'required|string|max:255',
            'isbn'           => 'required|string|unique:books',
            'price'          => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'description'    => 'nullable|string',
            'cover_image'    => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')
                ->store('cover', 'public');
        }

        Book::create($validated);

        return redirect()->route('books.index')
            ->with('success', 'Book added successfully!');
    }

    public function show(Book $book)
    {
        $book->load(['category', 'reviews.user']);
        return view('books.show', compact('book'));
    }

    public function edit(Book $book)
    {
        $categories = Category::all();
        return view('books.edit', compact('book', 'categories'));
    }

    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'category_id'    => 'required|exists:categories,id',
            'title'          => 'required|string|max:255',
            'author'         => 'required|string|max:255',
            'isbn'           => 'required|string|unique:books,isbn,' . $book->id,
            'price'          => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'description'    => 'nullable|string',
            'cover_image'    => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')
                ->store('cover', 'public');
        }

        $book->update($validated);

        return redirect()->route('admin.books.show', $book)
            ->with('success', 'Book updated successfully!');
    }

    public function destroy(Book $book)
    {
        $book->delete();

        return redirect()->route('books.index')
            ->with('success', 'Book deleted successfully!');
    }
}