<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Safora — Jadi Saksi</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap');*{font-family:'Inter',sans-serif;} h1,.font-unbounded{font-family:'Space Grotesk',sans-serif!important;}</style>
</head>
<body class="bg-[#faf9f7] text-gray-900 antialiased min-h-screen">
    <?php
        $backUrl = request()->headers->get('referer') ?: (auth()->check() ? route('dashboard') : '/');
        $backLabel = 'Kembali';
        $showBrand = false;
    ?>
    <?php echo $__env->make('partials.nav-auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="max-w-lg mx-auto px-6 py-12">
        <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-3">COMMUNITY WITNESS</p>
<h1 class="font-unbounded text-3xl font-semibold text-gray-900 mb-2">Bantu dengan buktimu.</h1>
        <p class="text-gray-500 text-sm mb-8">Upload bukti untuk laporan yang kamu saksikan. Bersifat rahasia — hanya bisa diakses korban dan mitra terverifikasi.</p>

        <?php if(session('success')): ?><div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl mb-6 text-sm">✓ <?php echo e(session('success')); ?></div><?php endif; ?>
        <?php if($errors->any()): ?><div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 text-sm"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><p>• <?php echo e($e); ?></p><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div><?php endif; ?>

        <div class="bg-white border border-gray-200 rounded-2xl p-6">
            <form action="/witness" method="POST" enctype="multipart/form-data" class="space-y-4">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">ID Laporan *</label>
                    <input type="text" name="report_id" value="<?php echo e(old('report_id', request('report_id'))); ?>" placeholder="ID laporan dari korban..."
                        class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition" required>
                    <p class="text-gray-400 text-xs mt-1">Minta ID dari korban atau lihat di URL tracking mereka.</p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">Nama (opsional)</label>
                        <input type="text" name="witness_name" placeholder="Anonim jika kosong" class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">No. HP (opsional)</label>
                        <input type="text" name="witness_phone" placeholder="628xxxxxxxxx" class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">Keterangan</label>
                    <textarea name="witness_note" rows="3" placeholder="Ceritakan apa yang kamu lihat..." class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition resize-none"><?php echo e(old('witness_note')); ?></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">Upload Bukti (opsional)</label>
                    <div class="border border-dashed border-gray-300 hover:border-gray-400 rounded-xl p-5 text-center transition cursor-pointer" onclick="document.getElementById('wf').click()">
                        <p class="text-2xl mb-1">📁</p>
                        <p class="text-gray-500 text-sm">Klik untuk pilih file</p>
                        <p class="text-gray-400 text-xs">Foto, video, audio — maks. 20MB</p>
                        <p id="wfn" class="text-green-600 text-xs mt-1 hidden"></p>
                    </div>
                    <input type="file" name="evidence_file" id="wf" class="hidden" onchange="document.getElementById('wfn').textContent='✓ '+this.files[0].name;document.getElementById('wfn').classList.remove('hidden')">
                </div>
                <div class="bg-gray-50 rounded-xl px-4 py-3 text-xs text-gray-500">🔒 Buktimu hanya bisa diakses korban dan mitra terpercaya.</div>
                <button type="submit" class="w-full bg-gray-900 hover:bg-gray-700 text-white py-3.5 rounded-xl font-bold text-sm transition">Kirim Bukti Kesaksian</button>
            </form>
        </div>
    </div>
</body>
</html>

<?php /**PATH D:\CODING\olivia_final\resources\views/pages/witness/index.blade.php ENDPATH**/ ?>