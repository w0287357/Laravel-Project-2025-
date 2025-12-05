<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:categories,name|max:255',
        ]);

        Category::create([
            'name' => $request->name,
        ]);

        return redirect()->route('categories.index');
    }

    public function index()
    {
        $categories = Category::all();
        return view('categories.index', compact('categories'));
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:categories,name,' . $id . '|max:255',
        ]);

        $category = Category::findOrFail($id);

        $category->update([
            'name' => $request->name,
        ]);

        return redirect()->route('categories.index');
    }
}
