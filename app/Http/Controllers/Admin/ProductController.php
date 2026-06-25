<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Product, Category};
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()    { return view('admin.products.index', ['products' => Product::with('category')->latest()->paginate(15)]); }
    public function create()   { return view('admin.products.form', ['product' => new Product(), 'categories' => Category::all()]); }

    public function store(Request $r) {
        $data = $r->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string',
            'description' => 'nullable',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'image'       => 'nullable|image|max:2048',
        ]);
        if ($r->hasFile('image')) $data['image'] = $r->file('image')->store('products', 'public');
        $data['slug'] = Str::slug($data['name']);
        Product::create($data);
        return redirect()->route('admin.products.index')->with('success', 'Product created.');
    }

    public function edit(Product $product) { return view('admin.products.form', ['product' => $product, 'categories' => Category::all()]); }

    public function update(Request $r, Product $product) {
        $data = $r->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string',
            'description' => 'nullable',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'image'       => 'nullable|image|max:2048',
            'is_active'   => 'boolean',
        ]);
        if ($r->hasFile('image')) {
            if ($product->image) \Storage::disk('public')->delete($product->image);
            $data['image'] = $r->file('image')->store('products', 'public');
        }
        $product->update($data);
        return redirect()->route('admin.products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product) {
        if ($product->image) \Storage::disk('public')->delete($product->image);
        $product->delete();
        return back()->with('success', 'Product deleted.');
    }
}
