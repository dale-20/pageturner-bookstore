<?php

namespace App\Imports;

use App\Models\Book;
use App\Models\Category;
use App\Models\ImportLog;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class BooksImport implements ToModel, WithHeadingRow, WithValidation, WithChunkReading, WithBatchInserts, SkipsOnFailure, ShouldQueue
{
    use SkipsFailures;

    protected $userId;
    protected $importLogId;
    protected $updateExisting;
    protected $failures = [];

    public function __construct($userId, $importLogId, $updateExisting = false)
    {
        $this->userId = $userId;
        $this->importLogId = $importLogId;
        $this->updateExisting = $updateExisting;
    }

    public function model(array $row)
    {
        // Find or create category
        $category = Category::firstOrCreate(
            ['name' => $row['category']],
            ['slug' => \Str::slug($row['category'])]
        );

        // Check if book exists by ISBN
        $book = Book::where('isbn', $row['isbn'])->first();

        if ($book && $this->updateExisting) {
            // Update existing book
            $book->update([
                'title' => $row['title'],
                'author' => $row['author'],
                'price' => $row['price'],
                'stock_quantity' => $row['stock'],
                'category_id' => $category->id,
                'description' => $row['description'] ?? $book->description,
            ]);
            
            DB::table('import_logs')->where('id', $this->importLogId)->increment('successful_rows');
            return null;
        } elseif (!$book) {
            // Create new book
            DB::table('import_logs')->where('id', $this->importLogId)->increment('successful_rows');
            
            return new Book([
                'isbn' => $row['isbn'],
                'title' => $row['title'],
                'author' => $row['author'],
                'price' => $row['price'],
                'stock_quantity' => $row['stock'],
                'category_id' => $category->id,
                'description' => $row['description'] ?? '',
            ]);
        }

        return null;
    }

    public function rules(): array
    {
        return [
            'isbn' => 'required|string|min:10|max:13',
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'price' => 'required|numeric|min:0|max:9999.99',
            'stock' => 'required|integer|min:0',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'isbn.required' => 'The ISBN field is required.',
            'isbn.min' => 'ISBN must be at least 10 characters.',
            'price.numeric' => 'Price must be a valid number.',
            'stock.integer' => 'Stock must be a whole number.',
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            DB::table('import_logs')->where('id', $this->importLogId)->increment('failed_rows');
            
            $this->failures[] = [
                'row' => $failure->row(),
                'attribute' => $failure->attribute(),
                'errors' => $failure->errors(),
                'values' => $failure->values(),
            ];
        }

        // Store failures in import_log
        $currentFailures = DB::table('import_logs')->where('id', $this->importLogId)->value('failures');
        $failuresArray = $currentFailures ? json_decode($currentFailures, true) : [];
        $failuresArray = array_merge($failuresArray, $this->failures);
        
        DB::table('import_logs')
            ->where('id', $this->importLogId)
            ->update(['failures' => json_encode($failuresArray)]);
    }
}