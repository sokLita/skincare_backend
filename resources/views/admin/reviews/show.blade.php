@extends('admin.layout')

@section('title', 'Review Details')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.reviews.index') }}" class="text-indigo-600 hover:text-indigo-900 mb-4 inline-block">
        <i class="fas fa-arrow-left"></i> Back to Reviews
    </a>
    <h2 class="text-2xl font-bold text-gray-800 mt-4">Review #{{ $review->id }}</h2>
</div>

<div class="bg-white rounded-xl shadow-lg overflow-hidden">
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider mb-2">Product</h3>
                <p class="text-gray-900 font-medium">
                    <a href="{{ route('admin.products.show', $review->product) }}" class="text-indigo-600 hover:text-indigo-900">
                        {{ $review->product->name }}
                    </a>
                </p>
                <p class="text-sm text-gray-600 mt-1">Category: {{ $review->product->category->name ?? 'N/A' }}</p>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider mb-2">Customer</h3>
                <div class="flex items-center">
                    <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-semibold">
                        {{ substr($review->user->name, 0, 1) }}
                    </div>
                    <div class="ml-3">
                        <p class="text-gray-900 font-medium">{{ $review->user->name }}</p>
                        <p class="text-sm text-gray-600">{{ $review->user->email }}</p>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider mb-2">Rating</h3>
                <div class="flex items-center">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= $review->rating)
                            <i class="fas fa-star text-yellow-400 text-xl"></i>
                        @else
                            <i class="far fa-star text-gray-300 text-xl"></i>
                        @endif
                    @endfor
                    <span class="ml-2 text-lg font-semibold text-gray-800">{{ $review->rating }}/5</span>
                </div>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider mb-2">Date</h3>
                <p class="text-gray-900">{{ $review->created_at->format('F j, Y g:i A') }}</p>
                <p class="text-sm text-gray-600">{{ $review->created_at->diffForHumans() }}</p>
            </div>
        </div>

        <div class="mt-6 pt-6 border-t border-gray-200">
            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider mb-2">Comment</h3>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-gray-900 whitespace-pre-wrap">{{ $review->comment ?: 'No comment provided' }}</p>
            </div>
        </div>

        <div class="mt-6 pt-6 border-t border-gray-200 flex justify-between">
            <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" onsubmit="return confirm('Are you sure you want to delete this review?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Delete Review
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
