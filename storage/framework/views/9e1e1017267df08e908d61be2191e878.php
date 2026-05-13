<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SuraRa — Dashboard Admin</title>

    <?php echo app('Illuminate\Foundation\Vite')('resources/css/app.css'); ?>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

        *{
            font-family:'Inter',sans-serif;
        }
    </style>
</head>

<body class="bg-[#faf9f7] text-gray-900 antialiased min-h-screen">

    <?php $backUrl = null; ?>

    <?php echo $__env->make('partials.nav-auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="max-w-6xl mx-auto px-6 py-10">

        
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">

            <div>

                <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-1">
                    CONTROL CENTER
                </p>

                <h1 class="font-unbounded text-3xl font-black text-gray-900">
                    Dashboard Admin
                </h1>

                <p class="text-gray-400 text-sm mt-1">
                    Monitoring platform, partner, dan aktivitas laporan SuraRa.
                </p>

            </div>

            <div class="bg-white border border-gray-200 rounded-2xl px-5 py-3">

                <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">
                    Total Laporan
                </p>

                <p class="text-2xl font-black text-gray-900">
                    <?php echo e($stats['reports']); ?>

                </p>

            </div>

        </div>

        
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">

            <div class="bg-white border border-gray-200 rounded-2xl p-5">

                <p class="text-xs uppercase tracking-wider text-gray-400 font-semibold mb-2">
                    Emergency
                </p>

                <p class="text-3xl font-black text-red-700">
                    <?php echo e($stats['emergency']); ?>

                </p>

            </div>

            <div class="bg-white border border-gray-200 rounded-2xl p-5">

                <p class="text-xs uppercase tracking-wider text-gray-400 font-semibold mb-2">
                    Resolved
                </p>

                <p class="text-3xl font-black text-green-700">
                    <?php echo e($stats['resolved']); ?>

                </p>

            </div>

            <div class="bg-white border border-gray-200 rounded-2xl p-5">

                <p class="text-xs uppercase tracking-wider text-gray-400 font-semibold mb-2">
                    Partner
                </p>

                <p class="text-3xl font-black text-blue-700">
                    <?php echo e($stats['partners']); ?>

                </p>

            </div>

            <div class="bg-white border border-gray-200 rounded-2xl p-5">

                <p class="text-xs uppercase tracking-wider text-gray-400 font-semibold mb-2">
                    User
                </p>

                <p class="text-3xl font-black text-gray-900">
                    <?php echo e($stats['users']); ?>

                </p>

            </div>

            <div class="bg-white border border-gray-200 rounded-2xl p-5">

                <p class="text-xs uppercase tracking-wider text-gray-400 font-semibold mb-2">
                    Active Cases
                </p>

                <p class="text-3xl font-black text-orange-600">
                    <?php echo e($stats['reports'] - $stats['resolved']); ?>

                </p>

            </div>

        </div>

        
        <div class="grid md:grid-cols-2 gap-4 mb-8">

            
            <a href="<?php echo e(route('admin.partners')); ?>"
               class="bg-white border border-gray-200 hover:border-gray-300 rounded-3xl p-6 transition">

                <div class="flex items-center justify-between mb-5">

                    <div class="w-14 h-14 rounded-2xl bg-blue-50 flex items-center justify-center text-2xl">
                        🏢
                    </div>

                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">
                        Management
                    </span>

                </div>

                <h2 class="text-xl font-black text-gray-900">
                    Partner Management
                </h2>

                <p class="text-sm text-gray-400 mt-2">
                    Kelola akun partner, verifikasi, dan akses mitra.
                </p>

            </a>

            
            <div class="bg-white border border-gray-200 rounded-3xl p-6">

                <div class="flex items-center justify-between mb-5">

                    <div class="w-14 h-14 rounded-2xl bg-red-50 flex items-center justify-center text-2xl">
                        🚨
                    </div>

                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">
                        Live
                    </span>

                </div>

                <h2 class="text-xl font-black text-gray-900">
                    Monitoring System
                </h2>

                <p class="text-sm text-gray-400 mt-2">
                    Pantau seluruh aktivitas laporan dan status kasus aktif.
                </p>

            </div>

        </div>

        
        <div class="bg-white border border-gray-200 rounded-3xl p-6">

            <div class="flex items-center justify-between mb-6">

                <div>

                    <h2 class="font-black text-xl text-gray-900">
                        Laporan Terbaru
                    </h2>

                    <p class="text-sm text-gray-400 mt-1">
                        Aktivitas laporan terbaru di platform.
                    </p>

                </div>

                <div class="text-sm text-gray-400">
                    <?php echo e($reports->count()); ?> laporan
                </div>

            </div>

            <?php
                $sc = [
                    'Submitted' => 'bg-gray-100 text-gray-600',
                    'Routed' => 'bg-blue-50 text-blue-700',
                    'Viewed' => 'bg-yellow-50 text-yellow-700',
                    'In Progress' => 'bg-orange-50 text-orange-700',
                    'Resolved' => 'bg-green-50 text-green-700',
                ];
            ?>

            <?php if($reports->count() > 0): ?>

                <div class="space-y-3">

                    <?php $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                    <a href="/partner/report/<?php echo e($report->id); ?>"
                       class="flex items-center justify-between border border-gray-200 hover:border-gray-300 rounded-2xl px-5 py-4 transition">

                        <div class="flex-1 min-w-0">

                            <div class="flex items-center gap-2 flex-wrap mb-1">

                                <p class="font-bold text-gray-900">
                                    <?php echo e($report->category); ?>

                                </p>

                                <?php if($report->anonymous): ?>
                                    <span class="bg-purple-50 text-purple-700 text-xs px-2 py-0.5 rounded-full">
                                        Anonim
                                    </span>
                                <?php endif; ?>

                            </div>

                            <?php if($report->description): ?>
                                <p class="text-sm text-gray-400 truncate">
                                    <?php echo e($report->description); ?>

                                </p>
                            <?php endif; ?>

                            <p class="text-xs text-gray-400 mt-1">
                                <?php echo e($report->created_at->format('d M Y, H:i')); ?>

                            </p>

                        </div>

                        <span class="text-xs font-semibold px-3 py-1.5 rounded-full ml-4 <?php echo e($sc[$report->status] ?? 'bg-gray-100 text-gray-600'); ?>">
                            <?php echo e($report->status); ?>

                        </span>

                    </a>

                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                </div>

            <?php else: ?>

                <div class="text-center py-16">

                    <p class="text-5xl mb-4">
                        📭
                    </p>

                    <p class="text-gray-400">
                        Belum ada laporan masuk.
                    </p>

                </div>

            <?php endif; ?>

        </div>

    </div>

</body>
</html><?php /**PATH D:\CODING\olivia_final\resources\views/pages/admin/index.blade.php ENDPATH**/ ?>