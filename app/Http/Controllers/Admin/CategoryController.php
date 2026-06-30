<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()             { return view('admin.categories.index', ['categories' => Category::withCount('products')->get()]); }
    public function create()            { return view('admin.categories.form', ['category' => new Category()]); }
    public function store(Request $r) {
        $data = $r->validate([
            'name' => 'required|unique:categories',
            'description' => 'nullable',
            'image' => 'nullable|image|max:2048',
        ]);
        
        if ($r->hasFile('image')) {
            $data['image'] = $r->file('image')->store('categories', 'public');
        }
        
        Category::create($data);
        return redirect()->route('admin.categories.index')->with('success', 'Category created.');
    }
    public function edit($id) {
        $category = Category::findOrFail($id);
        return view('admin.categories.form', ['category' => $category]);
    }
    public function update(Request $r, Category $cat) {
        $data = $r->validate([
            'name' => 'required|unique:categories,name,'.$cat->id,
            'description' => 'nullable',
            'image' => 'nullable|image|max:2048',
        ]);
        
        if ($r->hasFile('image')) {
            if ($cat->image) {
                Storage::disk('public')->delete($cat->image);
            }
            $data['image'] = $r->file('image')->store('categories', 'public');
        }
        
        $cat->update($data);
        return redirect()->route('admin.categories.index')->with('success', 'Category updated.');
    }
    public function destroy(Category $cat) {
        $cat->delete();
        return back()->with('success', 'Deleted.');
    }
}
