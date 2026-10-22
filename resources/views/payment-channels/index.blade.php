<x-app-layout>
    <x-slot name="header">
        Metode Pembayaran (Midtrans)
    </x-slot>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <div>
                <h3 class="font-bold text-gray-800 text-lg">Atur Channel Pembayaran</h3>
                <p class="text-xs text-gray-500 mt-1">Nyalakan atau matikan metode pembayaran yang muncul di aplikasi wali murid.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold">
                    <tr>
                        <th class="px-6 py-4">Logo</th>
                        <th class="px-6 py-4">Nama Metode</th>
                        <th class="px-6 py-4">Kategori</th>
                        <th class="px-6 py-4 text-center">Status (Saklar)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($channels as $channel)
                    <tr class="hover:bg-gray-50/50 transition duration-150">
                        <td class="px-6 py-4">
                            <div class="w-16 h-10 bg-white border border-gray-200 rounded-lg p-1 flex items-center justify-center shadow-sm">
                                <img src="{{ $channel->logo }}" alt="{{ $channel->name }}" class="max-h-full max-w-full object-contain">
                            </div>
                        </td>
                        <td class="px-6 py-4 font-semibold text-gray-800">
                            {{ $channel->name }}
                            <div class="text-xs text-gray-400 font-normal mt-0.5">Kode: {{ $channel->code }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-600 uppercase tracking-wider">
                                {{ str_replace('_', ' ', $channel->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer toggle-checkbox" 
                                       data-id="{{ $channel->id }}" 
                                       {{ $channel->is_active ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer 
                                            peer-checked:after:translate-x-full peer-checked:after:border-white 
                                            after:content-[''] after:absolute after:top-[2px] after:left-[2px] 
                                            after:bg-white after:border-gray-300 after:border after:rounded-full 
                                            after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0ea5e9]">
                                </div>
                            </label>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggles = document.querySelectorAll('.toggle-checkbox');
            
            toggles.forEach(toggle => {
                toggle.addEventListener('change', function () {
                    let channelId = this.getAttribute('data-id');
                    let isChecked = this.checked;

                    // Hit Endpoint Toggle pakai Fetch API
                    fetch(`/settings/payment-channels/${channelId}/toggle`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.status === 'success') {
                            // Munculin Notif Pojok Kanan Atas pakai SweetAlert2 bawaan layout lu
                            const Toast = Swal.mixin({
                                toast: true, position: 'top-end', showConfirmButton: false, timer: 3000,
                                timerProgressBar: true, didOpen: (toast) => {
                                    toast.addEventListener('mouseenter', Swal.stopTimer)
                                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                                }
                            });
                            Toast.fire({ icon: 'success', title: data.message });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        // Kalau error/gagal nyambung, balikin posisi saklar ke semula
                        this.checked = !isChecked;
                        Swal.fire({ icon: 'error', title: 'Gagal', text: 'Terjadi kesalahan sistem.' });
                    });
                });
            });
        });
    </script>
</x-app-layout>