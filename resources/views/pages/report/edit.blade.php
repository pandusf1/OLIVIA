<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Laporan #{{ strtoupper(substr($report->id, 0, 8)) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    <form action="{{ route('report.update', $report->id) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="mb-4">
                            <label for="category" class="block text-sm font-medium text-gray-700">Kategori Laporan</label>
                            <select name="category" id="category" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm" required>
                                <option value="Kekerasan" {{ $report->category === 'Kekerasan' ? 'selected' : '' }}>Kekerasan Fisik / KDRT</option>
                                <option value="Pelecehan & Bullying" {{ $report->category === 'Pelecehan & Bullying' ? 'selected' : '' }}>Pelecehan & Perundungan (Bullying)</option>
                                <option value="Salah Tangkap" {{ $report->category === 'Salah Tangkap' ? 'selected' : '' }}>Salah Tangkap / Kriminalisasi</option>
                                <option value="Konseling & Trauma" {{ $report->category === 'Konseling & Trauma' ? 'selected' : '' }}>Konseling & Pemulihan Trauma</option>
                                <option value="Sosial" {{ $report->category === 'Sosial' ? 'selected' : '' }}>Sosial / Anak/Lansia Terlantar</option>
                                <option value="Lainnya" {{ $report->category === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Mengubah kategori akan meneruskan ulang laporan Anda ke institusi yang relevan dan menyetel ulang waktu expired (15 menit).</p>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi Kejadian</label>
                            <textarea name="description" id="description" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">{{ old('description', $report->description) }}</textarea>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">Batal</a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
