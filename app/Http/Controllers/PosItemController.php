<?php

namespace App\Http\Controllers;

use App\Models\PosItem;
use Illuminate\Http\Request;

class PosItemController extends Controller
{
    // 1. Tampilkan Daftar Barang (Inventory)
    public function index(Request $request)
    {
        $query = PosItem::query();

        // Fitur Pencarian
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('category', 'like', '%' . $request->search . '%');
        }

        $items = $query->latest()->paginate(10);

        return view('pos.items.index', compact('items'));
    }

    // 2. Simpan Barang Baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        PosItem::create([
            'name' => $request->name,
            'category' => $request->category,
            'price' => $request->price,
            'stock' => $request->stock,
            'is_active' => true
        ]);

        return back()->with('success', 'Barang berhasil ditambahkan!');
    }

    // 3. Update Barang (Edit Harga/Stok)
    public function update(Request $request, $id)
    {
        $item = PosItem::findOrFail($id);

        $request->validate([
            'name' => 'required|string',
            'category' => 'required|string',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
        ]);

        $item->update($request->all());

        return back()->with('success', 'Data barang diperbarui!');
    }

    // 4. Hapus Barang
    public function destroy($id)
    {
        PosItem::destroy($id);
        return back()->with('success', 'Barang dihapus dari sistem.');
    }
}