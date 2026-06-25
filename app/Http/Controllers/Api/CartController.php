<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;

class CartController extends Controller
{
    public function index(Request $request) {
        $items = $request->user()->cart()->with('product')->get();
        $total = $items->sum(fn($i) => $i->product->price * $i->quantity);
        return response()->json(['items' => $items, 'total' => $total]);
    }

    public function store(Request $request) {
        $request->validate(['product_id' => 'required|exists:products,id', 'quantity' => 'integer|min:1']);
        $cart = Cart::updateOrCreate(
            ['user_id' => $request->user()->id, 'product_id' => $request->product_id],
            ['quantity' => \DB::raw('quantity + ' . ($request->quantity ?? 1))]
        );
        return response()->json($cart, 201);
    }

    public function update(Request $request, $id) {
        $request->validate(['quantity' => 'required|integer|min:1']);
        $item = Cart::where('user_id', $request->user()->id)->findOrFail($id);
        $item->update(['quantity' => $request->quantity]);
        return response()->json($item);
    }

    public function destroy(Request $request, $id) {
        Cart::where('user_id', $request->user()->id)->where('id', $id)->delete();
        return response()->json(['message' => 'Removed from cart']);
    }
}