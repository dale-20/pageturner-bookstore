<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Book;
use App\Models\Category;
use App\Models\Review;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $recentOrders = Order::where('user_id', $user->id)
            ->with(['orderItems.book'])
            ->latest()
            ->take(5)
            ->get();

        $orderSummary = [
            'total'      => Order::where('user_id', $user->id)->count(),
            'pending'    => Order::where('user_id', $user->id)->where('status', 'pending')->count(),
            'processing' => Order::where('user_id', $user->id)->where('status', 'processing')->count(),
            'completed'  => Order::where('user_id', $user->id)->where('status', 'completed')->count(),
            'cancelled'  => Order::where('user_id', $user->id)->where('status', 'cancelled')->count(),
        ];

        $featuredBooks = Book::with('category')
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        $categories = Category::withCount('books')->get();

        $recentReviews = Review::where('user_id', $user->id)
            ->with('book')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'recentOrders',
            'orderSummary',
            'categories',
            'featuredBooks',
            'recentReviews'
        ));
    }
}