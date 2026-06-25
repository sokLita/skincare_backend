@extends('admin.layout')

@section('title', $product->exists ? 'Edit Product' : 'Create Product')

@section('content')
<div class="max-w-3xl">
    <div class="bg-white rounded-xl shadow-lg p-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">
            <i class="fas fa-{{ $product->exists ? 'edit' : 'plus-circle' }} mr-2"></i>
            {{ $product->exists ? 'Edit Product' : 'Create New Product' }}
        </h2>

        <form method="POST" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" enctype="multipart/form-data">
            @csrf
            @if($product->exists)
                @method('PUT')
            @endif

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-3" for="name">Product Name</label>
                <input class="form-control"
                       id="name" type="text" name="name" value="{{ old('name', $product->name) }}" required placeholder="Enter product name">
                @error('name')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-3" for="category_id">Category</label>
                <select class="form-select" id="category_id" name="category_id" required>
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-3" for="description">Description</label>
                <textarea class="form-control"
                          id="description" name="description" rows="4" placeholder="Enter product description">{{ old('description', $product->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-3" for="price">Price ($)</label>
                    <input class="form-control"
                           id="price" type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" required placeholder="0.00">
                    @error('price')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-3" for="stock">Stock Quantity</label>
                    <input class="form-control"
                           id="stock" type="number" name="stock" value="{{ old('stock', $product->stock) }}" required placeholder="0">
                    @error('stock')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-6">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" name="is_active" {{ old('is_active', $product->is_active) ? 'checked' : '' }} class="w-5 h-5 text-indigo-600 rounded focus:ring-indigo-500">
                    <span class="ml-3 text-gray-700 font-semibold">Active (visible to customers)</span>
                </label>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-3" for="image">Product Image (Optional)</label>
                <input class="form-control"
                       id="image" type="file" name="image" accept="image/*">
                <p class="text-sm text-gray-500 mt-2">Upload JPG, PNG, or WebP (max 2MB). Leave empty to keep existing image or no image.</p>
                @error('image')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
                @if($product->image_url)
                    <div class="mt-4">
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-32 w-32 object-cover rounded-lg shadow">
                        <p class="text-sm text-gray-500 mt-2">Current image - upload new to replace</p>
                    </div>
                @else
                    <div class="mt-4">
                        <div class="h-32 w-32 rounded-lg bg-gray-200 flex items-center justify-center">
                            <i class="fas fa-image text-gray-400 text-3xl"></i>
                        </div>
                        <p class="text-sm text-gray-500 mt-2">No image uploaded</p>
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4 pt-4 border-t border-gray-200">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-{{ $product->exists ? 'save' : 'plus' }}"></i>
                    {{ $product->exists ? 'Update Product' : 'Create Product' }}
                </button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
