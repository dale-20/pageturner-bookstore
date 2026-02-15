<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Book;
use App\Models\Category;

class DashboardController extends Controller
{
    //

    public function index()
    {
        $user = auth()->user();
        $recentOrders = Order::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();


        $featuredBooks = Book::with('category')
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();
        $categories = Category::withCount('books')->get();

        return view('dashboard', compact('recentOrders', 'categories', 'featuredBooks'));
    }
}
