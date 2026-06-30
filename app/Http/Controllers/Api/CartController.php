<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;

class CartController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            $items = $user->cart()->with('product')->get();
            $total = $items->sum(fn($i) => $i->product->price * $i->quantity);
            $count = $items->sum('quantity');

            return response()->json(['items' => $items, 'total' => $total, 'count' => $count]);
        } catch (\Throwable $e) {
            \Log::error('CartController@index failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['message' => 'Server error.'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'integer|min:1',
            ]);

            $cart = Cart::updateOrCreate(
                ['user_id' => $user->id, 'product_id' => $request->product_id],
                ['quantity' => \DB::raw('quantity + ' . ($request->quantity ?? 1))]
            );

            return response()->json($cart, 201);
        } catch (\Throwable $e) {
            \Log::error('CartController@store failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['message' => 'Server error.'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            $request->validate(['quantity' => 'required|integer|min:1']);

            $item = Cart::where('user_id', $user->id)->findOrFail($id);
            $item->update(['quantity' => $request->quantity]);

            return response()->json($item);
        } catch (\Throwable $e) {
            \Log::error('CartController@update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['message' => 'Server error.'], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            Cart::where('user_id', $user->id)->where('id', $id)->delete();

            return response()->json(['message' => 'Removed from cart']);
        } catch (\Throwable $e) {
            \Log::error('CartController@destroy failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['message' => 'Server error.'], 500);
        }
    }
}
