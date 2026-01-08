<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Generate Tagihan SPP') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 p-8">
                
                <h3 class="text-xl font-bold text-gray-800 mb-2">Buat Tagihan Massal</h3>
                <p class="text-gray-500 text-sm mb-6">Tagihan akan dibuat untuk <strong>SEMUA SISWA</strong> yang aktif. Siswa yang sudah punya tagihan di bulan ini akan dilewati (tidak dobel).</p>

                <form action="{{ route('spp.store_generate') }}" method="POST">
                    @csrf
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Bulan SPP</label>
                        <input type="month" name="month" required 
                               class="w-full rounded-xl border-gray-300 focus:border-[#0ea5e9] focus:ring-[#0ea5e9] shadow-sm text-gray-700">
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nominal Tagihan (Rp)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 font-bold">Rp</span>
                            </div>
                            <input type="number" name="amount" value="350000" min="0" required 
                                   class="w-full pl-10 rounded-xl border-gray-300 focus:border-[#0ea5e9] focus:ring-[#0ea5e9] shadow-sm text-gray-700 font-bold text-lg">
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <a href="{{ route('spp.index') }}" class="w-full py-3 text-center rounded-xl border border-gray-300 text-gray-700 font-bold hover:bg-gray-50 transition">
                            Batal
                        </a>
                        <button type="submit" class="w-full py-3 text-center rounded-xl bg-[#0ea5e9] text-white font-bold hover:bg-sky-600 shadow-lg shadow-sky-200 transition">
                            Generate Sekarang
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</x-app-layout>