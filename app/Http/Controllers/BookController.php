<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Repositories\BookRepository;
use App\Services\BookCacheService;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function __construct(
        private BookRepository    $books,
        private BookCacheService  $cache,
    ) {}

    public function index(Request $request)
    {
        $searchTerm = trim($request->get('search', ''));
        $categoryId = $request->get('category');
        $isbn       = trim($request->get('isbn', ''));

        // ── ISBN exact lookup — Redis cached via BookCacheService ─────────────
        if (!empty($isbn)) {
            $book = $this->books->findByIsbn($isbn);

            $books = $book
                ? Book::with('category')
                    ->withAvg('reviews', 'rating')
                    ->withCount('reviews')
                    ->where('isbn', $isbn)
                    ->paginate(100)
                : Book::whereRaw('false')->paginate(100);

            $total      = $books->total();
            $categories = $this->books->allCategories();

            return view('books.index', compact('books', 'categories', 'total', 'searchTerm'));
        }

        // ── Meilisearch Scout search ──────────────────────────────────────────
        if (!empty($searchTerm)) {
            $scoutOptions = [];

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
            $categories = $this->books->allCategories();

            return view('books.index', compact('books', 'categories', 'total', 'searchTerm'));
        }

        // ── Normal catalog listing — via BookRepository ───────────────────────
        $books      = $this->books->catalog($categoryId ? (int) $categoryId : null);
        $total      = $books->total();
        $categories = $this->books->allCategories();

        return view('books.index', compact('books', 'categories', 'total', 'searchTerm'));
    }

    public function create()
    {
        $categories = $this->books->allCategories();
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
        // BookObserver::saved() fires automatically — clears ISBN + category cache

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
        $categories = $this->books->allCategories();
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
        // BookObserver::saved() fires automatically — clears ISBN + category cache

        return redirect()->route('admin.books.show', $book)
            ->with('success', 'Book updated successfully!');
    }

    public function destroy(Book $book)
    {
        $book->delete();
        // BookObserver::deleted() fires automatically — clears ISBN + category cache

        return redirect()->route('books.index')
            ->with('success', 'Book deleted successfully!');
    }
}