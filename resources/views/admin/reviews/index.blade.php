@extends('admin.layout')

@section('title', 'Reviews')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">All Reviews</h2>
    <p class="text-gray-600 mt-1">Manage customer reviews and ratings</p>
</div>

<div class="bg-white rounded-xl shadow-lg overflow-hidden">
    <table class="min-w-full table-auto">
        <thead>
            <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">ID</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Product</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Customer</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Rating</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Comment</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Date</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($reviews as $review)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="font-semibold text-gray-800">#{{ $review->id }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <a href="{{ route('admin.products.show', $review->product) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                        {{ $review->product->name }}
                    </a>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-semibold text-sm">
                            {{ substr($review->user->name, 0, 1) }}
                        </div>
                        <span class="ml-2 text-gray-800">{{ $review->user->name }}</span>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= $review->rating)
                                <i class="fas fa-star text-yellow-400"></i>
                            @else
                                <i class="far fa-star text-gray-300"></i>
                            @endif
                        @endfor
                        <span class="ml-2 text-sm text-gray-600">({{ $review->rating }})</span>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <p class="text-gray-700 max-w-xs truncate">{{ $review->comment ?: 'No comment' }}</p>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                    {{ $review->created_at->diffForHumans() }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <a href="{{ route('admin.reviews.show', $review) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">
                        <i class="fas fa-eye"></i> View
                    </a>
                    <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this review?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                    <i class="fas fa-comment-slash text-5xl mb-3 text-gray-300"></i>
                    <p class="text-lg">No reviews found</p>
                    <p class="text-sm mt-1">Reviews will appear here when customers leave them</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($reviews->hasPages())
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $reviews->links() }}
    </div>
    @endif
</div>
@endsection
