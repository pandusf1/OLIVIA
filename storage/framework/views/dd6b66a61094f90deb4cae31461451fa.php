<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Safora — Evidence Locker</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap');*{font-family:'Inter',sans-serif;} h1,.font-unbounded{font-family:'Space Grotesk',sans-serif!important;}</style>
</head>
<body class="bg-[#faf9f7] text-gray-900 antialiased min-h-screen">
    <?php
        $backUrl = request()->headers->get('referer') ?: route('dashboard');
        $backLabel = 'Kembali';
        $showBrand = false;
    ?>
    <?php echo $__env->make('partials.nav-auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="max-w-3xl mx-auto px-6 py-10">
        <div class="mb-8">
            <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-1">EVIDENCE LOCKER</p>
<h1 class="font-unbounded text-3xl font-bold text-gray-900">Bukti tersimpan aman.</h1>
            <p class="text-gray-400 text-sm mt-1">Semua bukti otomatis ber-timestamp, hash, dan GPS. Klik laporan untuk melihat & tambah bukti.</p>
        </div>

        <?php if($reports->count() > 0): ?>
        <div class="space-y-4">
            <?php $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <div>
                        <p class="font-bold text-gray-900"><?php echo e($report->category); ?></p>
                        <p class="text-gray-400 text-xs"><?php echo e($report->created_at->format('d M Y, H:i')); ?> · <?php echo e($report->evidences_count); ?> bukti</p>
                    </div>
                    <a href="/tracking/<?php echo e($report->id); ?>" class="text-red-700 hover:text-red-800 text-sm font-semibold transition">Lihat Laporan →</a>
                </div>
                <div class="divide-y divide-gray-50">
                    <?php $__currentLoopData = $report->evidences; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center justify-between px-5 py-3">
                        <div>
                            <p class="text-sm text-gray-700"><?php echo e($ev->file_type); ?></p>
                            <p class="text-xs text-gray-400 font-mono"><?php echo e(substr($ev->file_hash,0,32)); ?>...</p>
                            <p class="text-xs text-gray-400"><?php echo e(\Carbon\Carbon::parse($ev->uploaded_at)->format('d M Y, H:i')); ?></p>
                        </div>
                        <a href="<?php echo e(asset('storage/'.$ev->file_url)); ?>" target="_blank" class="text-red-700 hover:text-red-800 text-xs font-semibold transition">Buka →</a>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php else: ?>
        <div class="bg-white border border-gray-200 rounded-2xl p-12 text-center">
            <p class="text-4xl mb-3">🔒</p>
            <p class="font-bold text-gray-900 mb-1">Belum ada laporan.</p>
            <p class="text-gray-400 text-sm mb-4">Buat laporan darurat dan upload bukti untuk mengisi locker ini.</p>
            <a href="/emergency" class="inline-block bg-red-700 hover:bg-red-800 text-white px-5 py-2.5 rounded-xl font-semibold text-sm transition">Buat Laporan</a>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>

<?php /**PATH D:\CODING\olivia_final\resources\views/pages/evidence.blade.php ENDPATH**/ ?>