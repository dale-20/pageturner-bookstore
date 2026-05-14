<?php

namespace App\Exports;

use App\Models\Book;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Contracts\Queue\ShouldQueue;

class BooksExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, ShouldQueue
{
    protected $filters;
    protected $selectedColumns;

    public function __construct($filters = [], $selectedColumns = null)
    {
        $this->filters = $filters;
        $this->selectedColumns = $selectedColumns;
    }

    public function query()
    {
        $query = Book::with('category');

        if (!empty($this->filters['category'])) {
            $query->where('category_id', $this->filters['category']);
        }

        if (!empty($this->filters['min_price'])) {
            $query->where('price', '>=', $this->filters['min_price']);
        }

        if (!empty($this->filters['max_price'])) {
            $query->where('price', '<=', $this->filters['max_price']);
        }

        if (!empty($this->filters['stock_status'])) {
            if ($this->filters['stock_status'] === 'in_stock') {
                $query->where('stock_quantity', '>', 0);
            } elseif ($this->filters['stock_status'] === 'out_of_stock') {
                $query->where('stock_quantity', 0);
            } elseif ($this->filters['stock_status'] === 'low_stock') {
                $query->where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 10);
            }
        }

        if (!empty($this->filters['date_from'])) {
            $query->where('created_at', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->where('created_at', '<=', $this->filters['date_to']);
        }

        return $query;
    }

    public function headings(): array
    {
        $defaultHeadings = [
            'ID', 'ISBN', 'Title', 'Author', 'Price', 'Stock', 
            'Category', 'Description', 'Created At'
        ];

        if ($this->selectedColumns && is_array($this->selectedColumns)) {
            return $this->selectedColumns;
        }

        return $defaultHeadings;
    }

    public function map($book): array
    {
        $data = [
            $book->id,
            $book->isbn,
            $book->title,
            $book->author,
            $book->price,
            $book->stock_quantity,
            $book->category->name ?? 'Uncategorized',
            $book->description,
            $book->created_at->format('Y-m-d H:i:s'),
        ];

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}