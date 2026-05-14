<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ExportLog;
use App\Models\Book;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $exportId;
    protected $filters;
    protected $format;
    protected $userId;
    
    // Maximum number of failed attempts
    public $tries = 3;
    
    // Timeout in seconds (2 hours for large exports)
    public $timeout = 7200;
    
    // Increase memory limit for large exports
    public $memoryLimit = '512M';

    /**
     * Create a new job instance.
     */
    public function __construct($exportId, $filters, $format, $userId)
    {
        $this->exportId = $exportId;
        $this->filters = $filters;
        $this->format = $format;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $exportLog = ExportLog::find($this->exportId);
        
        if (!$exportLog) {
            Log::error('Export job failed: Export log not found', [
                'export_id' => $this->exportId,
                'user_id' => $this->userId
            ]);
            return;
        }
        
        try {
            // Update status to processing
            $exportLog->update(['status' => 'processing']);
            
            // Generate the export file
            $fileInfo = $this->generateExportFile();
            
            // Update export log with completion status
            $exportLog->update([
                'status' => 'completed',
                'file_name' => $fileInfo['file_name'],
                'download_path' => $fileInfo['download_path'],
                'rows_exported' => $fileInfo['rows_exported'],
                'completed_at' => now(),
            ]);
            
            Log::info('Export job completed successfully', [
                'export_id' => $this->exportId,
                'rows_exported' => $fileInfo['rows_exported'],
                'file_name' => $fileInfo['file_name'],
            ]);
            
        } catch (\Exception $e) {
            Log::error('Export job failed', [
                'export_id' => $this->exportId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            $exportLog->update([
                'status' => 'failed',
                'completed_at' => now(),
                'failures' => json_encode([[
                    'error' => $e->getMessage(),
                    'timestamp' => now()->toDateTimeString()
                ]]),
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Generate the export file with chunking for memory efficiency
     */
    private function generateExportFile()
    {
        // Build the query with filters
        $query = Book::with('category');
        
        // Apply filters
        if (!empty($this->filters['category'])) {
            $query->where('category_id', $this->filters['category']);
        }
        
        if (!empty($this->filters['min_price'])) {
            $query->where('price', '>=', (float) $this->filters['min_price']);
        }
        
        if (!empty($this->filters['max_price'])) {
            $query->where('price', '<=', (float) $this->filters['max_price']);
        }
        
        if (($this->filters['stock_status'] ?? null) === 'in_stock') {
            $query->where('stock_quantity', '>', 0);
        } elseif (($this->filters['stock_status'] ?? null) === 'out_of_stock') {
            $query->where('stock_quantity', 0);
        } elseif (($this->filters['stock_status'] ?? null) === 'low_stock') {
            $query->where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 10);
        }
        
        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }
        
        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }
        
        // Get total count for logging
        $totalRecords = $query->count();
        
        // Determine selected columns
        $selectedColumns = $this->filters['columns'] ?? ['id', 'isbn', 'title', 'author', 'price', 'stock_quantity', 'category', 'created_at'];
        
        // Column mapping for headers
        $columnMap = [
            'id' => 'ID',
            'isbn' => 'ISBN',
            'title' => 'Title',
            'author' => 'Author',
            'price' => 'Price',
            'stock_quantity' => 'Stock Quantity',
            'category' => 'Category',
            'description' => 'Description',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
        
        // Generate filename
        $fileName = 'books_export_' . date('Ymd_His') . '_' . uniqid() . '.' . $this->format;
        $storagePath = 'exports/' . $fileName;
        $fullPath = storage_path('app/public/' . $storagePath);
        
        // Ensure directory exists
        $directory = dirname($fullPath);
        if (!file_exists($directory)) {
            mkdir($directory, 0775, true);
        }
        
        // Generate file based on format
        if ($this->format === 'csv') {
            $this->generateCSV($fullPath, $query, $selectedColumns, $columnMap);
        } elseif ($this->format === 'xlsx') {
            $this->generateXLSX($fullPath, $query, $selectedColumns, $columnMap);
        } elseif ($this->format === 'pdf') {
            $this->generatePDF($fullPath, $query, $selectedColumns, $columnMap);
        } else {
            throw new \Exception("Unsupported format: {$this->format}");
        }
        
        return [
            'file_name' => $fileName,
            'download_path' => $storagePath,
            'rows_exported' => $totalRecords,
        ];
    }
    
    /**
     * Generate CSV file with chunking
     */
    private function generateCSV($filePath, $query, $selectedColumns, $columnMap)
    {
        $file = fopen($filePath, 'w');
        
        // Add BOM for Excel UTF-8 compatibility
        fwrite($file, "\xEF\xBB\xBF");
        
        // Write headers
        $headers = [];
        foreach ($selectedColumns as $col) {
            $headers[] = $columnMap[$col] ?? $col;
        }
        fputcsv($file, $headers);
        
        // Process in chunks of 1000 rows for memory efficiency
        $query->chunk(1000, function ($books) use ($file, $selectedColumns) {
            foreach ($books as $book) {
                $row = [];
                foreach ($selectedColumns as $col) {
                    switch ($col) {
                        case 'category':
                            $row[] = $book->category ? $book->category->name : 'Uncategorized';
                            break;
                        case 'price':
                            $row[] = number_format($book->price, 2);
                            break;
                        case 'created_at':
                        case 'updated_at':
                            $row[] = $book->{$col} ? $book->{$col}->format('Y-m-d H:i:s') : '';
                            break;
                        default:
                            $row[] = $book->{$col} ?? '';
                    }
                }
                fputcsv($file, $row);
            }
        });
        
        fclose($file);
    }
    
    /**
     * Generate XLSX file using Laravel Excel
     * Note: Requires maatwebsite/excel package
     */
    private function generateXLSX($filePath, $query, $selectedColumns, $columnMap)
    {
        // Check if Excel package is installed
        if (!class_exists('\Maatwebsite\Excel\Facades\Excel')) {
            throw new \Exception('Laravel Excel package not installed. Run: composer require maatwebsite/excel');
        }
        
        $exportData = [];
        
        // Add headers
        $headers = [];
        foreach ($selectedColumns as $col) {
            $headers[] = $columnMap[$col] ?? $col;
        }
        $exportData[] = $headers;
        
        // Process in chunks
        $query->chunk(1000, function ($books) use (&$exportData, $selectedColumns) {
            foreach ($books as $book) {
                $row = [];
                foreach ($selectedColumns as $col) {
                    switch ($col) {
                        case 'category':
                            $row[] = $book->category ? $book->category->name : 'Uncategorized';
                            break;
                        case 'price':
                            $row[] = number_format($book->price, 2);
                            break;
                        case 'created_at':
                        case 'updated_at':
                            $row[] = $book->{$col} ? $book->{$col}->format('Y-m-d H:i:s') : '';
                            break;
                        default:
                            $row[] = $book->{$col} ?? '';
                    }
                }
                $exportData[] = $row;
            }
        });
        
        // Use Laravel Excel to create XLSX
        \Maatwebsite\Excel\Facades\Excel::store(
            new class($exportData) implements \Maatwebsite\Excel\Concerns\FromArray {
                private $data;
                public function __construct($data) { $this->data = $data; }
                public function array(): array { return $this->data; }
            },
            $filePath,
            'local',
            \Maatwebsite\Excel\Excel::XLSX
        );
    }
    
    /**
     * Generate PDF file using DomPDF
     * Note: Requires barryvdh/laravel-dompdf package
     */
    private function generatePDF($filePath, $query, $selectedColumns, $columnMap)
    {
        // Check if DomPDF package is installed
        if (!class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
            throw new \Exception('DomPDF package not installed. Run: composer require barryvdh/laravel-dompdf');
        }
        
        $exportData = [];
        $totalRecords = $query->count();
        
        // Process in chunks to get all data (PDF needs all data for rendering)
        $query->chunk(1000, function ($books) use (&$exportData, $selectedColumns) {
            foreach ($books as $book) {
                $row = [];
                foreach ($selectedColumns as $col) {
                    switch ($col) {
                        case 'category':
                            $row[$col] = $book->category ? $book->category->name : 'Uncategorized';
                            break;
                        case 'price':
                            $row[$col] = number_format($book->price, 2);
                            break;
                        case 'created_at':
                        case 'updated_at':
                            $row[$col] = $book->{$col} ? $book->{$col}->format('Y-m-d H:i:s') : '';
                            break;
                        default:
                            $row[$col] = $book->{$col} ?? '';
                    }
                }
                $exportData[] = (object) $row;
            }
        });
        
        // Generate headers for PDF
        $headers = [];
        foreach ($selectedColumns as $col) {
            $headers[] = $columnMap[$col] ?? $col;
        }
        
        // Create PDF view
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.books-pdf', [
            'headers' => $headers,
            'books' => $exportData,
            'columns' => $selectedColumns,
            'total_records' => $totalRecords,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'filters' => $this->filters,
        ]);
        
        // Save PDF
        $pdf->save($filePath);
    }
    
    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::critical('Export job failed permanently', [
            'export_id' => $this->exportId,
            'error' => $exception->getMessage(),
            'user_id' => $this->userId,
        ]);
        
        // Update export log as failed
        $exportLog = ExportLog::find($this->exportId);
        if ($exportLog) {
            $exportLog->update([
                'status' => 'failed',
                'completed_at' => now(),
                'failures' => json_encode([[
                    'error' => $exception->getMessage(),
                    'timestamp' => now()->toDateTimeString()
                ]]),
            ]);
        }
    }
}