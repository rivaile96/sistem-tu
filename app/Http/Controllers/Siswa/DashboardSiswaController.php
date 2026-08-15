<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\StudentBill;
use Illuminate\Support\Facades\Auth;

class DashboardSiswaController extends Controller
{
    public function index()
    {
        $student = Auth::guard('siswa')->user();

        $bills = StudentBill::where('student_id', $student->id)
                            ->orderByDesc('bill_year')
                            ->orderByDesc('bill_month')
                            ->get();

        $unpaidBills = $bills->whereIn('status', ['UNPAID', 'PARTIAL']);
        $paidBills   = $bills->where('status', 'PAID');
        $totalUnpaid = $unpaidBills->sum('amount');
        $totalPaid   = $paidBills->sum('amount');

        $schoolName = Setting::where('key', 'school_name')->value('value') ?? 'Sekolah';

        $thisMonthBills = StudentBill::where('student_id', $student->id)
                            ->where('bill_month', now()->month)
                            ->where('bill_year', now()->year)
                            ->orderBy('name')
                            ->get();

        return view('siswa.dashboard', compact(
            'student', 'bills', 'unpaidBills', 'paidBills',
            'totalUnpaid', 'totalPaid', 'schoolName', 'thisMonthBills'
        ));
    }

    public function detail(StudentBill $bill)
    {
        $student = Auth::guard('siswa')->user();

        // Pastikan tagihan milik siswa ini
        if ((int) $bill->student_id !== (int) $student->id) {
            abort(403);
        }

        $schoolName = Setting::where('key', 'school_name')->value('value') ?? 'Sekolah';

        return view('siswa.bill-detail', compact('bill', 'student', 'schoolName'));
    }

    public function struk(StudentBill $bill)
    {
        $student = Auth::guard('siswa')->user();

        if ((int) $bill->student_id !== (int) $student->id) {
            abort(403);
        }

        if ($bill->status !== 'PAID') {
            return redirect()->route('siswa.dashboard');
        }

        $schoolName    = Setting::where('key', 'school_name')->value('value') ?? 'Sekolah';
        $schoolAddress = Setting::where('key', 'school_address')->value('value') ?? '';

        return view('siswa.struk', compact('bill', 'student', 'schoolName', 'schoolAddress'));
    }
}
