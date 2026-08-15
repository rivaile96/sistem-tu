<?php

namespace App\Http\Controllers;

use App\Models\PosItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // <--- PENTING!

class PosItemController extends Controller
{
    public function index(Request $request)
    {
        $query = PosItem::query();
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('category', 'like', '%' . $request->search . '%');
        }
        $items = $query->orderBy('stock', 'asc')->paginate(10);
        return view('pos.items.index', compact('items'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'stock' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Validasi Gambar
        ]);

        $data = $request->only(['name', 'category', 'stock', 'price']);

        // Logic Upload Gambar Baru
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $data['image'] = $path;
        }

        PosItem::create($data);
        return redirect()->back()->with('success', 'Barang berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'stock' => 'required|integer',
            'price' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $item = PosItem::findOrFail($id);
        $data = $request->only(['name', 'category', 'stock', 'price']);

        // Logic Ganti Gambar
        if ($request->hasFile('image')) {
            if ($item->image) {
                Storage::disk('public')->delete($item->image); // Hapus yg lama
            }
            $path = $request->file('image')->store('products', 'public');
            $data['image'] = $path;
        }

        $item->update($data);
        return redirect()->back()->with('success', 'Data barang diperbarui!');
    }

    public function destroy($id)
    {
        $item = PosItem::findOrFail($id);
        
        if ($item->image) {
            Storage::disk('public')->delete($item->image); // Bersihkan file
        }

        $item->delete();
        return redirect()->back()->with('success', 'Barang dihapus dari sistem.');
    }
}