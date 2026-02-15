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
    public function index()
    {
        $user = auth()->user();
        $orders = Order::orderBy("created_at","desc")->where("user_id", $user->id)->paginate(10);
        return view("order.index", compact("orders"));

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
        //
        $validated = $request->validate([
            "book_id" => "required|exists:books,id",
            "quantity" => "required|integer|min:1",
            "price" => "required|numeric",
        ]);

        $validated["total_amount"] = $validated["quantity"] * $validated["price"];
        $validated["user_id"] = auth()->id();

        $order = Order::create([
            'user_id' => $validated['user_id'],
            'total_amount' => $validated['total_amount'],
            'status' => 'pending', // Don't forget status!
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'book_id' => $validated['book_id'],
            'quantity' => $validated['quantity'],
            'unit_price' => $validated['price'], // Consider renaming to unit_price
            'item_total' => $validated['total_amount'],
        ]);

        $book = Book::find($validated['book_id']);
        $book->decrement('stock_quantity', $validated['quantity']);

        return redirect()->route("books.show", $book)->with("success", "Order placed successfully");
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
        //
        $validated = $request->validate([
            "quantity" => "required|integer|min:1",
            "previous_quantity" => "required|integer|min:1",
            "order_item_id" => "required|exists:order_items,id"
        ]);

        $order = Order::find($id);
        $orderItem = OrderItem::find($validated["order_item_id"]);
        $orderItem->update(
            [
                'quantity' => $validated['quantity'],
            ]
        );
        $order->update([
            'total_amount' => $orderItem->getSubtotalAttribute()            
        ]);
        $book = $orderItem->book;


        if ($validated['quantity'] > $validated['previous_quantity']){
            $book->decrement('stock_quantity', $validated['quantity'] - $validated['previous_quantity']);
        }
        else{
            $book->increment("stock_quantity",  $validated["previous_quantity"] - $validated["quantity"]);
        }

        return redirect()->route("orders.show", $order)->with("success", "Order updated successfully");
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
}
