<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">📦 Daftar Paket Bundling</h2>
                <a href="{{ route('pos.bundles.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-blue-700 transition">
                    + Buat Paket Baru
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                        <tr>
                            <th class="px-6 py-3">Nama Paket</th>
                            <th class="px-6 py-3">Harga Paket</th>
                            <th class="px-6 py-3 text-center">Jumlah Item</th>
                            <th class="px-6 py-3 text-center">Status</th>
                            <th class="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bundles as $bundle)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4 font-bold text-gray-800">{{ $bundle->name }}</td>
                            <td class="px-6 py-4 font-bold text-green-600">Rp {{ number_format($bundle->price, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded border border-gray-200">
                                    {{ $bundle->items_count }} Jenis Barang
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-green-600 font-bold text-xs">Aktif</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <form action="{{ route('pos.bundles.destroy', $bundle->id) }}" method="POST" onsubmit="return confirm('Hapus paket ini?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 font-bold">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center">Belum ada paket bundling.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>