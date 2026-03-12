<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Book;
use App\Models\User;
use App\Notifications\OrderPlacedNotification;
use App\Notifications\OrderStatusChangedNotification;
use App\Notifications\NewOrderAdminNotification;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Order::with(['orderItems.book'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        if ($request->has('status') && in_array($request->status, ['pending', 'processing', 'completed', 'cancelled'])) {
            $query->where('status', $request->status);
        }

        $orders = $query->paginate(10);

        $totalOrders = Order::where('user_id', $user->id)->count();
        $totalSpent = Order::where('user_id', $user->id)
            ->whereIn('status', ['completed'])
            ->sum('total_amount');

        $statusCounts = [
            'pending'    => Order::where('user_id', $user->id)->where('status', 'pending')->count(),
            'processing' => Order::where('user_id', $user->id)->where('status', 'processing')->count(),
            'completed'  => Order::where('user_id', $user->id)->where('status', 'completed')->count(),
            'cancelled'  => Order::where('user_id', $user->id)->where('status', 'cancelled')->count(),
        ];

        $recentOrders = Order::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        return view('order.index', compact(
            'orders', 'totalOrders', 'totalSpent', 'statusCounts', 'recentOrders'
        ));
    }

    public function create() {}

    /**
     * Store a newly created resource in storage.
     * Notifies: customer (order placed) + all admins (new order).
     */
    public function store(Request $request)
    {
        $cart = session()->get('cart');

        $totalAmount = 0;
        foreach ($cart as $item) {
            $totalAmount += $item['price'] * $item['quantity'];
        }

        $order = Order::create([
            'user_id'      => auth()->user()->id,
            'total_amount' => $totalAmount,
        ]);

        $lastBook = null;
        foreach ($cart as $bookID => $item) {
            OrderItem::create([
                'order_id'   => $order->id,
                'book_id'    => $bookID,
                'quantity'   => $item['quantity'],
                'unit_price' => $item['price'],
            ]);

            $lastBook = Book::find($bookID);
            $lastBook->decrement('stock_quantity', $item['quantity']);
        }

        session()->forget('cart');

        // Load relationships needed for notifications
        $order->load(['user', 'orderItems']);

        // Notify the customer
        auth()->user()->notify(new OrderPlacedNotification($order));

        // Notify all admins
        User::where('role', 'admin')->each(function ($admin) use ($order) {
            $admin->notify(new NewOrderAdminNotification($order));
        });

        return redirect()->route('cart.index', $lastBook)->with('success', 'Order placed successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $order = Order::find($id);
        return view('order.show', compact('order'));
    }

    public function edit(string $id) {}

    public function update(Request $request, string $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $order = Order::find($id);
        $order->delete();
        return redirect()->route('orders.index', $order)->with('success', "Order ID $id deleted successfully");
    }

    /**
     * Change order status.
     * Notifies: customer (status changed).
     */
    public function changeStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required',
        ]);

        $oldStatus = $order->status;

        $order->update([
            'status' => $validated['status'],
        ]);

        // Restore stock on cancellation
        if ($validated['status'] === 'cancelled') {
            foreach ($order->orderItems as $orderItem) {
                $orderItem->book->increment('stock_quantity', $orderItem->quantity);
            }
        }

        // Notify the customer about the status change
        $order->user->notify(new OrderStatusChangedNotification($order, $oldStatus));

        if (auth()->user()->role === 'admin') {
            return redirect()->route('admin.orderShow', $order->id)
                ->with('success', 'Order status updated.');
        }

        return redirect()->route('orders.show', $order->id)
            ->with('success', 'Order cancelled.');
    }
}