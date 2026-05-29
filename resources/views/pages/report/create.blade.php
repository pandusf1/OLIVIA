<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Safora — Buat Laporan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap');
        * { font-family: 'Space Grotesk', sans-serif; }
        h1 { font-family: 'Space Grotesk', sans-serif !important; }
        .font-unbounded { font-family: 'Space Grotesk', sans-serif !important; }
    </style>
</head>
<body class="bg-[#faf9f7] text-gray-900 antialiased min-h-screen">
    @php
        $backUrl = route('dashboard');
        $backLabel = 'Batal';
    @endphp
    @include('partials.nav-auth')
    <div class="max-w-lg mx-auto px-6 py-12">
        <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-3">LAPORAN BIASA</p>
        <h1 class="font-unbounded text-3xl font-bold text-gray-900 mb-2">Buat Laporan Baru</h1>
        <p class="text-gray-500 text-sm mb-8">Gunakan formulir ini untuk melaporkan kejadian yang sudah berlalu (bukan darurat). Kamu bisa menceritakan kronologi dan lokasi kejadian secara spesifik.</p>

        @if($errors->any())<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 text-sm" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition.duration.500ms>@foreach($errors->all() as $e)<p>• {{ $e }}</p>@endforeach</div>@endif

        <div class="bg-white border border-gray-200 rounded-2xl p-6">
            <form action="{{ route('report.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">Kategori Kejadian *</label>
                    <select name="category" class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition bg-white" required>
                        <option value="" disabled selected>Pilih Kategori...</option>
                        <option value="Salah Tangkap">Salah Tangkap / Kriminalisasi</option>
                        <option value="Pelecehan">Pelecehan Seksual</option>
                        <option value="Kekerasan">Kekerasan Fisik</option>
                        <option value="Kecelakaan">Kecelakaan Lalu Lintas</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">Tanggal Kejadian *</label>
                    <input type="date" name="incident_date" value="{{ old('incident_date') }}"
                        class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition bg-white" required>
                </div>
                
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">Lokasi Kejadian *</label>
                    <input type="text" name="location_text" value="{{ old('location_text') }}" placeholder="Contoh: Jl. Sudirman depan stasiun, Semarang"
                        class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition" required>
                </div>
                
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">Kronologi / Deskripsi *</label>
                    <textarea name="description" rows="4" placeholder="Ceritakan secara detail waktu, orang yang terlibat, dan urutan kejadian..." class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition resize-none" required>{{ old('description') }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">Upload Bukti (opsional)</label>
                    <div class="border border-dashed border-gray-300 hover:border-gray-400 rounded-xl p-5 text-center transition cursor-pointer" onclick="document.getElementById('evf').click()">
                        <p class="text-2xl mb-1">📁</p>
                        <p class="text-gray-500 text-sm">Klik untuk pilih file</p>
                        <p class="text-gray-400 text-xs">Bisa pilih lebih dari 1 (Foto, video, audio, dll — maks. 50MB/file)</p>
                    </div>
                    <input type="file" id="evf" name="evidences[]" class="hidden" multiple accept="*/*">
                    <div id="upload-list" class="mt-3 space-y-2"></div>
                    <div id="hidden-inputs"></div>
                </div>

                <div class="flex items-center justify-between border border-gray-200 rounded-xl px-4 py-3 mb-4 mt-2">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Sembunyikan Identitas</p>
                        <p class="text-xs text-gray-400">Laporan akan bersifat anonim</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="anonymous" value="1" class="sr-only peer">
                        <div class="w-10 h-6 bg-gray-200 peer-checked:bg-gray-900 rounded-full transition-all after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-4"></div>
                    </label>
                </div>
                
                <div class="bg-gray-50 rounded-xl px-4 py-3 text-xs text-gray-500">🔒 Buktimu hanya bisa diakses korban dan mitra terpercaya.</div>
                
                <button type="submit" class="w-full bg-gray-900 hover:bg-gray-700 text-white py-3.5 rounded-xl font-bold text-sm transition">Simpan Laporan & Upload Bukti</button>
            </form>
        </div>
    </div>
    
    <script>
        const fileInput = document.getElementById('evf');
        const uploadList = document.getElementById('upload-list');
        const form = document.querySelector('form');
        const submitBtn = form.querySelector('button[type="submit"]');
        let dataTransfer = new DataTransfer();

        // Validasi max 50MB per file
        const MAX_FILE_SIZE = 50 * 1024 * 1024;

        fileInput.addEventListener('change', function() {
            Array.from(this.files).forEach(file => {
                if (file.size > MAX_FILE_SIZE) {
                    alert('File ' + file.name + ' terlalu besar. Maksimal 50MB.');
                    return;
                }
                dataTransfer.items.add(file);
                renderFile(file, dataTransfer.items.length - 1);
            });
            // Update actual file input
            this.files = dataTransfer.files;
        });

        function renderFile(file, index) {
            const uniqueId = Math.random().toString(36).substring(2, 15) + index;
            
            const item = document.createElement('div');
            item.id = `upload-${uniqueId}`;
            item.className = "flex items-center justify-between bg-white border border-gray-100 p-3 rounded-xl shadow-sm mb-2";
            item.innerHTML = `
                <div class="flex-1 min-w-0 mr-3">
                    <p class="text-sm font-semibold text-gray-800 truncate">${file.name}</p>
                    <p class="text-[10px] text-gray-500">${(file.size / 1024 / 1024).toFixed(2)} MB</p>
                </div>
                <button type="button" class="text-red-500 hover:text-red-700 p-1" onclick="deleteFile('${file.name}', '${uniqueId}')">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            `;
            uploadList.appendChild(item);
        }

        function deleteFile(fileName, uniqueId) {
            document.getElementById(`upload-${uniqueId}`).remove();
            
            // Rebuild DataTransfer
            const dt = new DataTransfer();
            for (let i = 0; i < dataTransfer.files.length; i++) {
                if (dataTransfer.files[i].name !== fileName) {
                    dt.items.add(dataTransfer.files[i]);
                }
            }
            dataTransfer = dt;
            fileInput.files = dataTransfer.files;
        }

        form.addEventListener('submit', function(e) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                MENYIMPAN...
            `;
            submitBtn.classList.add('opacity-70', 'cursor-not-allowed');
        });
    </script>
</body>
</html>
