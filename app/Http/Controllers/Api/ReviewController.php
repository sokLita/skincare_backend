<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;

class ReviewController extends Controller
{
    public function store(Request $request, $productId) {
        $request->validate(['rating' => 'required|integer|min:1|max:5', 'comment' => 'nullable|string']);
        $review = Review::updateOrCreate(
            ['user_id' => $request->user()->id, 'product_id' => $productId],
            ['rating' => $request->rating, 'comment' => $request->comment]
        );
        return response()->json($review->load('user'), 201);
    }

    public function index($productId) {
        $reviews = Review::where('product_id', $productId)->with('user')->latest()->get();
        return response()->json($reviews);
    }
}