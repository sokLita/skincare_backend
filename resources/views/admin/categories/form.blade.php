@extends('admin.layout')

@section('title', $category->exists ? 'Edit Category' : 'Create Category')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-lg p-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">
            <i class="fas fa-{{ $category->exists ? 'edit' : 'plus-circle' }} mr-2"></i>
            {{ $category->exists ? 'Edit Category' : 'Create New Category' }}
        </h2>

        <form method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" enctype="multipart/form-data">
            @csrf
            @if($category->exists)
                @method('PUT')
            @endif

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-3" for="name">Category Name</label>
                <input class="form-control"
                       id="name" type="text" name="name" value="{{ old('name', $category->name) }}" required placeholder="Enter category name">
                @error('name')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-3" for="description">Description</label>
                <textarea class="form-control"
                          id="description" name="description" rows="4" placeholder="Enter category description">{{ old('description', $category->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-3" for="image">Category Image (Optional)</label>
                <input class="form-control"
                       id="image" type="file" name="image" accept="image/*">
                <p class="text-sm text-gray-500 mt-2">Upload JPG, PNG, or WebP (max 2MB). Leave empty to keep existing image.</p>
                @error('image')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
                @if($category->image_url)
                    <div class="mt-4">
                        <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="h-24 w-24 object-cover rounded-lg shadow">
                        <p class="text-sm text-gray-500 mt-2">Current image - upload new to replace</p>
                    </div>
                @else
                    <div class="mt-4">
                        <div class="h-24 w-24 rounded-lg bg-gray-200 flex items-center justify-center">
                            <i class="fas fa-image text-gray-400 text-2xl"></i>
                        </div>
                        <p class="text-sm text-gray-500 mt-2">No image uploaded</p>
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-{{ $category->exists ? 'save' : 'plus' }}"></i>
                    {{ $category->exists ? 'Update Category' : 'Create Category' }}
                </button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
