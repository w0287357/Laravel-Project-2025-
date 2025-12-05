<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use App\Models\Item;
use App\Models\Category;

class ItemController extends Controller
{
    public function create()
    {
        $categories = Category::all();
        return view('items.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|max:255',
            'description' => 'required|max:500',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'sku' => 'required|unique:items,sku|max:100',
            'picture' => 'required|image|max:2048',
        ]);

        // Generate unique filename with timestamp
        $file = $request->file('picture');
        $extension = $file->getClientOriginalExtension();
        $timestamp = time();
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $filename = $originalName . '_' . $timestamp . '.' . $extension;
        
        // Get full storage path
        $storagePath = storage_path('app/public/images/');
        
        // Store original image
        $file->storeAs('images', $filename, 'public');
        
        // Create image manager instance
        $manager = new ImageManager(new Driver());
        
        // Create thumbnail (100x100)
        $thumbnail = $manager->read($file);
        $thumbnail->cover(100, 100);
        $thumbnail->save($storagePath . 'tn_' . $filename);
        
        // Create large preview (300x300)
        $large = $manager->read($file);
        $large->cover(300, 300);
        $large->save($storagePath . 'lrg_' . $filename);

        Item::create([
            'category_id' => $request->category_id,
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'sku' => $request->sku,
            'picture' => $filename,
        ]);

        return redirect()->route('items.index');
    }

    public function index()
    {
        $items = Item::all();
        return view('items.index', compact('items'));
    }

    public function edit($id)
    {
        $item = Item::findOrFail($id);
        $categories = Category::all();
        return view('items.edit', compact('item', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|max:255',
            'description' => 'required|max:500',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'sku' => 'required|unique:items,sku,' . $id . '|max:100',
            'picture' => 'nullable|image|max:2048',
        ]);

        $item = Item::findOrFail($id);

        $filename = $item->picture;
        if ($request->hasFile('picture')) {
            // Delete old images (all 3 versions)
            if ($item->picture) {
                Storage::disk('public')->delete([
                    'images/' . $item->picture,
                    'images/tn_' . $item->picture,
                    'images/lrg_' . $item->picture,
                ]);
            }
            
            // Generate unique filename with timestamp
            $file = $request->file('picture');
            $extension = $file->getClientOriginalExtension();
            $timestamp = time();
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $filename = $originalName . '_' . $timestamp . '.' . $extension;
            
            // Get full storage path
            $storagePath = storage_path('app/public/images/');
            
            // Store original image
            $file->storeAs('images', $filename, 'public');
            
            // Create image manager instance
            $manager = new ImageManager(new Driver());
            
            // Create thumbnail (100x100)
            $thumbnail = $manager->read($file);
            $thumbnail->cover(100, 100);
            $thumbnail->save($storagePath . 'tn_' . $filename);
            
            // Create large preview (300x300)
            $large = $manager->read($file);
            $large->cover(300, 300);
            $large->save($storagePath . 'lrg_' . $filename);
        }

        $item->update([
            'category_id' => $request->category_id,
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'sku' => $request->sku,
            'picture' => $filename,
        ]);

        return redirect()->route('items.index');
    }

    public function destroy($id)
    {
        $item = Item::findOrFail($id);
        $item->delete();
        
        return redirect()->route('items.index');
    }
}
