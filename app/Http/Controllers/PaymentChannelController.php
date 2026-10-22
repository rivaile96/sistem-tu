<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentChannel;

class PaymentChannelController extends Controller
{
    public function index()
    {
        $channels = PaymentChannel::orderBy('type')->orderBy('name')->get();
        return view('payment-channels.index', compact('channels'));
    }

    // Fungsi buat nerima klik saklar (Toggle) dari AJAX
    public function toggle(Request $request, $id)
    {
        $channel = PaymentChannel::findOrFail($id);
        $channel->is_active = !$channel->is_active; // Dibalik (True jadi False, False jadi True)
        $channel->save();

        return response()->json([
            'status' => 'success', 
            'message' => $channel->name . ' berhasil di' . ($channel->is_active ? 'aktifkan' : 'nonaktifkan')
        ]);
    }
}