<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Book;
use App\Models\Order;
use App\Models\User;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'totalBooks' => Book::count(),
            'totalOrders' => Order::count(),
            'totalUsers' => User::count(),
            'revenue' => Order::where('status', 'completed')->sum('total_amount'),
        ];
        
        $recentOrders = Order::with('user')
            ->latest()
            ->take(10)
            ->get();
            
        return view('admin', compact('stats', 'recentOrders'));
    }
}