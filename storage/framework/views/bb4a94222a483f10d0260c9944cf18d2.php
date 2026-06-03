<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Safora — Detail Laporan</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');*{font-family:'Inter',sans-serif;}</style>
</head>
<body class="bg-[#faf9f7] text-gray-900 antialiased min-h-screen">
    <?php $backUrl = route('partner.index'); $backLabel = 'Kembali'; ?>
    <?php echo $__env->make('partials.nav-auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="max-w-3xl mx-auto px-6 py-10">



        <?php
        $sc=['Submitted'=>'bg-gray-100 text-gray-600','Routed'=>'bg-blue-50 text-blue-700','Viewed'=>'bg-yellow-50 text-yellow-700','In Progress'=>'bg-orange-50 text-orange-700','Resolved'=>'bg-green-50 text-green-700'];
        $stages=[['Submitted','Diajukan'],['Routed','Diteruskan'],['Viewed','Ditinjau'],['In Progress','Diproses'],['Resolved','Selesai']];
        $ci=array_search($report->status,array_column($stages,0));
        $statusIndo = match($report->status) {
            'Submitted' => 'Diajukan',
            'Routed' => 'Diteruskan',
            'Viewed' => 'Ditinjau',
            'Assigned' => 'Diterima',
            'In Progress' => 'Diproses',
            'Resolved' => 'Selesai',
            'Rejected' => 'Ditolak',
            default => $report->status
        };
        ?>

        
        <div class="mb-6">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-1">LAPORAN #<?php echo e(strtoupper(substr($report->id,0,8))); ?></p>
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="font-unbounded text-3xl font-black text-gray-900"><?php echo e($report->category); ?></h1>
                    <div class="flex items-center gap-3 mt-1 text-sm text-gray-400 flex-wrap">
                        <?php if($report->anonymous): ?><span class="bg-purple-50 text-purple-700 text-xs px-2 py-0.5 rounded-full font-semibold">Anonim</span><?php endif; ?>
                        <span><?php echo e($report->created_at->format('d M Y, H:i')); ?></span>
                        <?php if($report->incident_date): ?><span class="bg-gray-150 text-gray-700 text-xs px-2 py-0.5 rounded-full font-semibold">🗓️ Kejadian: <?php echo e(\Carbon\Carbon::parse($report->incident_date)->format('d M Y, H:i')); ?></span><?php endif; ?>
                        <?php if($report->latitude): ?><a href="https://maps.google.com/?q=<?php echo e($report->latitude); ?>,<?php echo e($report->longitude); ?>" target="_blank" class="text-red-700 hover:text-red-800 text-xs underline">📍 Lihat Peta</a><?php endif; ?>
                    </div>
                    <?php if($canViewSensitive && $report->user && $report->user->phone): ?>
                    <div class="mt-2 text-sm text-gray-700 font-semibold">
                        📞 <a href="tel:<?php echo e($report->user->phone); ?>" class="text-blue-600 hover:underline"><?php echo e($report->user->phone); ?></a>
                    </div>
                    <?php endif; ?>
                </div>
                <span class="px-3 py-1.5 rounded-full text-sm font-semibold <?php echo e($sc[$report->status]??'bg-gray-100 text-gray-600'); ?> whitespace-nowrap"><?php echo e($statusIndo); ?></span>
            </div>
            
            <?php if($isHandling): ?>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="/chat/report/<?php echo e($report->id); ?>" class="bg-gray-900 hover:bg-gray-700 text-white px-6 py-2.5 rounded-xl font-semibold text-sm transition text-center">💬 Lanjut Chat</a>
                <?php if($report->status !== 'Resolved'): ?>
                <form method="POST" action="<?php echo e(route('partner.status', $report->id)); ?>" class="inline-block">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="status" value="Resolved">
                    <button type="submit" class="bg-green-700 hover:bg-green-800 text-white px-6 py-2.5 rounded-xl font-semibold text-sm transition">✅ Tandai Selesai</button>
                </form>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if($report->handler_partner_id === null && isset($isPending) && $isPending): ?>
            <div class="mt-6">
                <form method="POST" action="<?php echo e(route('partner.report.accept', $report->id)); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="bg-red-700 hover:bg-red-800 text-white px-6 py-3 rounded-xl font-black text-sm transition shadow-md">
                        TERIMA KASUS
                    </button>
                </form>
            </div>
            <?php endif; ?>
        </div>

        <?php if($report->description): ?>
        <div class="bg-white border border-gray-200 rounded-2xl p-5 mb-6">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2">Deskripsi</p>
            <p class="text-gray-700"><?php echo e($report->description); ?></p>
        </div>
        <?php endif; ?>

        
        <?php if($isHandling): ?>
        <div class="bg-white border border-gray-200 rounded-2xl p-6 mb-6">
            <h2 class="font-bold text-gray-900 mb-4">Update Status</h2>
            <form action="/partner/report/<?php echo e($report->id); ?>/status" method="POST" class="flex gap-3">
                <?php echo csrf_field(); ?>
                <select name="status" class="flex-1 border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-2.5 text-sm focus:outline-none bg-white">
                    <?php $__currentLoopData = ['Submitted' => 'Diajukan', 'Routed' => 'Diteruskan', 'Viewed' => 'Ditinjau', 'In Progress' => 'Diproses', 'Resolved' => 'Selesai']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $lbl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($val); ?>" <?php echo e($report->status===$val?'selected':''); ?>><?php echo e($lbl); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <button type="submit" class="bg-gray-900 hover:bg-gray-700 text-white px-6 py-2.5 rounded-xl font-semibold text-sm transition">Update</button>
            </form>
        </div>
        <?php endif; ?>

        
        <div class="bg-white border border-gray-200 rounded-2xl p-6 mb-6">
            <h2 class="font-bold text-gray-900 mb-5">Timeline Status</h2>

            
            <div class="flex items-center mb-6">
                <?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i=>[$key,$label]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex flex-col items-center flex-1">
                    <div class="flex items-center w-full">
                        <?php if($i>0): ?><div class="flex-1 h-0.5 <?php echo e($i<=$ci?'bg-green-500':'bg-gray-200'); ?> transition-all"></div><?php endif; ?>
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0
                            <?php echo e($i<$ci?'bg-green-500 text-white':($i==$ci?'bg-green-500 text-white ring-4 ring-green-100':'bg-gray-100 text-gray-400')); ?>">
                            <?php if($i<$ci): ?><svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <?php elseif($i==$ci): ?><div class="w-2 h-2 bg-white rounded-full"></div>
                            <?php else: ?><?php echo e($i+1); ?><?php endif; ?>
                        </div>
                        <?php if($i<count($stages)-1): ?><div class="flex-1 h-0.5 <?php echo e($i<$ci?'bg-green-500':'bg-gray-200'); ?>"></div><?php endif; ?>
                    </div>
                    <p class="text-xs mt-1 <?php echo e($i<=$ci?'text-gray-700 font-semibold':'text-gray-400'); ?> text-center hidden sm:block"><?php echo e($label); ?></p>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div class="space-y-3 border-t border-gray-100 pt-4">
                <?php $__currentLoopData = $report->statusLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $logStatusIndo = match($log->new_status) {
                        'Submitted' => 'Diajukan',
                        'Routed' => 'Diteruskan',
                        'Viewed' => 'Ditinjau',
                        'Assigned' => 'Diterima',
                        'In Progress' => 'Diproses',
                        'Resolved' => 'Selesai',
                        'Rejected' => 'Ditolak',
                        default => $log->new_status
                    };
                ?>
                <div class="flex items-start gap-3">
                    <div class="w-2 h-2 bg-red-500 rounded-full mt-1.5 flex-shrink-0"></div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900"><?php echo e($logStatusIndo); ?></p>
                        <p class="text-xs text-gray-400"><?php echo e(\Carbon\Carbon::parse($log->changed_at)->format('d/m/Y, H:i:s')); ?></p>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        
        <div class="bg-white border border-gray-200 rounded-2xl p-6 mb-6">
            <h2 class="font-bold text-gray-900 mb-4">Bukti dari Korban (<?php echo e($report->evidences->count()); ?>)</h2>
            <?php if(!$canViewSensitive): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm font-semibold">Bukti hanya dapat dilihat oleh mitra yang menangani kasus ini, atau jika permintaan belum kadaluarsa.</div>
            <?php elseif($report->evidences->count() > 0): ?>
            <div class="space-y-2">
                <?php $__currentLoopData = $report->evidences; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex items-center justify-between bg-gray-50 rounded-xl px-4 py-3">
                    <div>
                        <p class="text-sm text-gray-700"><?php echo e($ev->file_type); ?></p>
                        <p class="text-xs text-gray-400 font-mono"><?php echo e(substr($ev->file_hash,0,28)); ?>...</p>
                        <p class="text-xs text-gray-400"><?php echo e(\Carbon\Carbon::parse($ev->uploaded_at)->format('d M Y, H:i')); ?></p>
                    </div>
                    <a href="<?php echo e(str_starts_with($ev->file_url, 'data:') ? $ev->file_url : url('/evidences/view/' . basename($ev->file_url))); ?>" target="_blank" class="text-red-700 hover:text-red-800 text-sm font-semibold transition">Buka →</a>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <?php else: ?>
            <p class="text-gray-400 text-sm">Belum ada bukti diupload.</p>
            <?php endif; ?>
        </div>




    </div>
</body>
</html>

<?php /**PATH D:\CODING\olivia_final\resources\views/pages/partner/show.blade.php ENDPATH**/ ?>