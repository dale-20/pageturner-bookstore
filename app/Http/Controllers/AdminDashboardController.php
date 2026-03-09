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

        return view('admin.index', compact('stats', 'recentOrders'));
    }

    public function books(Request $request)
    {
        $books = Book::all()->sortByDesc('created_at');
        return view('admin.books', compact('books'));
    }

    public function bookShow(Book $book)
    {
        $book->load(['category', 'reviews.user']);
        $total_sold = Order::where('status', 'completed')
            ->whereHas('orderItems', function ($query) use ($book) {
                $query->where('book_id', $book->id);
            })
            ->count();
        $revenue = $book->price * $total_sold;
        return view('admin.show', compact('book', 'total_sold', 'revenue'));
    }

    public function orders(string $status)
    {
        $orders = Order::where('status', $status)->get();

        return view('admin.orders', compact('orders'));

    }

    public function orderStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required',
        ]);
        $order->update([
            'status' => $validated['status'],
        ]);

        return redirect()->route('admin.orderShow', $order->id)
            ->with('success', 'Order successfully updated.');
    }

    public function orderShow(int $id)
    {
        $order = Order::with(['user', 'orderItems.book', 'orderItems.book.category'])
            ->find($id);

        if (!$order) {
            return redirect()->route('admin.orders', 'pending')
                ->with('error', 'Order not found.');
        }

        return view('admin.orderShow', compact('order'));
    }

    public function users(string $role)
    {
        $users = User::where('role', $role)->get();

        return view('admin.users', compact('users'));
    }

    public function userShow(User $user)
    {
        $user->loadCount('orders')->loadSum('orders', 'total_amount');
        return view('admin.userShow', compact('user'));
    }

    public function userEdit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function userUpdate(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $user->update($request->only('name', 'email'));

        return redirect()->route('admin.users')->with('success', 'User updated successfully.');
    }

    public function userDestroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users')->with('success', 'User deleted successfully.');
    }
}