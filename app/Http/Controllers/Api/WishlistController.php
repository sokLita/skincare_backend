<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Wishlist};

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            $items = $user->wishlist()->with('product.category')->get();
            return response()->json($items);
        } catch (\Throwable $e) {
            \Log::error('WishlistController@index failed', [
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

            $request->validate(['product_id' => 'required|exists:products,id']);

            $item = Wishlist::firstOrCreate([
                'user_id' => $user->id,
                'product_id' => $request->product_id,
            ]);

            return response()->json($item, 201);
        } catch (\Throwable $e) {
            \Log::error('WishlistController@store failed', [
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

            Wishlist::where('user_id', $user->id)
                ->where(function ($query) use ($id) {
                    $query->where('product_id', $id)
                        ->orWhere('id', $id);
                })
                ->delete();

            return response()->json(['message' => 'Removed from wishlist']);
        } catch (\Throwable $e) {
            \Log::error('WishlistController@destroy failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['message' => 'Server error.'], 500);
        }
    }
}
