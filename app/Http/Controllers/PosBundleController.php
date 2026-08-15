<?php

namespace App\Http\Controllers;

use App\Models\PosBundle;
use App\Models\PosItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosBundleController extends Controller
{
    public function index()
    {
        $bundles  = PosBundle::withCount('items')->latest()->get();
        $products = PosItem::where('stock', '>', 0)->get();

        return view('pos.bundles.index', compact('bundles', 'products'));
    }

    public function create()
    {
        $products = PosItem::where('stock', '>', 0)->get();
        return view('pos.bundles.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'price'        => 'required|numeric|min:0',
            'products'     => 'required|array',
            'products.*'   => 'nullable|exists:pos_items,id',
            'quantities'   => 'required|array',
            'quantities.*' => 'required|integer|min:1',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $bundle = PosBundle::create([
                    'name'      => $request->name,
                    'price'     => $request->price,
                    'is_active' => true,
                ]);

                foreach ($request->products as $index => $productId) {
                    if ($productId) {
                        $bundle->items()->create([
                            'pos_item_id' => $productId,
                            'quantity'    => $request->quantities[$index] ?? 1,
                        ]);
                    }
                }
            });

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Paket Bundling Berhasil Dibuat!']);
            }

            return redirect()->route('pos.bundles.index')->with('success', 'Paket Bundling Berhasil Dibuat!');

        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal membuat paket: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Gagal membuat paket: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $bundle   = PosBundle::with('items.posItem')->findOrFail($id);
        $products = PosItem::where('stock', '>', 0)->get();

        if (request()->wantsJson()) {
            return response()->json([
                'bundle'   => $bundle,
                'products' => $products,
            ]);
        }

        return view('pos.bundles.edit', compact('bundle', 'products'));
    }

    public function update(Request $request, $id)
    {
        $bundle = PosBundle::findOrFail($id);

        $request->validate([
            'name'         => 'required|string|max:255',
            'price'        => 'required|numeric|min:0',
            'products'     => 'required|array',
            'products.*'   => 'nullable|exists:pos_items,id',
            'quantities'   => 'required|array',
            'quantities.*' => 'required|integer|min:1',
        ]);

        try {
            DB::transaction(function () use ($request, $bundle) {
                $bundle->update([
                    'name'  => $request->name,
                    'price' => $request->price,
                ]);

                $bundle->items()->delete();

                foreach ($request->products as $index => $productId) {
                    if ($productId) {
                        $bundle->items()->create([
                            'pos_item_id' => $productId,
                            'quantity'    => $request->quantities[$index] ?? 1,
                        ]);
                    }
                }
            });

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Paket Bundling Berhasil Diperbarui!']);
            }

            return redirect()->route('pos.bundles.index')->with('success', 'Paket Bundling Berhasil Diperbarui!');

        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal update paket: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Gagal update paket: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $bundle = PosBundle::findOrFail($id);
            $nama   = $bundle->name;
            $bundle->items()->delete();
            $bundle->delete();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => "Paket {$nama} berhasil dihapus."]);
            }

            return back()->with('success', "Paket {$nama} berhasil dihapus.");

        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal menghapus paket: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Gagal menghapus paket: ' . $e->getMessage());
        }
    }
}
