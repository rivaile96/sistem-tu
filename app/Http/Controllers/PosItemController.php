<?php

namespace App\Http\Controllers;

use App\Models\PosItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PosItemController extends Controller
{
    public function index(Request $request)
    {
        $query = PosItem::query();
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('category', 'like', '%' . $request->search . '%');
            });
        }
        $items = $query->orderBy('stock', 'asc')->paginate(10)->withQueryString();
        return view('pos.items.index', compact('items'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'stock'    => 'required|integer|min:0',
            'price'    => 'required|numeric|min:0',
            'image'    => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->only(['name', 'category', 'stock', 'price']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        PosItem::create($data);

        return redirect()->back()->with('success', 'Barang berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'category' => 'required|string|max:100',   // BUG FIX: category wajib saat update
            'stock'    => 'required|integer|min:0',
            'price'    => 'required|numeric|min:0',
            'image'    => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $item = PosItem::findOrFail($id);
        $data = $request->only(['name', 'category', 'stock', 'price']);

        if ($request->hasFile('image')) {
            if ($item->image) {
                Storage::disk('public')->delete($item->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $item->update($data);

        return redirect()->back()->with('success', 'Data barang diperbarui!');
    }

    public function destroy($id)
    {
        $item = PosItem::findOrFail($id);

        // BUG FIX: tangani FK constraint — barang yang masih dipakai di
        // pos_order_items atau pos_bundle_items tidak boleh dihapus.
        $usedInOrders  = $item->orderItems()->exists();
        $usedInBundles = $item->bundleItems()->exists();

        if ($usedInOrders || $usedInBundles) {
            $where = collect([
                $usedInOrders  ? 'riwayat transaksi' : null,
                $usedInBundles ? 'paket bundling'    : null,
            ])->filter()->implode(' dan ');

            return redirect()->back()->with(
                'error',
                "Barang \"{$item->name}\" tidak dapat dihapus karena masih digunakan di {$where}."
            );
        }

        if ($item->image) {
            Storage::disk('public')->delete($item->image);
        }

        $item->delete();

        return redirect()->back()->with('success', 'Barang berhasil dihapus dari sistem.');
    }
}
