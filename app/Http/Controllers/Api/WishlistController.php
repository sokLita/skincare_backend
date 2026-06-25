<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Wishlist};

class WishlistController extends Controller
{
    public function index(Request $request) {
        $items = $request->user()->wishlist()->with('product.category')->get();
        return response()->json($items);
    }

    public function store(Request $request) {
        $request->validate(['product_id' => 'required|exists:products,id']);
        $item = Wishlist::firstOrCreate([
            'user_id'    => $request->user()->id,
            'product_id' => $request->product_id,
        ]);
        return response()->json($item, 201);
    }

    public function destroy(Request $request, $productId) {
        Wishlist::where('user_id', $request->user()->id)
                ->where('product_id', $productId)->delete();
        return response()->json(['message' => 'Removed from wishlist']);
    }
}