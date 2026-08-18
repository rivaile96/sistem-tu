<?php

namespace App\Http\Controllers;

use App\Models\BillItem;
use App\Models\Kelas;
use App\Models\PosBundle;
use App\Models\PosItem;
use App\Models\Student;
use App\Models\StudentBill;
use App\Services\FinancialAuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosBundleController extends Controller
{
    public function index()
    {
        $bundles  = PosBundle::with('items.product')->latest()->paginate(12);
        $products = PosItem::where('stock', '>', 0)->get();

        // Hitung stats di controller — bukan di view — agar tidak crash
        // saat $bundles adalah Paginator (bukan Collection).
        $totalBundles  = PosBundle::count();
        $activeBundles = PosBundle::where('is_active', true)->count();
        $avgItems      = $totalBundles > 0
            ? round(PosBundle::withCount('items')->get()->avg('items_count'), 1)
            : 0;

        return view('pos.bundles.index', compact('bundles', 'products', 'totalBundles', 'activeBundles', 'avgItems'));
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
        $bundle   = PosBundle::with('items.product')->findOrFail($id);
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

    // =========================================================================
    // GENERATE BILLS — Bundle → StudentBill
    // =========================================================================

    /**
     * Show form to generate bills from a bundle.
     */
    public function generateBillsForm(PosBundle $bundle)
    {
        $bundle->load('items.product');
        $kelasList = Kelas::aktif()->orderBy('tingkat')->orderBy('nama_kelas')->get();
        $students  = Student::whereIn('status', ['active', 'calon_siswa'])
            ->with('kelas')
            ->orderBy('name')
            ->get();

        return view('pos.bundles.generate-bills', compact('bundle', 'kelasList', 'students'));
    }

    /**
     * Execute bundle → StudentBill generation for selected students.
     */
    public function generateBills(Request $request, PosBundle $bundle)
    {
        $request->validate([
            'student_ids'     => 'required|array|min:1',
            'student_ids.*'   => 'exists:students,id',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_note'   => 'nullable|string|max:255',
        ]);

        $bundle->load('items.product');

        // Calculate bundle total from items
        $bundleTotal = $bundle->items->sum(function ($item) {
            return ($item->product->price ?? 0) * $item->quantity;
        });

        if ($bundleTotal <= 0) {
            return back()->with('error', 'Bundle ini tidak memiliki item atau total harga = 0.');
        }

        $discountAmount = max(0, (float) ($request->discount_amount ?? 0));
        $discountNote   = $request->discount_note ?? null;

        // Guard: discount cannot exceed bundle total
        if ($discountAmount >= $bundleTotal) {
            return back()
                ->with('error', 'Diskon tidak boleh melebihi atau sama dengan total bundle (Rp ' . number_format($bundleTotal, 0, ',', '.') . ').')
                ->withInput();
        }

        $finalAmount = $bundleTotal - $discountAmount;

        $created = 0;
        $skipped = 0;
        $errors  = [];

        DB::transaction(function () use (
            $request, $bundle, $bundleTotal, $discountAmount, $discountNote, $finalAmount,
            &$created, &$skipped, &$errors
        ) {
            foreach ($request->student_ids as $studentId) {
                $student = Student::find($studentId);

                if (! $student || ! in_array($student->status, ['active', 'calon_siswa'], true)) {
                    $errors[] = "Student ID {$studentId} tidak valid atau status bukan active/calon_siswa.";
                    $skipped++;
                    continue;
                }

                // Create the aggregate bill
                $bill = StudentBill::create([
                    'student_id'      => $student->id,
                    'name'            => $bundle->name,
                    'type'            => 'PAKET',
                    'amount'          => $finalAmount,
                    'original_amount' => $bundleTotal,
                    'discount_amount' => $discountAmount,
                    'discount_note'   => $discountNote,
                    'status'          => 'UNPAID',
                    'created_by'      => Auth::id(),
                ]);

                // Create detail items
                foreach ($bundle->items as $bundleItem) {
                    $product  = $bundleItem->product;
                    $price    = $product->price ?? 0;
                    $qty      = $bundleItem->quantity;
                    $subtotal = $price * $qty;

                    BillItem::create([
                        'student_bill_id' => $bill->id,
                        'item_name'       => $product->name,
                        'quantity'        => $qty,
                        'price'           => $price,
                        'subtotal'        => $subtotal,
                        'pos_bundle_id'   => $bundle->id,
                    ]);
                }

                FinancialAuditLogger::billCreated($bill);
                $created++;
            }
        });

        $msg = "Berhasil generate {$created} tagihan dari bundle '{$bundle->name}'";
        if ($skipped > 0) $msg .= ", {$skipped} dilewati";

        return redirect()->route('bills.index')
            ->with('success', $msg)
            ->with('import_errors', $errors);
    }
}
