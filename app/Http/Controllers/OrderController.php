<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Book;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Base query
        $query = Order::with(['orderItems.book']) // Eager load relationships
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        // Optional status filter
        if ($request->has('status') && in_array($request->status, ['pending', 'processing', 'completed', 'cancelled'])) {
            $query->where('status', $request->status);
        }

        // Get paginated orders
        $orders = $query->paginate(10);

        // Calculate additional stats for the view
        $totalOrders = Order::where('user_id', $user->id)->count();
        $totalSpent = Order::where('user_id', $user->id)
            ->whereIn('status', ['completed']) // Only count paid orders
            ->sum('total_amount');

        // Get counts by status for the filter tabs
        $statusCounts = [
            'pending' => Order::where('user_id', $user->id)->where('status', 'pending')->count(),
            'processing' => Order::where('user_id', $user->id)->where('status', 'processing')->count(),
            'completed' => Order::where('user_id', $user->id)->where('status', 'completed')->count(),
            'cancelled' => Order::where('user_id', $user->id)->where('status', 'cancelled')->count(),
        ];

        // Get recent activity
        $recentOrders = Order::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        return view('order.index', compact(
            'orders',
            'totalOrders',
            'totalSpent',
            'statusCounts',
            'recentOrders'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $cart = session()->get('cart');

        $totalAmount = 0;

        foreach ($cart as $item) {
            $totalAmount += $item['price'] * $item['quantity'];
        }

        $order = Order::create([
            'user_id' => auth()->user()->id,
            'total_amount' => $totalAmount,
        ]);

        foreach ($cart as $bookID => $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'book_id' => $bookID,
                'quantity' => $item['quantity'],
                'unit_price' => $item['price']
            ]);

            $book = Book::find($bookID);
            $book->decrement('stock_quantity', $item['quantity']);

        }


        session()->forget('cart');

        return redirect()->route("cart.index", $book)->with("success", "Order placed successfully");
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $order = Order::find($id);
        return view("order.show", compact("order"));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $order = Order::find($id);
        $order->delete();
        return redirect()->route("orders.index", $order)->with("success", "Order ID $id deleted successfully");
    }

    public function changeStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required',
        ]);

        $order->update([
            'status' => $validated['status'],
        ]);

        // Only restore stock if the order is being cancelled
        if ($validated['status'] === 'cancelled') {
            foreach ($order->orderItems as $orderItem) {
                $orderItem->book->increment('stock_quantity', $orderItem->quantity);
            }
        }

        if(auth()->user()->role == 'admin'){
            return redirect()->route('admin.orderShow', $order->id)
            ->with('success', 'Order canceled');
        }
        else{
            return redirect()->route('orders.show', $order->id)
            ->with('success', 'Order canceled');
        }
    }
}
