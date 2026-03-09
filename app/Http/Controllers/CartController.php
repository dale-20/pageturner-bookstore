<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Book;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cart = session()->get('cart', []); // Default to empty array if null
        $totalAmount = 0;
        $totalItems = 0;

        // Only calculate if cart is not empty
        if (!empty($cart)) {
            foreach ($cart as $item) {
                $totalAmount += $item['price'] * $item['quantity'];
                $totalItems += $item['quantity'];
            }
        }

        return view('cart.index', compact('cart', 'totalAmount', 'totalItems'));
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
        // Validate the request
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric'
        ]);

        // Initialize cart if it doesn't exist
        if (!session()->has('cart')) {
            session()->put('cart', []);
        }

        // Get the current cart
        $cart = session()->get('cart');

        // Add/update item in cart
        $cart[$request->book_id] = [
            'quantity' => $request->quantity,
            'price' => $request->price
        ];

        // IMPORTANT: Save the updated cart back to session
        session()->put('cart', $cart);

        return redirect()->route('books.show', $request->book_id)
            ->with("success", "Item added to cart successfully");
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id, )
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            $cart[$id]['quantity'] = $request->quantity;
            session()->put('cart', $cart);
            return redirect()->route("cart.index")->with("success", "Cart updated successfully");
        }

        return redirect()->route("cart.index")->with("error", "Item not found in cart");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
            return redirect()->route("cart.index")->with("success", "Item removed from cart");
        }

        return redirect()->route("cart.index")->with("error", "Item not found in cart");
    }

    public function clear()
    {
        session()->forget('cart');
        return redirect()->route("cart.index")->with("success", "Cart cleared successfully");
    }
}
