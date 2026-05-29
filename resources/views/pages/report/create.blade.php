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
                        <p class="text-gray-400 text-xs">Bisa pilih lebih dari 1 (Foto, video, audio, dll — maks. 20MB/file)</p>
                    </div>
                    <input type="file" id="evf" class="hidden" multiple accept="*/*">
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
        const hiddenInputs = document.getElementById('hidden-inputs');

        fileInput.addEventListener('change', function() {
            Array.from(this.files).forEach(file => {
                uploadFile(file);
            });
            // Reset input so the same file can be selected again if deleted
            this.value = '';
        });

        function uploadFile(file) {
            const uniqueId = Math.random().toString(36).substring(2, 15);
            
            // UI element for progress
            const item = document.createElement('div');
            item.id = `upload-${uniqueId}`;
            item.className = "flex items-center justify-between bg-white border border-gray-100 p-3 rounded-xl shadow-sm";
            item.innerHTML = `
                <div class="flex-1 min-w-0 mr-3">
                    <p class="text-sm font-semibold text-gray-800 truncate">${file.name}</p>
                    <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1.5 overflow-hidden" id="progress-container-${uniqueId}">
                        <div class="bg-green-500 h-1.5 rounded-full transition-all duration-300" style="width: 0%" id="progress-${uniqueId}"></div>
                    </div>
                    <div class="flex items-center gap-1 mt-1">
                        <svg class="w-3 h-3 text-green-500 hidden" id="check-${uniqueId}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        <p class="text-[10px] text-gray-500" id="text-${uniqueId}">Uploading 0%</p>
                    </div>
                </div>
                <button type="button" class="text-red-500 hover:text-red-700 p-1 hidden" id="delete-${uniqueId}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
                <div class="text-gray-400 p-1" id="loading-${uniqueId}">
                    <svg class="animate-spin w-4 h-4 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </div>
            `;
            uploadList.appendChild(item);

            const formData = new FormData();
            formData.append('evidence', file);
            formData.append('_token', '{{ csrf_token() }}');

            const xhr = new XMLHttpRequest();
            xhr.open('POST', '{{ route("report.evidence.upload") }}', true);

            xhr.upload.onprogress = function(e) {
                if (e.lengthComputable) {
                    const percent = Math.round((e.loaded / e.total) * 100);
                    document.getElementById(`progress-${uniqueId}`).style.width = percent + '%';
                    document.getElementById(`text-${uniqueId}`).innerText = `Uploading ${percent}%`;
                }
            };

            xhr.onload = function() {
                if (xhr.status === 200) {
                    const res = JSON.parse(xhr.responseText);
                    if (res.success) {
                        document.getElementById(`text-${uniqueId}`).innerText = 'Selesai';
                        document.getElementById(`text-${uniqueId}`).classList.replace('text-gray-500', 'text-green-600');
                        document.getElementById(`progress-container-${uniqueId}`).classList.add('hidden');
                        document.getElementById(`check-${uniqueId}`).classList.remove('hidden');
                        document.getElementById(`loading-${uniqueId}`).classList.add('hidden');
                        const delBtn = document.getElementById(`delete-${uniqueId}`);
                        delBtn.classList.remove('hidden');
                        
                        // Add hidden input
                        const hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = 'temp_evidences[]';
                        hidden.value = res.path;
                        hidden.id = `input-${uniqueId}`;
                        hiddenInputs.appendChild(hidden);

                        delBtn.onclick = function() { deleteFile(res.path, uniqueId); };
                    }
                } else {
                    document.getElementById(`text-${uniqueId}`).innerText = 'Gagal upload';
                    document.getElementById(`text-${uniqueId}`).classList.add('text-red-500');
                    document.getElementById(`progress-${uniqueId}`).classList.replace('bg-green-500', 'bg-red-500');
                    document.getElementById(`loading-${uniqueId}`).classList.add('hidden');
                }
            };

            xhr.onerror = function() {
                document.getElementById(`text-${uniqueId}`).innerText = 'Gagal koneksi';
                document.getElementById(`text-${uniqueId}`).classList.add('text-red-500');
                document.getElementById(`progress-${uniqueId}`).classList.replace('bg-green-500', 'bg-red-500');
                document.getElementById(`loading-${uniqueId}`).classList.add('hidden');
            };

            xhr.send(formData);
        }

        function deleteFile(path, uniqueId) {
            // Optimistically remove from UI
            document.getElementById(`upload-${uniqueId}`).remove();
            const hidden = document.getElementById(`input-${uniqueId}`);
            if (hidden) hidden.remove();

            // Send delete request
            fetch('{{ route("report.evidence.delete") }}', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ path: path })
            }).catch(e => console.log('Delete error:', e));
        }
    </script>
</body>
</html>
