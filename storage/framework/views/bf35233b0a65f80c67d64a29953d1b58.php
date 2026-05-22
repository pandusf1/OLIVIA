<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Safora - Dashboard Admin</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="min-h-screen bg-gray-50 text-gray-900 antialiased">
<?php
    $backUrl = null;
    $showBrand = true;
    $statusClass = [
        'Submitted' => 'bg-gray-100 text-gray-700',
        'Routed' => 'bg-blue-100 text-blue-800',
        'Viewed' => 'bg-yellow-100 text-yellow-900',
        'In Progress' => 'bg-orange-100 text-orange-800',
        'Resolved' => 'bg-green-100 text-green-800',
    ];
?>
<?php echo $__env->make('partials.nav-auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <?php if(session('success')): ?>
        <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <header class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-gray-500">Pusat Monitoring</p>
            <h1 class="mt-1 text-3xl font-black text-gray-950">Dashboard Admin</h1>
            <p class="mt-1 text-sm text-gray-500">Pantau laporan, respons partner, dan aktivitas platform Safora.</p>
        </div>
        <a href="<?php echo e(route('admin.partners')); ?>" class="rounded-lg bg-gray-950 px-5 py-3 text-sm font-bold text-white transition hover:bg-gray-800">Manajemen Partner</a>
    </header>

    <section class="mb-8 grid grid-cols-2 gap-3 lg:grid-cols-6">
        <?php $__currentLoopData = [
            ['Total Laporan', $stats['reports'], 'text-gray-950'],
            ['Laporan Hari Ini', $stats['today'], 'text-blue-700'],
            ['Laporan Darurat', $stats['emergency'], 'text-red-700'],
            ['Belum Ditangani', $stats['unhandled'], 'text-orange-700'],
            ['Selesai', $stats['resolved'], 'text-green-700'],
            ['Partner Aktif', $stats['active_partners'], 'text-indigo-700'],
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label, $value, $color]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="rounded-lg border border-gray-200 bg-white p-4">
                <p class="text-xs font-bold uppercase text-gray-500"><?php echo e($label); ?></p>
                <p class="mt-2 text-3xl font-black <?php echo e($color); ?>"><?php echo e($value); ?></p>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </section>

    <section class="mb-8 rounded-lg border border-orange-200 bg-orange-50 p-5">
        <div class="mb-4 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-xl font-black text-orange-950">Laporan Tidak Tertangani</h2>
                <p class="text-sm text-orange-800">Tidak ada partner yang accept dan tidak ada routing pending yang masih valid.</p>
            </div>
            <span class="rounded-full bg-white px-3 py-1 text-sm font-bold text-orange-800"><?php echo e($unhandledReports->count()); ?> laporan</span>
        </div>

        <div class="overflow-x-auto rounded-lg border border-orange-200 bg-white">
            <table class="min-w-full divide-y divide-orange-100 text-sm">
                <thead class="bg-orange-100 text-left text-xs font-bold uppercase text-orange-900">
                    <tr>
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3">Waktu Masuk</th>
                        <th class="px-4 py-3">Durasi Tidak Tertangani</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $unhandledReports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="px-4 py-3 font-mono text-xs"><?php echo e(strtoupper(substr($report->id, 0, 8))); ?></td>
                            <td class="px-4 py-3 font-semibold"><?php echo e($report->category); ?></td>
                            <td class="px-4 py-3 text-gray-600"><?php echo e($report->created_at->format('d M Y, H:i')); ?></td>
                            <td class="px-4 py-3 text-gray-600"><?php echo e($report->created_at->diffForHumans(null, true)); ?></td>
                            <td class="px-4 py-3">
                                <form method="POST" action="<?php echo e(route('admin.reports.reroute', $report->id)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <button class="rounded-lg bg-orange-700 px-3 py-2 text-xs font-bold text-white transition hover:bg-orange-800">Re-route Manual</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">Tidak ada laporan tidak tertangani.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="mb-8 rounded-lg border border-gray-200 bg-white p-5">
        <h2 class="mb-4 text-xl font-black text-gray-950">Monitoring Partner</h2>
        <div class="overflow-x-auto">
            <table class="sortable min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-bold uppercase text-gray-500">
                    <tr>
                        <th class="cursor-pointer px-4 py-3">Partner Name</th>
                        <th class="cursor-pointer px-4 py-3">Tipe</th>
                        <th class="cursor-pointer px-4 py-3">Kota</th>
                        <th class="cursor-pointer px-4 py-3">Verified</th>
                        <th class="cursor-pointer px-4 py-3">Laporan Diterima</th>
                        <th class="cursor-pointer px-4 py-3">Rata-rata Respons</th>
                        <th class="cursor-pointer px-4 py-3">Aktif Sekarang</th>
                        <th class="cursor-pointer px-4 py-3">Status Keaktifan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__currentLoopData = $partners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $partner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-bold"><?php echo e($partner->partner_name); ?></td>
                            <td class="px-4 py-3"><?php echo e($partner->partner_type); ?></td>
                            <td class="px-4 py-3"><?php echo e($partner->city); ?></td>
                            <td class="px-4 py-3"><?php echo e($partner->verified ? 'Ya' : 'Tidak'); ?></td>
                            <td class="px-4 py-3"><?php echo e($partner->accepted_count); ?></td>
                            <td class="px-4 py-3"><?php echo e($partner->average_response_minutes !== null ? $partner->average_response_minutes . ' menit' : '-'); ?></td>
                            <td class="px-4 py-3"><?php echo e($partner->active_reports_count); ?></td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-3 py-1 text-xs font-bold <?php echo e($partner->activity_status === 'Aktif' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700'); ?>"><?php echo e($partner->activity_status); ?></span>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="mb-8 rounded-lg border border-gray-200 bg-white p-5">
        <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="text-xl font-black text-gray-950">Semua Laporan</h2>
                <p class="text-sm text-gray-500">Gunakan filter untuk audit operasional harian.</p>
            </div>
            <form method="GET" action="<?php echo e(route('admin.index')); ?>" class="grid gap-2 sm:grid-cols-2 lg:grid-cols-6">
                <select name="status" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">Semua Status</option>
                    <?php $__currentLoopData = ['Submitted','Routed','Viewed','In Progress','Resolved']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($status); ?>" <?php if(request('status') === $status): echo 'selected'; endif; ?>><?php echo e($status); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <select name="category" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">Semua Kategori</option>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($category); ?>" <?php if(request('category') === $category): echo 'selected'; endif; ?>><?php echo e($category); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <select name="report_type" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">Semua Tipe</option>
                    <option value="Emergency" <?php if(request('report_type') === 'Emergency'): echo 'selected'; endif; ?>>Emergency</option>
                    <option value="quick_emergency" <?php if(request('report_type') === 'quick_emergency'): echo 'selected'; endif; ?>>Quick Emergency</option>
                </select>
                <input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                <input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                <select name="partner_id" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">Semua Partner</option>
                    <?php $__currentLoopData = $partners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $partner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($partner->id); ?>" <?php if(request('partner_id') === $partner->id): echo 'selected'; endif; ?>><?php echo e($partner->partner_name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <button class="rounded-lg bg-gray-950 px-4 py-2 text-sm font-bold text-white lg:col-span-6">Terapkan Filter</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-bold uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">Tipe</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">User</th>
                        <th class="px-4 py-3">Partner Handle</th>
                        <th class="px-4 py-3">Waktu</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $handler = $report->partnerRoutings->firstWhere('status', 'accepted')?->partner; ?>
                        <tr>
                            <td class="px-4 py-3 font-mono text-xs"><?php echo e(strtoupper(substr($report->id, 0, 8))); ?></td>
                            <td class="px-4 py-3"><?php echo e($report->report_type); ?></td>
                            <td class="px-4 py-3 font-semibold"><?php echo e($report->category); ?></td>
                            <td class="px-4 py-3"><span class="rounded-full px-3 py-1 text-xs font-bold <?php echo e($statusClass[$report->status] ?? 'bg-gray-100 text-gray-700'); ?>"><?php echo e($report->status); ?></span></td>
                            <td class="px-4 py-3"><?php echo e($report->anonymous ? 'Anonim' : ($report->user?->name ?? 'Tanpa user')); ?></td>
                            <td class="px-4 py-3"><?php echo e($handler?->partner_name ?? '-'); ?></td>
                            <td class="px-4 py-3"><?php echo e($report->created_at->format('d M Y, H:i')); ?></td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <a href="<?php echo e(route('tracking.show', $report->id)); ?>" class="rounded-lg border border-gray-200 px-3 py-2 text-xs font-bold text-gray-700">Lihat Detail</a>
                                    <?php if(!$handler): ?>
                                        <form method="POST" action="<?php echo e(route('admin.reports.reroute', $report->id)); ?>"><?php echo csrf_field(); ?><button class="rounded-lg border border-orange-200 px-3 py-2 text-xs font-bold text-orange-800">Re-route</button></form>
                                    <?php endif; ?>
                                    <?php if($report->status !== 'Resolved'): ?>
                                        <form method="POST" action="<?php echo e(route('admin.reports.resolve', $report->id)); ?>"><?php echo csrf_field(); ?><button class="rounded-lg border border-green-200 px-3 py-2 text-xs font-bold text-green-800">Resolve Manual</button></form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="mb-8 rounded-lg border border-gray-200 bg-white p-5">
        <h2 class="mb-4 text-xl font-black text-gray-950">Aktivitas Terbaru</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-bold uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Waktu</th>
                        <th class="px-4 py-3">Aksi</th>
                        <th class="px-4 py-3">Entitas</th>
                        <th class="px-4 py-3">Dilakukan Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $auditLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="px-4 py-3"><?php echo e(optional($log->created_at)->format('d M Y, H:i')); ?></td>
                            <td class="px-4 py-3 font-semibold"><?php echo e($log->action); ?></td>
                            <td class="px-4 py-3"><?php echo e($log->target_type ?? '-'); ?> <?php echo e($log->target_id ? strtoupper(substr($log->target_id, 0, 8)) : ''); ?></td>
                            <td class="px-4 py-3"><?php echo e($log->user_id ? 'User ' . strtoupper(substr($log->user_id, 0, 8)) : 'system'); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">Belum ada aktivitas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<script>
    document.querySelectorAll('table.sortable').forEach(function (table) {
        table.querySelectorAll('th').forEach(function (header, index) {
            header.addEventListener('click', function () {
                const tbody = table.querySelector('tbody');
                const rows = Array.from(tbody.querySelectorAll('tr'));
                const asc = header.dataset.asc !== 'true';
                header.dataset.asc = asc ? 'true' : 'false';

                rows.sort(function (a, b) {
                    const left = a.children[index]?.textContent.trim() || '';
                    const right = b.children[index]?.textContent.trim() || '';
                    return asc
                        ? left.localeCompare(right, 'id', { numeric: true })
                        : right.localeCompare(left, 'id', { numeric: true });
                });

                rows.forEach(function (row) {
                    tbody.appendChild(row);
                });
            });
        });
    });
</script>
</body>
</html>
<?php /**PATH D:\CODING\olivia_final\resources\views\pages\admin\index.blade.php ENDPATH**/ ?>