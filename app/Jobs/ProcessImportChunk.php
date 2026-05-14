<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ImportLog;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessImportChunk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $importId;
    protected $updateExisting;
    
    // Maximum number of failed attempts
    public $tries = 3;
    
    // Timeout in seconds
    public $timeout = 3600;

    /**
     * Create a new job instance.
     */
    public function __construct($filePath, $importId, $updateExisting)
    {
        $this->filePath = $filePath;
        $this->importId = $importId;
        $this->updateExisting = $updateExisting;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $importLog = ImportLog::find($this->importId);
        
        if (!$importLog) {
            Log::error('Import job failed: Import log not found', ['import_id' => $this->importId]);
            return;
        }
        
        try {
            $this->processImportFile($importLog);
            
            // Clean up temp file
            if (file_exists($this->filePath)) {
                unlink($this->filePath);
            }
            
            Log::info('Import job completed successfully', [
                'import_id' => $this->importId,
                'successful_rows' => $importLog->successful_rows,
                'failed_rows' => $importLog->failed_rows,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Import job failed', [
                'import_id' => $this->importId,
                'error' => $e->getMessage(),
            ]);
            
            $importLog->update([
                'status' => 'failed',
                'completed_at' => now(),
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Process the import file with chunking
     */
    private function processImportFile($importLog)
    {
        if (!file_exists($this->filePath)) {
            throw new \Exception("Import file not found: {$this->filePath}");
        }
        
        $handle = fopen($this->filePath, 'r');
        
        // Read headers
        $headers = fgetcsv($handle);
        if (!$headers) {
            throw new \Exception("CSV file is empty or unreadable.");
        }
        
        $headers = array_map('trim', $headers);
        $requiredHeaders = ['ISBN', 'Title', 'Price'];
        
        // Validate required headers
        foreach ($requiredHeaders as $required) {
            if (!in_array($required, $headers)) {
                throw new \Exception("Missing required column: {$required}");
            }
        }
        
        $rowNumber = 1;
        $failures = [];
        $chunkSize = 1000;
        $chunk = [];
        $chunkCount = 0;
        
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            $chunk[] = $row;
            $chunkCount++;
            
            if ($chunkCount >= $chunkSize) {
                $this->processChunk($chunk, $headers, $importLog, $this->updateExisting, $failures, $rowNumber - $chunkCount);
                $chunk = [];
                $chunkCount = 0;
            }
        }
        
        // Process remaining rows
        if (!empty($chunk)) {
            $this->processChunk($chunk, $headers, $importLog, $this->updateExisting, $failures, $rowNumber - count($chunk));
        }
        
        fclose($handle);
        
        $status = ($importLog->successful_rows === 0 && $importLog->total_rows > 0) ? 'failed' : 'completed';
        
        $importLog->update([
            'status' => $status,
            'failures' => json_encode($failures),
            'completed_at' => now(),
        ]);
    }
    
    /**
     * Process a chunk of rows
     */
    private function processChunk($rows, $headers, $importLog, $updateExisting, &$failures, $startRow)
    {
        DB::beginTransaction();
        
        try {
            foreach ($rows as $index => $row) {
                $currentRow = $startRow + $index + 1;
                
                try {
                    if (count($headers) !== count($row)) {
                        throw new \Exception("Column count mismatch: expected " . count($headers) . " columns, got " . count($row));
                    }
                    
                    $data = array_combine($headers, $row);
                    
                    // Validate required fields
                    if (empty(trim($data['ISBN'] ?? ''))) {
                        throw new \Exception("ISBN is required");
                    }
                    if (empty(trim($data['Title'] ?? ''))) {
                        throw new \Exception("Title is required");
                    }
                    if (!isset($data['Price']) || $data['Price'] === '') {
                        throw new \Exception("Price is required");
                    }
                    
                    // Validate Title max 255 characters
                    $title = trim($data['Title']);
                    if (strlen($title) > 255) {
                        throw new \Exception("Title exceeds 255 characters (current: " . strlen($title) . ")");
                    }
                    
                    // Validate ISBN format
                    $cleanIsbn = preg_replace('/[^0-9X]/i', '', strtoupper(trim($data['ISBN'])));
                    if (!preg_match('/^(\d{10}|\d{13})$/', $cleanIsbn) && 
                        !(($cleanIsbn[9] ?? '') === 'X' && strlen($cleanIsbn) === 10)) {
                        throw new \Exception("Invalid ISBN format: {$data['ISBN']}. Must be ISBN-10 or ISBN-13.");
                    }
                    
                    // Validate price
                    $price = floatval($data['Price']);
                    if ($price < 0) {
                        throw new \Exception("Invalid price: {$data['Price']}. Must be positive.");
                    }
                    if ($price > 9999.99) {
                        throw new \Exception("Invalid price: {$data['Price']}. Maximum is 9999.99.");
                    }
                    
                    // Validate stock
                    $stock = isset($data['Stock']) ? intval($data['Stock']) : 0;
                    if ($stock < 0) {
                        throw new \Exception("Invalid stock: {$data['Stock']}. Must be non-negative.");
                    }
                    
                    // Create the category when an import row references a new one.
                    $categoryName = trim($data['Category'] ?? '');
                    if ($categoryName === '') {
                        $categoryName = 'Uncategorized';
                    }
                    $category = Category::firstOrCreate(['name' => $categoryName]);
                    
                    // Check for duplicate ISBN
                    $book = Book::where('isbn', $cleanIsbn)->first();
                    
                    if ($book && $updateExisting) {
                        $book->update([
                            'title' => $title,
                            'author' => trim($data['Author'] ?? $book->author),
                            'price' => $price,
                            'stock_quantity' => $stock,
                            'category_id' => $category->id,
                            'description' => trim($data['Description'] ?? $book->description),
                        ]);
                        $importLog->increment('successful_rows');
                        
                    } elseif (!$book) {
                        Book::create([
                            'isbn' => $cleanIsbn,
                            'title' => $title,
                            'author' => trim($data['Author'] ?? 'Unknown'),
                            'price' => $price,
                            'stock_quantity' => $stock,
                            'category_id' => $category->id,
                            'description' => trim($data['Description'] ?? ''),
                        ]);
                        $importLog->increment('successful_rows');
                        
                    } else {
                        $failures[] = [
                            'row' => $currentRow,
                            'error' => "Skipped — book with ISBN {$cleanIsbn} already exists (update option not selected).",
                        ];
                        $importLog->increment('failed_rows');
                    }
                    
                } catch (\Exception $e) {
                    $importLog->increment('failed_rows');
                    $failures[] = [
                        'row' => $currentRow,
                        'error' => $e->getMessage(),
                    ];
                }
            }
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
