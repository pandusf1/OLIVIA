<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SuraRa — Dashboard Mitra</title>
    <?php echo app('Illuminate\Foundation\Vite')('resources/css/app.css'); ?>
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');*{font-family:'Inter',sans-serif;}</style>
</head>
<body class="bg-[#faf9f7] text-gray-900 antialiased min-h-screen">
<?php $backUrl = null; ?>
    <?php $showBrand = true; ?>
    <?php echo $__env->make('partials.nav-auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="max-w-5xl mx-auto px-6 py-10">
        <div class="flex items-center justify-between mb-8">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-1">MITRA TERVERIFIKASI</p>
                <h1 class="font-unbounded text-3xl font-black text-gray-900">Dashboard Mitra</h1>
                <p class="text-gray-400 text-sm mt-1">Semua laporan masuk. Klik untuk lihat detail.</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl px-4 py-2 text-sm">
                Total: <span class="font-black text-gray-900"><?php echo e($reports->count()); ?></span>
            </div>
        </div>

        
        <?php $filter = request('status','semua'); ?>
        <div class="flex gap-2 mb-6 overflow-x-auto pb-1">
            <?php $__currentLoopData = ['semua','Submitted','Routed','Viewed','In Progress','Resolved']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="?status=<?php echo e($s); ?>"
                class="whitespace-nowrap px-4 py-2 rounded-xl text-sm font-semibold transition
                <?php echo e($filter===$s ? 'bg-gray-900 text-white' : 'bg-white border border-gray-200 text-gray-500 hover:border-gray-300'); ?>">
                <?php echo e($s === 'semua' ? 'Semua' : $s); ?>

            </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <?php
        $filtered = $filter==='semua' ? $reports : $reports->where('status',$filter);
        $sc=['Submitted'=>'bg-gray-100 text-gray-600','Routed'=>'bg-blue-50 text-blue-700','Viewed'=>'bg-yellow-50 text-yellow-700','In Progress'=>'bg-orange-50 text-orange-700','Resolved'=>'bg-green-50 text-green-700'];
        ?>

        <?php if($filtered->count() > 0): ?>
        <div class="space-y-2">
            <?php $__currentLoopData = $filtered; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="/partner/report/<?php echo e($report->id); ?>"
                class="flex items-center justify-between bg-white border border-gray-200 hover:border-gray-300 rounded-xl px-5 py-4 transition">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-0.5 flex-wrap">
                        <p class="font-semibold text-gray-900"><?php echo e($report->category); ?></p>
                        <?php if($report->anonymous): ?><span class="bg-purple-50 text-purple-700 text-xs px-2 py-0.5 rounded-full">Anonim</span><?php endif; ?>
                        <?php if($report->evidences_count > 0): ?><span class="bg-gray-100 text-gray-500 text-xs px-2 py-0.5 rounded-full"><?php echo e($report->evidences_count); ?> bukti</span><?php endif; ?>
                    </div>
                    <?php if($report->description): ?><p class="text-gray-400 text-sm truncate"><?php echo e($report->description); ?></p><?php endif; ?>
                    <p class="text-gray-400 text-xs mt-0.5"><?php echo e($report->created_at->format('d M Y, H:i')); ?></p>
                </div>
                <span class="text-xs font-semibold px-3 py-1.5 rounded-full ml-4 <?php echo e($sc[$report->status]??'bg-gray-100 text-gray-600'); ?>"><?php echo e($report->status); ?></span>
            </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php else: ?>
        <div class="bg-white border border-gray-200 rounded-2xl p-12 text-center">
            <p class="text-4xl mb-3">📭</p>
            <p class="text-gray-400">Tidak ada laporan dengan status "<?php echo e($filter); ?>"</p>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php /**PATH D:\CODING\olivia_final\resources\views/pages/partner/index.blade.php ENDPATH**/ ?>