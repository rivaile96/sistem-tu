<?php

namespace App\Http\Controllers;

use App\Models\PosBundle;
use App\Models\PosItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosBundleController extends Controller
{
    /**
     * 1. MENAMPILKAN DAFTAR PAKET (INDEX)
     */
    public function index()
    {
        // Mengambil data bundle terbaru beserta jumlah item di dalamnya
        $bundles = PosBundle::withCount('items')->latest()->get();
        
        return view('pos.bundles.index', compact('bundles'));
    }

    /**
     * 2. FORM TAMBAH PAKET BARU (CREATE)
     */
    public function create()
    {
        // Ambil barang yang stoknya tersedia (> 0) untuk dipilih
        $products = PosItem::where('stock', '>', 0)->get();
        
        return view('pos.bundles.create', compact('products'));
    }

    /**
     * 3. SIMPAN PAKET BARU KE DATABASE (STORE)
     */
    public function store(Request $request)
    {
        // Validasi Input
        $request->validate([
            'name'       => 'required|string|max:255',
            'price'      => 'required|numeric|min:0',
            'products'   => 'required|array', // Harus ada array ID barang
            'products.*' => 'nullable|exists:pos_items,id', // Pastikan ID barang valid
            'quantities' => 'required|array',
            'quantities.*' => 'required|integer|min:1',
        ]);

        try {
            // Gunakan Transaction agar data aman (kalau gagal, tidak ada sampah data)
            DB::transaction(function () use ($request) {
                
                // A. Buat Header Paket
                $bundle = PosBundle::create([
                    'name'      => $request->name,
                    'price'     => $request->price,
                    'is_active' => true
                ]);

                // B. Simpan Rincian Item Paket
                foreach ($request->products as $index => $productId) {
                    // Cek jika productId tidak kosong (kadang select option kirim null)
                    if ($productId) {
                        $bundle->items()->create([
                            'pos_item_id' => $productId,
                            'quantity'    => $request->quantities[$index] ?? 1
                        ]);
                    }
                }
            });

            return redirect()->route('pos.bundles.index')->with('success', 'Paket Bundling Berhasil Dibuat!');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat paket: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * 4. FORM EDIT PAKET (EDIT)
     * Method ini yang sebelumnya error "undefined method"
     */
    public function edit($id)
    {
        // Ambil data paket beserta item-itemnya
        $bundle = PosBundle::with('items')->findOrFail($id);
        
        // LOGIC PENTING:
        // Kita perlu menampilkan barang untuk dipilih di dropdown.
        // Tampilkan barang yang Stoknya > 0 ATAU Barang yang SUDAH ADA di paket ini.
        // (Supaya kalau barang stoknya 0 tapi dia bagian dari paket, tidak error/hilang saat edit).
        
        $currentProductIds = $bundle->items->pluck('pos_item_id')->toArray();
        
        $products = PosItem::where('stock', '>', 0)
                            ->orWhereIn('id', $currentProductIds)
                            ->get();

        return view('pos.bundles.edit', compact('bundle', 'products'));
    }

    /**
     * 5. SIMPAN PERUBAHAN PAKET (UPDATE)
     * Method ini menangani request PUT dari form edit
     */
    public function update(Request $request, $id)
    {
        // Validasi sama seperti store
        $request->validate([
            'name'       => 'required|string|max:255',
            'price'      => 'required|numeric|min:0',
            'products'   => 'required|array',
            'quantities' => 'required|array',
        ]);

        try {
            DB::transaction(function () use ($request, $id) {
                $bundle = PosBundle::findOrFail($id);

                // A. Update Header Paket (Nama & Harga)
                $bundle->update([
                    'name'  => $request->name,
                    'price' => $request->price,
                ]);

                // B. Update Isi Item
                // Cara paling aman & bersih: Hapus semua item lama, lalu buat ulang item baru.
                // Ini menghindari kerumitan ngecek mana yg ditambah/dihapus/diedit qty-nya.
                
                $bundle->items()->delete(); // Hapus item lama

                // Buat ulang item baru berdasarkan form
                foreach ($request->products as $index => $productId) {
                    if ($productId) {
                        $bundle->items()->create([
                            'pos_item_id' => $productId,
                            'quantity'    => $request->quantities[$index] ?? 1
                        ]);
                    }
                }
            });

            return redirect()->route('pos.bundles.index')->with('success', 'Paket Bundling Berhasil Diperbarui!');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal update paket: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * 6. HAPUS PAKET (DESTROY)
     */
    public function destroy($id)
    {
        try {
            $bundle = PosBundle::findOrFail($id);
            
            // Hapus paket (Item di dalamnya biasanya otomatis terhapus jika settingan database cascade, 
            // tapi kita bisa hapus manual juga biar aman)
            $bundle->items()->delete(); 
            $bundle->delete();

            return back()->with('success', 'Paket berhasil dihapus.');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus paket: ' . $e->getMessage());
        }
    }
}