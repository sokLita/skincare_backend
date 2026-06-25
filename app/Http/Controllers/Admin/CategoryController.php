<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()             { return view('admin.categories.index', ['categories' => Category::withCount('products')->get()]); }
    public function create()            { return view('admin.categories.form', ['category' => new Category()]); }
public function store(Request $r) {
        $r->validate(['name' => 'required|unique:categories', 'description' => 'nullable']);
        Category::create($r->only('name', 'description'));
        return redirect()->route('admin.categories.index')->with('success', 'Category created.');
    }
    public function edit(Category $cat) { return view('admin.categories.form', ['category' => $cat]); }
    public function update(Request $r, Category $cat) {
        $r->validate(['name' => 'required|unique:categories,name,'.$cat->id, 'description' => 'nullable']);
        $cat->update($r->only('name', 'description'));
        return redirect()->route('admin.categories.index')->with('success', 'Category updated.');
    }
    public function destroy(Category $cat) {
        $cat->delete();
        return back()->with('success', 'Deleted.');
    }
}
