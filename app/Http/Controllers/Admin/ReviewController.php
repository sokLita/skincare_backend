<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;

class ReviewController extends Controller
{
    public function index() {
        $reviews = Review::with(['user', 'product.category'])->latest()->paginate(20);
        return view('admin.reviews.index', compact('reviews'));
    }

    public function show(Review $review) {
        $review->load(['user', 'product.category']);
        return view('admin.reviews.show', compact('review'));
    }

    public function destroy(Review $review) {
        $review->delete();
        return back()->with('success', 'Review deleted successfully');
    }
}
