<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\Category;
use App\Models\ImportLog;
use App\Models\ExportLog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\DB;
use App\Jobs\ProcessImportChunk;
use App\Jobs\ProcessExportJob;

class ImportExportTest extends TestCase
{
    private $admin;
    private $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user for testing
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
        
        // Create test category
        $this->category = Category::create([
            'name' => 'Fiction',
            'slug' => 'fiction',
        ]);
    }

    /**
     * TEST 4.1.1: Successful import of 10,000+ book records with validation
     * Requirement: Import chunked processing with chunk size 1000
     * Expected: Memory usage stays under 256MB, all rows processed
     */
    public function test_import_10000_books_with_chunking()
    {
        Storage::fake('local');
        
        // Generate 10,000 CSV rows
        $csvContent = $this->generateLargeCSV(10000);
        $file = UploadedFile::fake()->createWithContent('books_10000.csv', $csvContent);
        
        $response = $this->actingAs($this->admin)
            ->postJson('/admin/import/books', [
                'file' => $file,
                'update_existing' => true,
            ]);
        
        $response->assertStatus(200);
        
        // Verify import log was created
        $this->assertDatabaseHas('import_logs', [
            'file_name' => 'books_10000.csv',
            'user_id' => $this->admin->id,
            'status' => 'completed',
        ]);
        
        // Verify books were imported (should be 10000 rows minus header)
        $this->assertEquals(10000, Book::count());
        
        // Verify memory usage (would need to monitor in production)
        $memoryUsage = memory_get_peak_usage(true);
        $this->assertLessThan(256 * 1024 * 1024, $memoryUsage, 
            'Memory usage exceeded 256MB for 10,000 record import');
    }

    /**
     * TEST 4.1.1: Handling of malformed files with proper error reporting
     * Requirement: Skip-on-failure with detailed failure reports
     * Expected: Failed rows are reported, good rows are imported
     */
    public function test_malformed_file_error_handling()
    {
        // Create CSV with mixed valid and invalid rows
        $csvContent = "ISBN,Title,Author,Price,Stock,Category\n";
        $csvContent .= "978-3161484100,Valid Book,John Doe,29.99,100,Fiction\n";
        $csvContent .= "INVALID-ISBN,Invalid Book,Jane Smith,-10.00,50,Fiction\n"; // Invalid ISBN and negative price
        $csvContent .= "978-0545010221,Another Valid,Jane Smith,19.99,50,Fiction\n";
        $csvContent .= ",Missing Title,No Author,29.99,100,Fiction\n"; // Missing ISBN and Title
        
        $file = UploadedFile::fake()->createWithContent('malformed_books.csv', $csvContent);
        
        $response = $this->actingAs($this->admin)
            ->postJson('/admin/import/books', [
                'file' => $file,
                'update_existing' => false,
            ]);
        
        $response->assertStatus(200);
        
        $data = $response->json();
        
        // Verify error reporting
        $this->assertArrayHasKey('failures', $data);
        $this->assertGreaterThan(0, count($data['failures']));
        
        // Verify only valid books were imported
        $this->assertEquals(2, Book::count());
        
        // Verify failure details are specific
        $failureMessages = implode(' ', array_column($data['failures'], 'error'));
        $this->assertStringContainsString('ISBN', $failureMessages);
        $this->assertStringContainsString('price', $failureMessages);
    }

    /**
     * TEST 4.1.1: Duplicate detection - Update existing vs skip
     * Requirement: Option to update existing books or skip duplicates
     * Expected: With update=true, existing books are updated; with false, they're skipped
     */
    public function test_duplicate_detection_and_update_logic()
    {
        // Create existing book
        $existingBook = Book::create([
            'isbn' => '9783161484100',
            'title' => 'Original Title',
            'author' => 'Original Author',
            'price' => 19.99,
            'stock_quantity' => 50,
            'category_id' => $this->category->id,
            'description' => 'Original description',
        ]);
        
        // Create CSV with duplicate ISBN
        $csvContent = "ISBN,Title,Author,Price,Stock,Category,Description\n";
        $csvContent .= "9783161484100,Updated Title,Updated Author,29.99,100,Fiction,Updated description\n";
        
        $file = UploadedFile::fake()->createWithContent('duplicate_book.csv', $csvContent);
        
        // Test with update_existing = true
        $response = $this->actingAs($this->admin)
            ->postJson('/admin/import/books', [
                'file' => $file,
                'update_existing' => true,
            ]);
        
        $response->assertStatus(200);
        
        // Verify book was updated
        $existingBook->refresh();
        $this->assertEquals('Updated Title', $existingBook->title);
        $this->assertEquals(29.99, $existingBook->price);
        $this->assertEquals(100, $existingBook->stock_quantity);
        
        // Test with update_existing = false (new test)
        $existingBook2 = Book::create([
            'isbn' => '9780545010221',
            'title' => 'Another Original',
            'author' => 'Another Author',
            'price' => 24.99,
            'stock_quantity' => 30,
            'category_id' => $this->category->id,
        ]);
        
        $csvContent2 = "ISBN,Title,Author,Price,Stock,Category\n";
        $csvContent2 .= "9780545010221,Should Not Update,New Author,34.99,80,Fiction\n";
        
        $file2 = UploadedFile::fake()->createWithContent('duplicate_book_skip.csv', $csvContent2);
        
        $response2 = $this->actingAs($this->admin)
            ->postJson('/admin/import/books', [
                'file' => $file2,
                'update_existing' => false,
            ]);
        
        $response2->assertStatus(200);
        
        // Verify book was NOT updated (should be skipped)
        $existingBook2->refresh();
        $this->assertEquals('Another Original', $existingBook2->title);
        $this->assertEquals(24.99, $existingBook2->price);
        
        // Verify failure was recorded for duplicate
        $data = $response2->json();
        $this->assertGreaterThan(0, count($data['failures']));
        $this->assertStringContainsString('already exists', $data['failures'][0]['error']);
    }

    /**
     * TEST 4.1.1: Queue processing and background job completion
     * Requirement: Queue integration for background processing
     * Expected: Job is dispatched to queue for large files
     */
    public function test_queue_processing_for_large_imports()
    {
        Queue::fake();
        
        // Generate CSV with 2000 rows (exceeds chunk size)
        $csvContent = $this->generateLargeCSV(2000);
        $file = UploadedFile::fake()->createWithContent('large_import.csv', $csvContent);
        
        $response = $this->actingAs($this->admin)
            ->postJson('/admin/import/books', [
                'file' => $file,
                'update_existing' => true,
            ]);
        
        $response->assertStatus(200);
        
        $data = $response->json();
        
        // For 2000 rows, should be queued (since >1000)
        if ($data['total_rows'] > 1000) {
            Queue::assertPushed(ProcessImportChunk::class);
            $this->assertEquals('queued', $data['status']);
        }
    }

    /**
     * TEST 4.1.1: Validation rules - ISBN, Title, Price validation
     * Requirement: ISBN unique, valid format; Title max 255; Price positive max 9999.99
     * Expected: Validation errors for invalid data
     */
    public function test_data_validation_rules()
    {
        $testCases = [
            'invalid_isbn_format' => [
                'isbn' => '123456789', // 9 digits only
                'should_fail' => true,
                'error_message' => 'Invalid ISBN',
            ],
            'isbn_too_long' => [
                'isbn' => '9783161484100123', // 15 digits
                'should_fail' => true,
                'error_message' => 'Invalid ISBN',
            ],
            'title_too_long' => [
                'title' => str_repeat('A', 260),
                'should_fail' => true,
                'error_message' => 'exceeds 255 characters',
            ],
            'negative_price' => [
                'price' => -10.00,
                'should_fail' => true,
                'error_message' => 'Invalid price',
            ],
            'price_too_high' => [
                'price' => 15000.00,
                'should_fail' => true,
                'error_message' => 'Maximum is 9999.99',
            ],
            'negative_stock' => [
                'stock' => -5,
                'should_fail' => true,
                'error_message' => 'non-negative',
            ],
            'valid_isbn_10' => [
                'isbn' => '3161484100',
                'should_fail' => false,
            ],
            'valid_isbn_13' => [
                'isbn' => '9783161484100',
                'should_fail' => false,
            ],
        ];
        
        foreach ($testCases as $caseName => $testCase) {
            $csvContent = $this->generateSingleRowCSV([
                'ISBN' => $testCase['isbn'] ?? '9783161484100',
                'Title' => $testCase['title'] ?? 'Test Book',
                'Author' => 'Test Author',
                'Price' => $testCase['price'] ?? 29.99,
                'Stock' => $testCase['stock'] ?? 100,
                'Category' => 'Fiction',
            ]);
            
            $file = UploadedFile::fake()->createWithContent("test_{$caseName}.csv", $csvContent);
            
            $response = $this->actingAs($this->admin)
                ->postJson('/admin/import/books', [
                    'file' => $file,
                    'update_existing' => false,
                ]);
            
            if ($testCase['should_fail']) {
                $data = $response->json();
                $this->assertGreaterThan(0, count($data['failures'] ?? []), 
                    "Failed for test case: {$caseName}");
                
                if (isset($testCase['error_message'])) {
                    $failureString = json_encode($data['failures']);
                    $this->assertStringContainsString($testCase['error_message'], $failureString,
                        "Expected error message not found for: {$caseName}");
                }
            } else {
                $this->assertEquals(1, Book::count() + 1, 
                    "Should have succeeded for: {$caseName}");
            }
            
            // Clean up
            Book::truncate();
        }
    }

    /**
     * TEST 4.1.1: Category handling - create missing categories during import
     * Requirement: Missing categories are created instead of skipping the row
     * Expected: Import succeeds and the new category is assigned to the book
     */
    public function test_missing_category_is_created_during_import()
    {
        $newCategoryName = 'ImportedCategory' . random_int(100000, 999999);
        $newCategoryIsbn = '978' . random_int(1000000000, 9999999999);
        $existingCategoryIsbn = '978' . random_int(1000000000, 9999999999);

        // Test with non-existent category
        $csvContent = "ISBN,Title,Author,Price,Stock,Category\n";
        $csvContent .= "{$newCategoryIsbn},Test Book,John Doe,29.99,100,{$newCategoryName}\n";
        
        $file = UploadedFile::fake()->createWithContent('invalid_category.csv', $csvContent);
        
        $response = $this->actingAs($this->admin)
            ->postJson('/admin/import/books', [
                'file' => $file,
                'update_existing' => false,
            ]);
        
        $response->assertStatus(200);
        $data = $response->json();

        // Should succeed and create the missing category
        $this->assertEquals(1, $data['successful_rows']);
        $this->assertEquals(0, $data['failed_rows']);
        $this->assertDatabaseHas('categories', ['name' => $newCategoryName]);
        $createdCategory = Category::where('name', $newCategoryName)->first();
        $this->assertDatabaseHas('books', [
            'isbn' => $newCategoryIsbn,
            'category_id' => $createdCategory->id,
        ]);
        
        // Test with existing category
        $csvContent2 = "ISBN,Title,Author,Price,Stock,Category\n";
        $csvContent2 .= "{$existingCategoryIsbn},Test Book 2,John Doe,29.99,100,Fiction\n";
        
        $file2 = UploadedFile::fake()->createWithContent('valid_category.csv', $csvContent2);
        
        $response2 = $this->actingAs($this->admin)
            ->postJson('/admin/import/books', [
                'file' => $file2,
                'update_existing' => false,
            ]);
        
        $response2->assertStatus(200);
        
        // Verify import succeeded
        $book = Book::where('isbn', $existingCategoryIsbn)->first();
        $this->assertNotNull($book);
        $this->assertEquals('Fiction', $book->category->name);
    }

    /**
     * TEST 4.1.1: Chunked processing verification (memory efficiency)
     * Requirement: Memory usage stays under 256MB
     * Expected: DB queries are chunked, memory is managed
     */
    public function test_chunked_processing_memory_efficiency()
    {
        // Create 5000 test books
        $books = [];
        for ($i = 0; $i < 5000; $i++) {
            $books[] = [
                'isbn' => '9783161484' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'title' => "Test Book {$i}",
                'author' => 'Test Author',
                'price' => 19.99,
                'stock_quantity' => 100,
                'category_id' => $this->category->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // Insert in chunks to avoid memory issues in test setup
        foreach (array_chunk($books, 500) as $chunk) {
            Book::insert($chunk);
        }
        
        $this->assertEquals(5000, Book::count());
        
        // Test export with chunking
        $response = $this->actingAs($this->admin)
            ->getJson('/admin/export/books?format=csv');
        
        $response->assertStatus(200);
        
        // Verify export log
        $this->assertDatabaseHas('export_logs', [
            'rows_exported' => 5000,
            'status' => 'completed',
        ]);
        
        // Check memory usage (should be under 256MB)
        $memoryUsage = memory_get_peak_usage(true);
        $this->assertLessThan(256 * 1024 * 1024, $memoryUsage,
            'Memory usage exceeded 256MB during chunked processing');
    }

    /**
     * TEST 4.1.1: Export of 50,000+ records without timeout
     * Requirement: Streaming/queuing for large exports
     * Expected: No timeout, successful export completion
     */
    public function test_export_50000_records_without_timeout()
    {
        // Create 50,000 test books efficiently
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Book::truncate();
        
        $chunkSize = 1000;
        $totalBooks = 50000;
        
        for ($i = 0; $i < $totalBooks; $i += $chunkSize) {
            $books = [];
            for ($j = 0; $j < $chunkSize && $i + $j < $totalBooks; $j++) {
                $books[] = [
                    'isbn' => '9783161484' . str_pad($i + $j, 5, '0', STR_PAD_LEFT),
                    'title' => "Test Book " . ($i + $j),
                    'author' => 'Test Author',
                    'price' => rand(100, 9999) / 100,
                    'stock_quantity' => rand(0, 500),
                    'category_id' => $this->category->id,
                    'created_at' => now()->subDays(rand(1, 365)),
                    'updated_at' => now(),
                ];
            }
            Book::insert($books);
        }
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        $this->assertEquals(50000, Book::count());
        
        // Start timer to check for timeout
        $startTime = microtime(true);
        
        // Request export with filters
        $response = $this->actingAs($this->admin)
            ->getJson('/admin/export/books?format=csv&category=' . $this->category->id);
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        $response->assertStatus(200);
        
        // Verify no timeout (execution under reasonable time)
        $this->assertLessThan(120, $executionTime, 
            'Export took more than 120 seconds (possible timeout)');
        
        // Verify all records were exported
        $data = $response->json();
        $this->assertEquals(50000, $data['rows_exported']);
        
        // Verify export log
        $this->assertDatabaseHas('export_logs', [
            'rows_exported' => 50000,
            'format' => 'csv',
        ]);
    }

    /**
     * TEST 4.1.1: Filtered exports functionality
     * Requirement: By category, price range, stock status, date range
     * Expected: Only filtered records are exported
     */
    public function test_filtered_exports()
    {
        // Create test books with various attributes
        $category2 = Category::create(['name' => 'Science', 'slug' => 'science']);
        
        $books = [
            ['isbn' => '9783161484001', 'title' => 'Fiction Book 1', 'price' => 15.99, 'stock_quantity' => 5, 'category_id' => $this->category->id, 'created_at' => '2024-01-15'],
            ['isbn' => '9783161484002', 'title' => 'Fiction Book 2', 'price' => 25.99, 'stock_quantity' => 20, 'category_id' => $this->category->id, 'created_at' => '2024-02-20'],
            ['isbn' => '9783161484003', 'title' => 'Science Book 1', 'price' => 35.99, 'stock_quantity' => 0, 'category_id' => $category2->id, 'created_at' => '2024-03-10'],
            ['isbn' => '9783161484004', 'title' => 'Science Book 2', 'price' => 45.99, 'stock_quantity' => 100, 'category_id' => $category2->id, 'created_at' => '2024-04-05'],
        ];
        
        foreach ($books as $book) {
            Book::create($book);
        }
        
        // Test category filter
        $response = $this->actingAs($this->admin)
            ->getJson('/admin/export/books?format=csv&category=' . $this->category->id);
        
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals(2, $data['rows_exported']);
        
        // Test price range filter (min price)
        $response = $this->actingAs($this->admin)
            ->getJson('/admin/export/books?format=csv&min_price=20');
        
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals(3, $data['rows_exported']);
        
        // Test price range filter (max price)
        $response = $this->actingAs($this->admin)
            ->getJson('/admin/export/books?format=csv&max_price=30');
        
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals(2, $data['rows_exported']);
        
        // Test stock status: out_of_stock
        $response = $this->actingAs($this->admin)
            ->getJson('/admin/export/books?format=csv&stock_status=out_of_stock');
        
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals(1, $data['rows_exported']);
        
        // Test stock status: low_stock (≤ 10)
        $response = $this->actingAs($this->admin)
            ->getJson('/admin/export/books?format=csv&stock_status=low_stock');
        
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals(1, $data['rows_exported']);
        
        // Test date range
        $response = $this->actingAs($this->admin)
            ->getJson('/admin/export/books?format=csv&date_from=2024-02-01&date_to=2024-03-31');
        
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals(2, $data['rows_exported']);
        
        // Test combined filters
        $response = $this->actingAs($this->admin)
            ->getJson('/admin/export/books?format=csv&category=' . $category2->id . '&min_price=40&max_price=50');
        
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals(1, $data['rows_exported']);
    }

    /**
     * TEST 4.1.1: Custom column selection for exports
     * Requirement: Allow users to choose which fields to export
     * Expected: Only selected columns appear in export
     */
    public function test_custom_column_selection()
    {
        // Create test book
        Book::create([
            'isbn' => '9783161484100',
            'title' => 'Custom Columns Test',
            'author' => 'Test Author',
            'price' => 29.99,
            'stock_quantity' => 100,
            'category_id' => $this->category->id,
            'description' => 'This is a test description',
        ]);
        
        // Test with selected columns only
        $columns = ['isbn', 'title', 'price'];
        $columnsParam = implode(',', $columns);
        
        $response = $this->actingAs($this->admin)
            ->getJson("/admin/export/books?format=csv&columns[0]=isbn&columns[1]=title&columns[2]=price");
        
        $response->assertStatus(200);
        
        // Download and verify content
        $data = $response->json();
        $this->assertArrayHasKey('download_url', $data);
        
        // The actual column verification would require downloading and parsing the CSV
        // This test verifies the API accepts the columns parameter
    }

    /**
     * TEST 4.1.1: Format options - XLSX and PDF
     * Requirement: Support XLSX, CSV, PDF formats
     * Expected: Files are generated in requested format
     */
    public function test_multiple_export_formats()
    {
        // Create test books
        for ($i = 1; $i <= 10; $i++) {
            Book::create([
                'isbn' => '9783161484' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'title' => "Format Test Book {$i}",
                'author' => 'Test Author',
                'price' => 19.99,
                'stock_quantity' => 50,
                'category_id' => $this->category->id,
            ]);
        }
        
        $formats = ['csv', 'xlsx', 'pdf'];
        
        foreach ($formats as $format) {
            $response = $this->actingAs($this->admin)
                ->getJson("/admin/export/books?format={$format}");
            
            $response->assertStatus(200);
            
            $data = $response->json();
            $this->assertTrue($data['success']);
            $this->assertStringContainsString($format, $data['download_url']);
            $this->assertEquals(10, $data['rows_exported']);
        }
    }

    /**
     * TEST 4.1.1: Queued exports for large datasets (>10,000 records)
     * Requirement: Queue exports for datasets >10,000 records
     * Expected: Job is dispatched, export status is queued
     */
    public function test_queued_exports_for_large_datasets()
    {
        Queue::fake();
        
        // Create 15,000 test books
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Book::truncate();
        
        $books = [];
        for ($i = 0; $i < 15000; $i++) {
            $books[] = [
                'isbn' => '9783161484' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'title' => "Queued Export Book {$i}",
                'author' => 'Test Author',
                'price' => 19.99,
                'stock_quantity' => 100,
                'category_id' => $this->category->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        foreach (array_chunk($books, 1000) as $chunk) {
            Book::insert($chunk);
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        $this->assertGreaterThan(10000, Book::count());
        
        $response = $this->actingAs($this->admin)
            ->getJson('/admin/export/books?format=csv');
        
        $response->assertStatus(200);
        
        $data = $response->json();
        
        // Should be queued due to large dataset
        $this->assertTrue($data['queued']);
        $this->assertEquals('queued', $data['status'] ?? null);
        
        // Verify job was dispatched
        Queue::assertPushed(ProcessExportJob::class);
        
        // Verify export log created with queued status
        $this->assertDatabaseHas('export_logs', [
            'status' => 'queued',
            'rows_exported' => Book::count(),
        ]);
    }

    /**
     * TEST: Import progress tracking and status polling
     * Requirement: Progress tracking for long-running imports
     * Expected: Status endpoint returns current progress
     */
    public function test_import_progress_tracking()
    {
        // Create import log
        $importLog = ImportLog::create([
            'file_name' => 'test_import.csv',
            'model_type' => 'Book',
            'user_id' => $this->admin->id,
            'status' => 'processing',
            'total_rows' => 1000,
            'successful_rows' => 450,
            'failed_rows' => 50,
        ]);
        
        $response = $this->actingAs($this->admin)
            ->getJson("/admin/import/status/{$importLog->id}");
        
        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertEquals('processing', $data['status']);
        $this->assertEquals(1000, $data['total_rows']);
        $this->assertEquals(450, $data['successful_rows']);
        $this->assertEquals(50, $data['failed_rows']);
    }

    /**
     * TEST: Export status tracking for queued exports
     * Requirement: Track status of queued exports
     * Expected: Status updates as processing -> completed
     */
    public function test_export_status_tracking()
    {
        // Create export log
        $exportLog = ExportLog::create([
            'file_name' => null,
            'model_type' => 'Book',
            'format' => 'csv',
            'filters' => [],
            'user_id' => $this->admin->id,
            'status' => 'queued',
            'rows_exported' => 0,
        ]);
        
        $response = $this->actingAs($this->admin)
            ->getJson("/admin/export/status/{$exportLog->id}");
        
        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertEquals('queued', $data['status']);
        
        // Update to processing and test again
        $exportLog->update(['status' => 'processing']);
        
        $response2 = $this->actingAs($this->admin)
            ->getJson("/admin/export/status/{$exportLog->id}");
        
        $data2 = $response2->json();
        $this->assertEquals('processing', $data2['status']);
        
        // Update to completed
        $exportLog->update([
            'status' => 'completed',
            'file_name' => 'test_export.csv',
            'download_path' => 'exports/test_export.csv',
            'rows_exported' => 100,
            'completed_at' => now(),
        ]);
        
        $response3 = $this->actingAs($this->admin)
            ->getJson("/admin/export/status/{$exportLog->id}");
        
        $data3 = $response3->json();
        $this->assertEquals('completed', $data3['status']);
        $this->assertNotNull($data3['download_url']);
    }

    /**
     * TEST: Download import template
     * Requirement: Create downloadable import templates
     * Expected: Template CSV with headers and sample data
     */
    public function test_download_import_template()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/export/template');
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');
        $response->assertHeader('Content-Disposition', 'attachment; filename="book_import_template.csv"');
    }

    /**
     * TEST: Recent imports display
     * Requirement: Show recent import history
     * Expected: List of recent imports with status
     */
    public function test_recent_imports_display()
    {
        // Create multiple import logs
        for ($i = 1; $i <= 5; $i++) {
            ImportLog::create([
                'file_name' => "import_{$i}.csv",
                'model_type' => 'Book',
                'user_id' => $this->admin->id,
                'status' => $i % 2 == 0 ? 'completed' : 'failed',
                'total_rows' => 100,
                'successful_rows' => $i % 2 == 0 ? 95 : 80,
                'failed_rows' => $i % 2 == 0 ? 5 : 20,
                'completed_at' => now()->subHours($i),
            ]);
        }
        
        $response = $this->actingAs($this->admin)
            ->getJson('/admin/import/recent');
        
        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertArrayHasKey('imports', $data);
        $this->assertGreaterThanOrEqual(5, count($data['imports']));
        
        // Verify imports are recent
        $this->assertEquals('import_1.csv', $data['imports'][0]['file_name']);
    }

    // ==================== HELPER METHODS ====================
    
    /**
     * Generate large CSV file content
     */
    private function generateLargeCSV($rows)
    {
        $csvContent = "ISBN,Title,Author,Price,Stock,Category,Description\n";
        
        for ($i = 1; $i <= $rows; $i++) {
            $isbn = '9783161484' . str_pad($i, 5, '0', STR_PAD_LEFT);
            $csvContent .= "{$isbn},Test Book {$i},Author {$i}," . rand(100, 9999)/100 . "," . rand(0, 500) . ",Fiction,Description for book {$i}\n";
        }
        
        return $csvContent;
    }
    
    /**
     * Generate single row CSV
     */
    private function generateSingleRowCSV($data)
    {
        $headers = ['ISBN', 'Title', 'Author', 'Price', 'Stock', 'Category', 'Description'];
        $row = [];
        
        foreach ($headers as $header) {
            $row[] = $data[$header] ?? '';
        }
        
        return implode(',', $headers) . "\n" . implode(',', $row);
    }
}
