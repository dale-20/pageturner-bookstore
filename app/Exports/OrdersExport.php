<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class OrdersExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $filters;
    protected $userId;

    public function __construct($filters = [], $userId = null)
    {
        $this->filters = $filters;
        $this->userId = $userId;
    }

    public function query()
    {
        $query = Order::with(['user', 'orderItems.book']);

        if ($this->userId) {
            $query->where('user_id', $this->userId);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
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
        return [
            'Order ID', 'Customer Name', 'Customer Email', 'Total Amount', 
            'Status', 'Items Count', 'Order Date'
        ];
    }

    public function map($order): array
    {
        return [
            $order->id,
            $order->user->name,
            $order->user->email,
            $order->total_amount,
            ucfirst($order->status),
            $order->orderItems->sum('quantity'),
            $order->created_at->format('Y-m-d H:i:s'),
        ];
    }
}