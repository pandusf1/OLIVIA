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

        <?php if($errors->any()): ?><div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 text-sm"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><p>• <?php echo e($e); ?></p><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div><?php endif; ?>

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
                        <p class="text-gray-500 text-sm">Klik untuk pilih file</p>
                        <p class="text-gray-400 text-xs">Bisa pilih lebih dari 1 (Foto, video, audio, dll — maks. 20MB/file)</p>
                        <div id="evf-names" class="text-green-600 text-xs mt-2 hidden text-left flex flex-col items-center"></div>
                    </div>
                    <input type="file" name="evidences[]" id="evf" class="hidden" multiple onchange="
                        const el = document.getElementById('evf-names');
                        el.innerHTML = '';
                        if(this.files.length > 0) {
                            el.classList.remove('hidden');
                            for(let i=0; i<this.files.length; i++){
                                el.innerHTML += '<div>✓ ' + this.files[i].name + '</div>';
                            }
                        } else {
                            el.classList.add('hidden');
                        }
                    ">
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
</body>
</html>
<?php /**PATH D:\CODING\olivia_final\resources\views/pages/report/create.blade.php ENDPATH**/ ?>