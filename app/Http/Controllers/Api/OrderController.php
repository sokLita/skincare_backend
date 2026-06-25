<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Order, OrderItem, Cart, Product};

class OrderController extends Controller
{
    public function index(Request $request) {
        $orders = $request->user()->orders()->with('items.product')->latest()->get();
        return response()->json($orders);
    }

    public function show(Request $request, $id) {
        $order = Order::where('user_id', $request->user()->id)
                      ->with('items.product')->findOrFail($id);
        return response()->json($order);
    }

    public function checkout(Request $request) {
        $request->validate(['shipping_address' => 'required|string']);
        $cartItems = $request->user()->cart()->with('product')->get();
        if ($cartItems->isEmpty())
            return response()->json(['message' => 'Cart is empty'], 400);

        $total = $cartItems->sum(fn($i) => $i->product->price * $i->quantity);
        $order = Order::create([
            'user_id'          => $request->user()->id,
            'total_amount'     => $total,
            'shipping_address' => $request->shipping_address,
        ]);
        foreach ($cartItems as $item) {
            OrderItem::create([
                'order_id'   => $order->id,
                'product_id' => $item->product_id,
                'quantity'   => $item->quantity,
                'price'      => $item->product->price,
            ]);
            $item->product->decrement('stock', $item->quantity);
        }
        $request->user()->cart()->delete();
        return response()->json($order->load('items.product'), 201);
    }
}