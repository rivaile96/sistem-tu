@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ route('spp.index') }}" class="inline-flex items-center text-gray-500 hover:text-[#0ea5e9] mb-6 transition">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Kembali ke Daftar Tagihan
    </a>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
            <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-6 h-6 text-[#0ea5e9]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                Generate Tagihan Massal
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                Fitur ini akan membuat tagihan SPP untuk <b>SEMUA SISWA</b> yang terdaftar aktif.
            </p>
        </div>
        
        <form id="generateForm" action="{{ route('spp.store_generate') }}" method="POST" class="p-8">
            @csrf
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Bulan & Tahun Tagihan</label>
                <input type="month" name="month" required 
                       class="w-full rounded-xl border-gray-300 focus:ring-[#0ea5e9] focus:border-[#0ea5e9] py-3 px-4 text-gray-700"
                       value="{{ date('Y-m') }}">
                <p class="text-xs text-gray-400 mt-2">Contoh: Jika memilih <b>Oktober 2024</b>, tagihan akan bernama "Oktober 2024".</p>
            </div>

            <div class="mb-8">
                <label class="block text-sm font-medium text-gray-700 mb-2">Nominal SPP (Rp)</label>
                <div class="relative">
                    <span class="absolute left-4 top-3.5 text-gray-400 font-bold">Rp</span>
                    <input type="number" name="amount" required min="0" placeholder="Contoh: 350000" value="350000"
                           class="w-full pl-12 pr-4 py-3 rounded-xl border-gray-300 focus:ring-[#0ea5e9] focus:border-[#0ea5e9] font-bold text-gray-800">
                </div>
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-8 flex gap-3">
                <svg class="w-6 h-6 text-yellow-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div>
                    <h4 class="text-sm font-bold text-yellow-800">Perhatian</h4>
                    <p class="text-xs text-yellow-700 mt-1">
                        Sistem otomatis <b>melewati (skip)</b> siswa yang sudah memiliki tagihan di bulan yang sama. Tidak akan ada tagihan ganda.
                    </p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-4">
                <a href="{{ route('spp.index') }}" class="px-6 py-3 rounded-xl text-gray-600 font-medium hover:bg-gray-100 transition">
                    Batal
                </a>
                
                <button type="button" onclick="confirmGenerate()" class="bg-[#0ea5e9] hover:bg-sky-600 text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-sky-200 transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                    Proses Generate
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function confirmGenerate() {
        // Panggil SweetAlert
        Swal.fire({
            title: 'Konfirmasi Generate',
            text: "Apakah Anda yakin ingin membuat tagihan massal untuk bulan ini?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0ea5e9', // Warna Biru
            cancelButtonColor: '#9ca3af',  // Warna Abu
            confirmButtonText: 'Ya, Proses!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            // Jika user klik 'Ya, Proses!'
            if (result.isConfirmed) {
                // Tampilkan Loading (Biar user tau sistem lagi kerja)
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Sedang membuat tagihan siswa, mohon tunggu.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Cari form berdasarkan ID 'generateForm' lalu submit
                document.getElementById('generateForm').submit();
            }
        });
    }
</script>
@endsection