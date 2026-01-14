<?php

namespace App\Http\Controllers;

use App\Models\PosBundle;
use App\Models\PosItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosBundleController extends Controller
{
    // 1. LIST DATA PAKET
    public function index()
    {
        $bundles = PosBundle::withCount('items')->latest()->get();
        return view('pos.bundles.index', compact('bundles'));
    }

    // 2. FORM TAMBAH PAKET
    public function create()
    {
        // Ambil semua barang buat dipilih di dropdown
        $products = PosItem::where('stock', '>', 0)->get(); 
        return view('pos.bundles.create', compact('products'));
    }

    // 3. LOGIC SIMPAN (Ngeracik Resep)
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'products' => 'required|array', // Array ID Barang
            'quantities' => 'required|array', // Array Jumlah Barang
        ]);

        try {
            DB::transaction(function () use ($request) {
                // A. Simpan Header Paket
                $bundle = PosBundle::create([
                    'name' => $request->name,
                    'price' => $request->price,
                    'is_active' => true
                ]);

                // B. Simpan Isi Paket (Looping)
                foreach ($request->products as $index => $productId) {
                    if ($productId) {
                        $bundle->items()->create([
                            'pos_item_id' => $productId,
                            'quantity' => $request->quantities[$index] ?? 1
                        ]);
                    }
                }
            });

            return redirect()->route('pos.bundles.index')->with('success', 'Paket Bundling Berhasil Dibuat!');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat paket: ' . $e->getMessage());
        }
    }

    // 4. HAPUS PAKET
    public function destroy($id)
    {
        PosBundle::find($id)->delete();
        return back()->with('success', 'Paket berhasil dihapus.');
    }
}