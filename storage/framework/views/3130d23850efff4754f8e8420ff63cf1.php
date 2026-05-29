<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Safora — Buat Laporan</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap');
        * { font-family: 'Space Grotesk', sans-serif; }
        h1 { font-family: 'Space Grotesk', sans-serif !important; }
        .font-unbounded { font-family: 'Space Grotesk', sans-serif !important; }
    </style>
</head>
<body class="bg-[#faf9f7] text-gray-900 antialiased min-h-screen">
    <?php
        $backUrl = route('dashboard');
        $backLabel = 'Batal';
    ?>
    <?php echo $__env->make('partials.nav-auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="max-w-lg mx-auto px-6 py-12">
        <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-3">LAPORAN BIASA</p>
        <h1 class="font-unbounded text-3xl font-bold text-gray-900 mb-2">Buat Laporan Baru</h1>
        <p class="text-gray-500 text-sm mb-8">Gunakan formulir ini untuk melaporkan kejadian yang sudah berlalu (bukan darurat). Kamu bisa menceritakan kronologi dan lokasi kejadian secara spesifik.</p>

        <?php if($errors->any()): ?><div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 text-sm" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition.duration.500ms><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><p>• <?php echo e($e); ?></p><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div><?php endif; ?>

        <div class="bg-white border border-gray-200 rounded-2xl p-6">
            <form action="<?php echo e(route('report.store')); ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
                <?php echo csrf_field(); ?>
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
                    <input type="date" name="incident_date" value="<?php echo e(old('incident_date')); ?>"
                        class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition bg-white" required>
                </div>
                
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">Lokasi Kejadian *</label>
                    <input type="text" name="location_text" value="<?php echo e(old('location_text')); ?>" placeholder="Contoh: Jl. Sudirman depan stasiun, Semarang"
                        class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition" required>
                </div>
                
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">Kronologi / Deskripsi *</label>
                    <textarea name="description" rows="4" placeholder="Ceritakan secara detail waktu, orang yang terlibat, dan urutan kejadian..." class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition resize-none" required><?php echo e(old('description')); ?></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">Upload Bukti (opsional)</label>
                    <div class="border border-dashed border-gray-300 hover:border-gray-400 rounded-xl p-5 text-center transition cursor-pointer" onclick="document.getElementById('evf').click()">
                        <p class="text-2xl mb-1">📁</p>
                        <p class="text-gray-500 text-sm font-semibold">Klik untuk pilih file</p>
                        <p class="text-gray-400 text-[11px] mt-1">Bisa pilih lebih dari 1 (Gambar maks 20MB, Video maks 100MB, Audio maks 50MB, PDF maks 30MB)</p>
                    </div>
                    <input type="file" id="evf" name="evidences[]" class="hidden" multiple accept="*/*">
                    <div id="upload-list" class="mt-3 space-y-2"></div>
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
        let selectedFiles = [];

        fileInput.addEventListener('change', function() {
            Array.from(this.files).forEach(file => {
                let limit = 50 * 1024 * 1024; // Default 50MB
                let limitText = "50MB";
                
                if (file.type.startsWith('image/')) {
                    limit = 20 * 1024 * 1024;
                    limitText = "20MB";
                } else if (file.type.startsWith('video/')) {
                    limit = 100 * 1024 * 1024;
                    limitText = "100MB";
                } else if (file.type.startsWith('audio/')) {
                    limit = 50 * 1024 * 1024;
                    limitText = "50MB";
                } else if (file.type === 'application/pdf') {
                    limit = 30 * 1024 * 1024;
                    limitText = "30MB";
                }

                if (file.size > limit) {
                    alert(`File "${file.name}" terlalu besar. Batasan jenis file ini adalah ${limitText}.`);
                    return;
                }

                const fileId = Math.random().toString(36).substring(2, 15) + Date.now();
                selectedFiles.push({
                    id: fileId,
                    file: file
                });
                renderFile(file, fileId);
            });
            updateFileInput();
        });

        function updateFileInput() {
            const dt = new DataTransfer();
            selectedFiles.forEach(item => {
                dt.items.add(item.file);
            });
            fileInput.files = dt.files;
        }

        function getFileIcon(mimeType) {
            if (mimeType.startsWith('image/')) return '🖼️';
            if (mimeType.startsWith('video/')) return '🎥';
            if (mimeType.startsWith('audio/')) return '🎵';
            if (mimeType === 'application/pdf') return '📄';
            return '📁';
        }

        function renderFile(file, fileId) {
            const item = document.createElement('div');
            item.id = `upload-${fileId}`;
            item.className = "flex items-center justify-between bg-white border border-gray-150 p-3 rounded-xl shadow-sm mb-2 hover:border-gray-300 transition duration-200";
            item.innerHTML = `
                <div class="flex-1 min-w-0 mr-3 flex items-center gap-2.5">
                    <span class="text-xl shrink-0">${getFileIcon(file.type)}</span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-gray-800 truncate">${file.name}</p>
                        <p class="text-[10px] text-gray-500 font-mono">${(file.size / 1024 / 1024).toFixed(2)} MB</p>
                    </div>
                </div>
                <button type="button" class="text-red-500 hover:text-red-700 p-1.5 rounded-lg hover:bg-red-50 transition shrink-0" onclick="deleteFile('${fileId}')">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            `;
            uploadList.appendChild(item);
        }

        function deleteFile(fileId) {
            const element = document.getElementById(`upload-${fileId}`);
            if (element) element.remove();
            
            selectedFiles = selectedFiles.filter(item => item.id !== fileId);
            updateFileInput();
        }

        form.addEventListener('submit', function(e) {
            // Using setTimeout to guarantee the browser initiates form submission before disabling button
            setTimeout(() => {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-70', 'cursor-not-allowed');
                submitBtn.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block align-text-bottom" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    MENYIMPAN LAPORAN...
                `;
            }, 10);
        });
    </script>
</body>
</html>
<?php /**PATH D:\CODING\olivia_final\resources\views/pages/report/create.blade.php ENDPATH**/ ?>