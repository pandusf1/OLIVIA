<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking Laporan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <style>
        * { font-family: 'Space Grotesk', sans-serif; }
        h1 { font-family: 'Space Grotesk', sans-serif !important; }
        .font-unbounded { font-family: 'Space Grotesk', sans-serif !important; }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
    <?php echo $__env->make('partials.vercel-analytics', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</head>
<body class="min-h-screen bg-gray-50 text-gray-950 antialiased">
<?php
    $backUrl = request()->headers->get('referer') ?: (auth()->check() ? route('dashboard') : '/');
    $backLabel = 'Kembali';
    $showBrand = false;
?>
<?php echo $__env->make('partials.nav-auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<main class="mx-auto max-w-5xl px-4 pb-10 pt-5 sm:px-6">
    <section class="rounded-lg border border-red-100 bg-white p-5 shadow-sm">
        <div class="grid gap-4 lg:grid-cols-[1fr_280px] lg:items-start">
            <div class="min-w-0">
                <p class="text-xs font-bold uppercase tracking-widest text-red-700">Laporan #<span data-field="short_id"><?php echo e($livePayload['report']['short_id']); ?></span></p>
                <h1 class="mt-2 text-2xl font-black sm:text-3xl" data-field="current_status"><?php echo e($livePayload['current_status']); ?></h1>
                <p class="mt-3 max-w-2xl text-base leading-7 text-gray-700" data-field="human_message"><?php echo e($livePayload['human_message']); ?></p>
        <p class="mt-1 text-sm leading-6" data-field="next_instruction"><?php echo e($livePayload['next_instruction']); ?></p>
        <p class="mt-2 text-xs leading-5" data-field="escalation_message"><?php echo e($livePayload['escalation_message']); ?></p>
            </div>
            <div class="rounded-lg p-4">
                <p class="text-xs font-bold uppercase text-gray-500">Lokasi</p>
                <a data-field="maps_url" href="<?php echo e($livePayload['report']['location']['maps_url'] ?? '#'); ?>" target="_blank" class="mt-1 inline-block font-black text-red-700 underline mb-2">
                    <?php echo e($livePayload['report']['location']['verified'] ? 'GPS diterima' : 'GPS belum tersedia'); ?>

                </a>
                <div id="tracking-map" class="h-36 w-full rounded-lg z-0 border border-gray-200"></div>
            </div>
        </div>

        <div class="mt-5 flex flex-col sm:flex-row gap-3">
            <div class="flex-1 rounded-lg bg-gray-50 p-4">
                <p class="text-xs font-bold uppercase text-gray-500">Kategori</p>
                <p class="mt-1 font-black" data-field="category"><?php echo e($livePayload['report']['category']); ?></p>
            </div>
            <div class="flex-1 rounded-lg bg-gray-50 p-4 <?php echo e(empty($livePayload['report']['incident_date']) ? 'hidden' : ''); ?>" id="incident-date-container">
                <p class="text-xs font-bold uppercase text-gray-500">Waktu Kejadian</p>
                <p class="mt-1 font-black" data-field="incident_date"><?php echo e($livePayload['report']['incident_date'] ?? '-'); ?></p>
            </div>
            <div class="flex-1 rounded-lg bg-gray-50 p-4">
                <p class="text-xs font-bold uppercase text-gray-500">Estimasi Respons</p>
                <p class="mt-1 font-black" data-field="eta"><?php echo e($livePayload['eta']); ?></p>
            </div>
        </div>
        <?php
            $currUser = auth()->user();
            $isMitraUser = $currUser && $currUser->role === 'mitra';
            $pId = $isMitraUser ? $currUser->mitra_id : null;
            
            $pRouting = $isMitraUser ? $report->mitraRoutings()->where('mitra_id', $pId)->first() : null;
            $isChronoPending = ($pRouting && $pRouting->status === 'pending' && (is_null($pRouting->expires_at) || $pRouting->expires_at > now()));
            
            $canMitraAccept = ($report->handler_mitra_id === null && $isChronoPending);
            $isMitraHandling = ($isMitraUser && $report->handler_mitra_id === $pId);
        ?>

        <?php
            $isCreator = !$isMitraUser && (
                (auth()->check() && auth()->id() === $report->user_id)
                || ($report->user_id === null && in_array($report->id, session('my_reports', [])))
            );
            $showResolveButton = ($isCreator || $isTrustedContact) && in_array($report->status, ['In Progress', 'Assigned']);
        ?>

        <?php if($showResolveButton): ?>
            <div class="mt-4">
                <form action="/tracking/<?php echo e($report->id); ?>/resolve" method="POST" data-confirm="Apakah Anda yakin laporan ini telah tertangani dan Anda sudah aman?">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="w-full rounded-lg bg-green-600 px-4 py-2 text-sm font-black text-white hover:bg-green-700 transition">Laporan Selesai</button>
                </form>
            </div>
        <?php endif; ?>

        <?php if($isMitraUser): ?>
            <?php if($canMitraAccept): ?>
                <div class="mt-4">
                    <form method="POST" action="<?php echo e(route('mitra.report.accept', $report->id)); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="w-full rounded-lg bg-red-700 hover:bg-red-800 text-white px-5 py-3.5 text-sm font-black transition hover:scale-[1.01] duration-200 shadow-md">
                            TERIMA KASUS
                        </button>
                    </form>
                </div>
            <?php elseif($isMitraHandling && in_array($report->status, ['In Progress', 'Assigned', 'Viewed', 'Routed', 'Submitted'])): ?>
                <div class="mt-4">
                    <form method="POST" action="<?php echo e(route('mitra.status', $report->id)); ?>" data-confirm="Apakah Anda yakin kasus ini telah selesai ditangani?">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="status" value="Resolved">
                        <button type="submit" class="w-full rounded-lg bg-green-600 hover:bg-green-700 text-white px-5 py-3.5 text-sm font-black transition hover:scale-[1.01] duration-200 shadow-md">
                            Laporan Selesai
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </section>

            <?php
                $user = auth()->user();
                $isMitra = $user && $user->role === 'mitra';
                $cookieReports = [];
                if (request()->hasCookie('safora_my_reports')) {
                    $cookieReports = json_decode(request()->cookie('safora_my_reports'), true) ?: [];
                }
                $isReporter = !$isMitra && (
                    (auth()->check() && auth()->id() === $report->user_id)
                    || ($report->user_id === null && (
                        in_array($report->id, session('my_reports', []))
                        || in_array($report->id, $cookieReports)
                    ))
                );
                
                $isOtherMitraHandling = $isMitra && $report->handler_mitra_id !== null && $report->handler_mitra_id !== $user->mitra_id;
                
                $isRoutingExist = $isMitra && $report->mitraRoutings()
                    ->where('mitra_id', $user->mitra_id)
                    ->exists();
                $isAcceptedMitra = $isMitra && $report->mitraRoutings()
                    ->where('mitra_id', $user->mitra_id)
                    ->where('status', 'accepted')
                    ->exists();
                $hasDirectChatAccess = $isReporter || $isAcceptedMitra || $isRoutingExist;
            ?>

            <section class="mt-4 rounded-lg border border-gray-200 bg-white p-5">
                <!-- Heading & Toolbar -->
                <div class="flex items-center justify-between gap-3 flex-wrap border-b border-gray-100 pb-4 mb-4">
                    <div>
                        <h2 class="text-lg font-black text-gray-900">Aktivitas Laporan</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Kronologi, saksi, berkas bukti, dan chat koordinasi.</p>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <?php if(!$isOtherMitraHandling): ?>
                            <!-- Tombol Tambah Bukti -->
                            <button type="button" onclick="openEvidenceModal()" class="text-xs font-semibold text-gray-700 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-full transition border border-gray-200">
                                + Bukti
                            </button>
                            
                            <!-- Tombol Tambah Kronologi -->
                            <button id="btn-add-chronology" onclick="openChronologyModal()" 
                                    class="<?php echo e(($isReporter || $isTrustedContact) ? '' : 'hidden'); ?> text-xs font-semibold text-red-700 hover:text-red-800 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-full transition border border-red-100">
                                + Kronologi
                            </button>
                        <?php endif; ?>

                        <!-- Tombol Chat -->
                        <?php if($report->status !== 'Resolved'): ?>
                            <?php if($hasDirectChatAccess): ?>
                                <a href="/chat/report/<?php echo e($report->id); ?>"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-700 hover:bg-red-800 text-white transition shadow-sm"
                                   title="Buka Chat Laporan">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/></svg>
                                </a>
                            <?php else: ?>
                                <div id="chat-action" class="inline-flex items-center gap-1.5">
                                    <span id="chat-checking" class="text-[10px] text-gray-400 italic">Memeriksa lokasi...</span>
                                    <a id="chat-link" href="#" class="hidden items-center justify-center w-8 h-8 rounded-full bg-gray-900 hover:bg-gray-700 text-white transition shadow-sm" title="Buka Chat Laporan">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/></svg>
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Pesan Peringatan GPS untuk Saksi -->
                <div id="chat-no-gps" class="hidden text-xs text-gray-500 bg-gray-50 px-3 py-2.5 rounded-lg mb-3 flex flex-col gap-1.5">
                    <p class="font-medium">📍 Tidak bisa mengambil lokasi. Chat & tambah kronologi saksi hanya tersedia dalam radius 5 km.</p>
                    <button id="btn-retry-gps" type="button" onclick="retryGeolocation()" class="w-fit text-left text-red-700 font-bold underline hover:text-red-800 transition">
                        Beri Izin Lokasi & Coba Lagi
                    </button>
                </div>

                <!-- ID Laporan (for saksi/warga around) -->
                <div class="mb-4 bg-slate-50 border border-slate-200 rounded-xl p-3">
                    <p class="text-xs font-bold text-gray-500 uppercase">ID Laporan</p>
                    <code class="mt-1 block break-all text-xs font-mono text-gray-700 select-all cursor-pointer hover:text-black"><?php echo e($report->id); ?></code>
                </div>

                <!-- Isi Section: List Kronologi -->
                <div>
                    <h3 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-1.5">
                        <span>Kronologi Kejadian</span>
                    </h3>
                    <div id="chronology-list" class="space-y-3">
                        <?php $__empty_1 = true; $__currentLoopData = $report->chronologies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chrono): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="p-4 bg-slate-50 border border-slate-100 rounded-xl">
                                <div class="flex justify-between items-start gap-3 flex-wrap">
                                    <span class="text-xs font-bold text-slate-800 bg-slate-200 px-2 py-0.5 rounded">
                                        <?php if($chrono->role === 'Korban'): ?>
                                            Korban (<?php echo e($chrono->writer_name); ?>)
                                        <?php elseif($chrono->role === 'Mitra' || $chrono->role === 'Mitra'): ?>
                                            Mitra (<?php echo e($chrono->writer_name); ?>)
                                        <?php elseif($chrono->role === 'Saksi'): ?>
                                            Saksi (<?php echo e($chrono->writer_name); ?>)
                                        <?php else: ?>
                                            <?php echo e($chrono->role); ?> (<?php echo e($chrono->writer_name); ?>)
                                        <?php endif; ?>
                                    </span>
                                    <span class="text-[10px] text-gray-400 font-medium">
                                        <?php echo e($chrono->created_at->format('d M Y, H:i')); ?>

                                    </span>
                                </div>
                                <p class="text-sm text-gray-700 mt-2 leading-relaxed whitespace-pre-line"><?php echo e($chrono->description); ?></p>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="text-center py-6 text-gray-400 text-sm italic" id="chronology-empty-state">
                                Belum ada kronologi tambahan.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Isi Section: List Bukti -->
                <div class="mt-6 border-t border-gray-100 pt-6">
                    <h3 class="text-sm font-bold text-gray-900 mb-3">Bukti Kejadian</h3>
                    
                    <div id="evidence-list-container" class="space-y-6">
                        <?php if($report->evidences->count() > 0): ?>
                            <?php
                                $groupedEvidences = $report->evidences->groupBy('uploader_role');
                                $groupOrder = ['Korban', 'Saksi', 'Mitra'];
                            ?>
                            
                            <?php $__currentLoopData = $groupOrder; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if($groupedEvidences->has($role) && $groupedEvidences->get($role)->count() > 0): ?>
                                    <div class="bg-slate-50/50 border border-slate-100 rounded-xl p-4" id="evidence-group-<?php echo e($role); ?>">
                                        <h4 class="text-xs font-bold text-slate-800 bg-slate-200 px-2 py-1 rounded inline-block mb-3">
                                            <?php echo e($role); ?>

                                        </h4>
                                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3" id="evidence-grid-<?php echo e($role); ?>">
                                            <?php $__currentLoopData = $groupedEvidences->get($role); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $evidence): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php
                                                    $isImage = str_starts_with($evidence->file_type, 'image/');
                                                    $isVideo = str_starts_with($evidence->file_type, 'video/');
                                                    $isAudio = str_starts_with($evidence->file_type, 'audio/');
                                                    
                                                    $canView = $report->show_evidence 
                                                        || (auth()->check() && (auth()->id() === $report->user_id || auth()->user()->role === 'mitra'))
                                                        || ($report->user_id === null && in_array($report->id, session('my_reports', [])));
                                                        
                                                    $evidenceUrl = url('/evidences/view/' . $evidence->id);
                                                    
                                                    $cardBg = 'bg-blue-50/50 border-blue-150';
                                                    $badgeBg = 'bg-blue-100 text-blue-800';
                                                    $badgeLabel = 'BERKAS';
                                                    
                                                    if ($isImage) {
                                                        $cardBg = 'bg-rose-50/50 border-rose-150';
                                                        $badgeBg = 'bg-rose-100 text-rose-800';
                                                        $badgeLabel = 'FOTO';
                                                    } elseif ($isVideo) {
                                                        $cardBg = 'bg-amber-50/50 border-amber-150';
                                                        $badgeBg = 'bg-amber-100 text-amber-800';
                                                        $badgeLabel = 'VIDEO';
                                                    } elseif ($isAudio) {
                                                        $cardBg = 'bg-emerald-50/50 border-emerald-150';
                                                        $badgeBg = 'bg-emerald-100 text-emerald-800';
                                                        $badgeLabel = 'REKAMAN';
                                                    }
                                                ?>
                                                <a href="javascript:void(0)" 
                                                   data-url="<?php echo e($canView ? $evidenceUrl : '#'); ?>" 
                                                   data-type="<?php echo e($evidence->file_type); ?>"
                                                   onclick="<?php echo e($canView ? 'viewEvidence(this.dataset.url, this.dataset.type)' : 'alert(\'Bukti hanya bisa dibuka oleh korban / mitra\')'); ?>" 
                                                   class="aspect-square rounded-2xl border <?php echo e($cardBg); ?> p-4 relative group flex flex-col items-center justify-center transition-all duration-300 hover:scale-[1.03] hover:shadow-md">
                                                    <?php if($isImage): ?>
                                                        <svg class="w-10 h-10 text-rose-500 group-hover:scale-110 transition-transform duration-300 <?php echo e(!$canView ? 'blur-[3px]' : ''); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                        </svg>
                                                    <?php elseif($isVideo): ?>
                                                        <svg class="w-10 h-10 text-amber-500 group-hover:scale-110 transition-transform duration-300 <?php echo e(!$canView ? 'blur-[3px]' : ''); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                        </svg>
                                                    <?php elseif($isAudio): ?>
                                                        <svg class="w-10 h-10 text-emerald-500 group-hover:scale-110 transition-transform duration-300 <?php echo e(!$canView ? 'blur-[3px]' : ''); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                                                        </svg>
                                                    <?php else: ?>
                                                        <svg class="w-10 h-10 text-blue-500 group-hover:scale-110 transition-transform duration-300 <?php echo e(!$canView ? 'blur-[3px]' : ''); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                        </svg>
                                                    <?php endif; ?>
                                                    
                                                    <span class="mt-3 text-[10px] font-bold tracking-widest uppercase <?php echo e($badgeBg); ?> px-2 py-0.5 rounded-full"><?php echo e($badgeLabel); ?></span>
                                                    
                                                    <?php if(!$canView): ?>
                                                        <div class="absolute inset-0 bg-white/60 backdrop-blur-[4px] rounded-2xl flex items-center justify-center">
                                                            <div class="bg-white/80 p-2 rounded-full shadow-sm border border-gray-100 flex items-center justify-center">
                                                                <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                                </svg>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </a>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php else: ?>
                            <div class="text-center py-6 text-gray-400 text-sm italic" id="evidence-empty-state">
                                Belum ada berkas bukti diunggah.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

    <div class="mt-5 grid gap-5 lg:grid-cols-[1.1fr_.9fr]">
        <div class="space-y-5">
            <section id="assigned-card" class="hidden rounded-lg border border-green-200 bg-green-50 p-5">
                <p class="text-xs font-bold uppercase tracking-widest text-green-700">Sedang Menangani</p>
                <h2 class="mt-2 text-xl font-black text-green-950" data-field="assigned_name"></h2>
                <p class="mt-1 text-sm text-green-800" data-field="assigned_detail"></p>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-5">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-black">Diteruskan ke Mitra</h2>
                    </div>
                </div>
                <div id="routed-mitras" class="space-y-3"></div>

                <?php if((auth()->check() && auth()->id() === $report->user_id) || ($report->user_id === null && in_array($report->id, session('my_reports', [])))): ?>
                    <div id="re-alert-container" class="mt-4 hidden animate-pulse">
                        <button id="btn-re-alert" onclick="triggerReAlert()" class="w-full rounded-xl bg-red-600 hover:bg-red-700 text-white font-black py-3.5 px-4 text-sm flex items-center justify-center gap-2 transition duration-300 shadow-md transform active:scale-95">
                            <span>🚨 Kirim Ulang Alert ke Mitra</span>
                            <span id="re-alert-countdown" class="hidden font-mono bg-red-800 px-2 py-0.5 rounded text-xs"></span>
                        </button>
                    </div>
                <?php endif; ?>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-5">
                <h2 class="text-lg font-black">Update Terbaru</h2>
                <div id="timeline" class="mt-4 space-y-4"></div>
            </section>
        </div>

        <aside class="space-y-5">
            <section class="rounded-lg border border-red-200 bg-white p-5">
                <h2 class="text-lg font-black text-red-800">Nomor Darurat</h2>
                <p class="mt-1 text-sm text-gray-600">Jika kondisi memburuk, hubungi bantuan resmi sekarang. Safora berjalan paralel.</p>
                <div id="hotlines" class="mt-4 grid gap-2"></div>
            </section>
        </aside>
    </div>
</main>

<script>
    const initialPayload = <?php echo json_encode($livePayload, 15, 512) ?>;
    const reportId = <?php echo json_encode($report->id, 15, 512) ?>;
    const isCreator = <?php echo json_encode($isReporter, 15, 512) ?>;
    const hasDirectChatAccess = <?php echo json_encode($hasDirectChatAccess, 15, 512) ?>;
    const isLoggedIn = <?php echo json_encode(auth()->check(), 15, 512) ?>;
    const isMitra = <?php echo json_encode($isMitra, 15, 512) ?>;
    const isMitraHandling = <?php echo json_encode($isMitraHandling, 15, 512) ?>;
    let lastPayload = null;

    // Hubungi nomor darurat otomatis jika diarahkan dari proses pelaporan
    try {
        const urlParams = new URLSearchParams(window.location.search);
        const callPhone = urlParams.get('call');
        if (callPhone) {
            const hasTriggered = sessionStorage.getItem('safora_call_triggered_' + reportId);
            if (!hasTriggered) {
                window.location.href = 'tel:' + callPhone;
            } else {
                sessionStorage.removeItem('safora_call_triggered_' + reportId);
            }
            // Hapus parameter URL agar tidak memicu panggilan ulang saat halaman direfresh
            urlParams.delete('call');
            const searchString = urlParams.toString();
            const newUrl = window.location.pathname + (searchString ? '?' + searchString : '');
            window.history.replaceState({}, document.title, newUrl);
        }
    } catch (e) {
        console.error('Error handling dialer parameter:', e);
    }

    // Sync session my_reports dengan localStorage agar ketahanan 100% terjamin (misal habis login/logout)
    let storedReports = JSON.parse(localStorage.getItem('safora_guest_reports') || '[]');
    if (storedReports.includes(reportId) && !isMitra) {
        if (!isCreator && !sessionStorage.getItem('synced_' + reportId)) {
            sessionStorage.setItem('synced_' + reportId, 'true');
            // Pelapor terdeteksi di browser ini tapi session kosong (misal habis logout)
            fetch('/tracking/active-check?ids=' + encodeURIComponent(storedReports.join(',')), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(res => {
                if (res.ok) {
                    window.location.reload();
                } else {
                    sessionStorage.removeItem('synced_' + reportId);
                }
            })
            .catch(() => {
                sessionStorage.removeItem('synced_' + reportId);
            });
        }
    } else if (isCreator && !isLoggedIn && !isMitra) {
        // Tambahkan ke localStorage jika kita adalah pelapor guest/anonim
        storedReports.push(reportId);
        localStorage.setItem('safora_guest_reports', JSON.stringify(Array.from(new Set(storedReports))));
    }

    let map = window._trackingMap || null;
    let marker = window._trackingMarker || null;
    let mitraMarker = window._trackingMitraMarker || null;

    function updateMapMarkers(victimLat, victimLng, mitraLat, mitraLng) {
        if (!victimLat || !victimLng) return;
        
        const victimLatLng = [victimLat, victimLng];
        const container = document.getElementById('tracking-map');
        if (!container) return;

        // Jika kontainer berubah, hapus objek peta lama
        if (map && map.getContainer() !== container) {
            try {
                map.remove();
            } catch (e) {}
            map = null;
            marker = null;
            mitraMarker = null;
            window._trackingMap = null;
            window._trackingMarker = null;
            window._trackingMitraMarker = null;
        }
        
        if (!map) {
            if (container._leaflet_id) {
                container._leaflet_id = null;
                container.innerHTML = "";
            }

            map = L.map('tracking-map', { zoomControl: false }).setView(victimLatLng, 15);
            window._trackingMap = map;
            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);
            
            // Penanda lokasi korban
            marker = L.circleMarker(victimLatLng, {
                color: 'red',
                fillColor: '#f03',
                fillOpacity: 0.5,
                radius: 8
            }).addTo(map);
            window._trackingMarker = marker;
        } else {
            if (marker) marker.setLatLng(victimLatLng);
        }
        
        if (mitraLat && mitraLng) {
            const mitraLatLng = [mitraLat, mitraLng];
            if (!mitraMarker) {
                // Penanda lokasi mitra (ikon biru dengan efek denyut)
                mitraMarker = L.marker(mitraLatLng, {
                    icon: L.divIcon({
                        className: 'mitra-map-icon',
                        html: `
                            <div class="relative flex items-center justify-center">
                                <div class="absolute w-6 h-6 bg-blue-500 rounded-full animate-ping opacity-75"></div>
                                <div class="relative w-5 h-5 bg-blue-600 border-2 border-white rounded-full flex items-center justify-center shadow-lg">
                                    <span class="text-[10px] text-white font-black">M</span>
                                </div>
                            </div>
                        `,
                        iconSize: [24, 24],
                        iconAnchor: [12, 12]
                    })
                }).addTo(map);
                window._trackingMitraMarker = mitraMarker;
                mitraMarker.bindPopup("<b>Lokasi Mitra</b><br>Sedang menuju ke lokasi Anda.").openPopup();
            } else {
                mitraMarker.setLatLng(mitraLatLng);
            }
            
            // Atur batas peta untuk menampilkan korban dan mitra sekaligus
            const bounds = L.latLngBounds([victimLatLng, mitraLatLng]);
            map.fitBounds(bounds, { padding: [30, 30] });
        } else {
            if (mitraMarker) {
                map.removeLayer(mitraMarker);
                mitraMarker = null;
                window._trackingMitraMarker = null;
            }
            map.setView(victimLatLng, 15);
        }
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function viewEvidence(url, fileType) {
        if (!url || url === '#') return;
        
        fileType = fileType || '';
        
        if (url.startsWith('data:')) {
            const newWindow = window.open();
            if (!newWindow) {
                alert('Pop-up diblokir oleh browser. Silakan aktifkan pop-up untuk melihat bukti.');
                return;
            }
            newWindow.document.write(`
                <!DOCTYPE html>
                <html lang="id">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Safora - Lihat Bukti</title>
                    <style>
                        body {
                            margin: 0;
                            background-color: #0b0f19;
                            color: #f3f4f6;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            min-height: 100vh;
                            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                        }
                        .container {
                            text-align: center;
                            padding: 24px;
                            max-width: 90%;
                            width: 100%;
                            display: flex;
                            flex-direction: column;
                            align-items: center;
                            justify-content: center;
                            gap: 16px;
                        }
                        img, video {
                            max-width: 100%;
                            max-height: 80vh;
                            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 10px 10px -5px rgba(0, 0, 0, 0.4);
                            border-radius: 16px;
                            border: 1px solid rgba(255, 255, 255, 0.1);
                        }
                        audio {
                            width: 100%;
                            max-width: 400px;
                        }
                        iframe {
                            width: 100%;
                            height: 80vh;
                            border: none;
                            border-radius: 16px;
                            background: white;
                            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
                        }
                        .meta {
                            font-size: 13px;
                            color: #9ca3af;
                            font-weight: 500;
                        }
                        .btn-download {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            background-color: #dc2626;
                            color: white;
                            padding: 10px 20px;
                            text-decoration: none;
                            border-radius: 12px;
                            font-weight: 700;
                            font-size: 14px;
                            transition: background-color 0.2s, transform 0.1s;
                            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                        }
                        .btn-download:hover {
                            background-color: #b91c1c;
                            transform: scale(1.02);
                        }
                        .btn-download:active {
                            transform: scale(0.98);
                        }
                    </style>
                </head>
                <body>
                    <div class="container">
            `);

            if (fileType.startsWith('image/')) {
                newWindow.document.write(`<img src="${url}" alt="Bukti Foto">`);
            } else if (fileType.startsWith('video/')) {
                newWindow.document.write(`<video src="${url}" controls autoplay></video>`);
            } else if (fileType.startsWith('audio/')) {
                newWindow.document.write(`<audio src="${url}" controls autoplay></audio>`);
            } else {
                newWindow.document.write(`<iframe src="${url}"></iframe>`);
            }

            newWindow.document.write(`
                        <div class="meta">Format: ${fileType}</div>
                        <a href="${url}" download="bukti-${Date.now()}" class="btn-download">Unduh Berkas</a>
                    </div>
                </body>
                </html>
            `);
            newWindow.document.close();
        } else {
            window.open(url, '_blank');
        }
    }

    function setText(selector, value) {
        const node = document.querySelector(selector);
        if (node) node.textContent = value || '-';
    }

    function statusLabel(status) {
        return {
            waiting: ['Menunggu', 'bg-gray-100 text-gray-700'],
            reviewing: ['Meninjau', 'bg-yellow-100 text-yellow-900'],
            unavailable: ['Tidak menerima', 'bg-orange-100 text-orange-800'],
            accepted: ['Menerima', 'bg-green-100 text-green-800'],
        }[status] || ['Menunggu', 'bg-gray-100 text-gray-700'];
    }

    function render(payload) {
        lastPayload = payload;
        setText('[data-field="current_status"]', payload.current_status);
        setText('[data-field="human_message"]', payload.human_message);
        setText('[data-field="eta"]', payload.eta);
        setText('[data-field="next_instruction"]', payload.next_instruction);
        setText('[data-field="escalation_message"]', payload.escalation_message);
        setText('[data-field="urgency"]', payload.report.urgency_level);
        setText('[data-field="category"]', payload.report.category);
        
        const incidentDateContainer = document.getElementById('incident-date-container');
        if (incidentDateContainer) {
            if (payload.report.incident_date) {
                incidentDateContainer.classList.remove('hidden');
                setText('[data-field="incident_date"]', payload.report.incident_date);
            } else {
                incidentDateContainer.classList.add('hidden');
            }
        }

        const maps = document.querySelector('[data-field="maps_url"]');
        if (maps && payload.report.location.maps_url) {
            maps.href = payload.report.location.maps_url;
            maps.textContent = payload.report.location.verified ? 'Titik Lokasi ' : 'Buka peta';
        }

        if (payload.report.location.latitude && payload.report.location.longitude) {
            const victimLat = parseFloat(payload.report.location.latitude);
            const victimLng = parseFloat(payload.report.location.longitude);
            let mitraLat = null;
            let mitraLng = null;
            
            if (payload.assigned_mitra && payload.assigned_mitra.latitude && payload.assigned_mitra.longitude) {
                mitraLat = parseFloat(payload.assigned_mitra.latitude);
                mitraLng = parseFloat(payload.assigned_mitra.longitude);
            }
            
            updateMapMarkers(victimLat, victimLng, mitraLat, mitraLng);
        }

        const assigned = document.getElementById('assigned-card');
        if (assigned) {
            if (payload.assigned_mitra) {
                assigned.classList.remove('hidden');
                setText('[data-field="assigned_name"]', payload.assigned_mitra.name);
                setText('[data-field="assigned_detail"]', payload.assigned_mitra.specialization);
                
                const chatLink = document.getElementById('chat-link');
                if (chatLink) {
                    // Atur URL chat dengan lokasi pengguna saat ini
                    const qs = (window._userLat && window._userLng) ? `?lat=${window._userLat}&lng=${window._userLng}` : '';
                    chatLink.href = `/chat/report/${reportId}${qs}`;
                    // Tampilkan/sembunyikan berdasarkan hasil pemeriksaan jarak
                    if (window._chatAllowed) {
                        chatLink.classList.remove('hidden');
                        chatLink.classList.add('inline-flex');
                    } else {
                        chatLink.classList.add('hidden');
                        chatLink.classList.remove('inline-flex');
                    }
                }
            } else {
                assigned.classList.add('hidden');
            }
        }

        const routedMitrasEl = document.getElementById('routed-mitras');
        if (routedMitrasEl) {
            routedMitrasEl.innerHTML = (payload.routed_mitras || []).map((mitra) => {
                const [label, klass] = statusLabel(mitra.status);
                return `
                    <div class="rounded-lg border border-gray-200 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-black">${escapeHtml(mitra.name)}</p>
                                <p class="mt-1 text-sm text-gray-500">${escapeHtml(mitra.specialization)}${mitra.city ? ' - ' + escapeHtml(mitra.city) : ''}</p>
                                <p class="mt-1 text-xs text-gray-500">${escapeHtml(mitra.distance || 'Jarak belum tersedia')}</p>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <span class="rounded-full px-3 py-1 text-xs font-bold ${klass}">${label}</span>
                                ${mitra.status === 'accepted' ? `<span id="chat-access-badge" class="text-xs text-green-700 font-semibold hidden">✓ Chat tersedia</span>` : ''}
                            </div>
                        </div>
                    </div>
                `;
            }).join('') || '<p class="rounded-lg bg-gray-50 p-4 text-sm text-gray-500">Kami sedang mencari mitra yang relevan.</p>';
        }

        const timelineEl = document.getElementById('timeline');
        if (timelineEl) {
            timelineEl.innerHTML = (payload.timeline || []).map((event) => `
                <div class="flex gap-3">
                    <div class="mt-1 h-2.5 w-2.5 rounded-full bg-red-700"></div>
                    <div>
                        <p class="text-sm font-bold text-gray-900">${escapeHtml(event.message)}</p>
                        <p class="mt-1 text-xs text-gray-400">${escapeHtml(event.time)}</p>
                    </div>
                </div>
            `).join('');
        }

        const hotlinesEl = document.getElementById('hotlines');
        if (hotlinesEl) {
            hotlinesEl.innerHTML = (payload.hotlines || []).map((hotline) => `
                <a href="tel:${escapeHtml(hotline.phone)}" class="flex items-center justify-between rounded-lg border border-red-100 bg-red-50 px-4 py-3">
                    <span class="text-sm font-bold text-red-950">${escapeHtml(hotline.label)}</span>
                    <span class="text-lg font-black text-red-800">${escapeHtml(hotline.phone)}</span>
                </a>
            `).join('');
        }

        const latestMsgsEl = document.getElementById('latest-messages');
        if (latestMsgsEl) {
            latestMsgsEl.innerHTML = (payload.latest_messages || []).map((message) => `
                <div class="rounded-lg bg-gray-50 p-3">
                    <p class="text-sm text-gray-800">${escapeHtml(message.message)}</p>
                    <p class="mt-1 text-xs text-gray-400">${escapeHtml(message.time)}</p>
                </div>
            `).join('') || '<p class="rounded-lg bg-gray-50 p-4 text-sm text-gray-500">Belum ada pesan mitra. Tetap pantau halaman ini.</p>';
        }

        // Update Re-Alert Button UI secara dinamis
        const reAlertContainer = document.getElementById('re-alert-container');
        if (reAlertContainer && isCreator) {
            if (payload.retry_count >= 3 && payload.report.status !== 'Resolved' && !payload.assigned_mitra) {
                reAlertContainer.classList.remove('hidden');
                
                const btnReAlert = document.getElementById('btn-re-alert');
                const btnCountdown = document.getElementById('re-alert-countdown');
                
                if (payload.cooldown_seconds > 0) {
                    btnReAlert.disabled = true;
                    btnReAlert.classList.remove('bg-red-600', 'hover:bg-red-700');
                    btnReAlert.classList.add('bg-gray-400', 'cursor-not-allowed');
                    btnCountdown.classList.remove('hidden');
                    
                    startCountdownTimer(payload.cooldown_seconds);
                } else {
                    // Check jika timer client-side tidak aktif baru aktifkan
                    if (!window._countdownActive) {
                        btnReAlert.disabled = false;
                        btnReAlert.classList.add('bg-red-600', 'hover:bg-red-700');
                        btnReAlert.classList.remove('bg-gray-400', 'cursor-not-allowed');
                    }
                }
            } else {
                reAlertContainer.classList.add('hidden');
            }
        }
        // Update Chronology List secara dinamis & real-time
        const chronologyList = document.getElementById('chronology-list');
        if (chronologyList && payload.chronologies) {
            if (payload.chronologies.length > 0) {
                chronologyList.innerHTML = payload.chronologies.map(chrono => {
                    let roleLabel = `${chrono.role} (${chrono.writer_name})`;
                    if (chrono.role === 'Korban') {
                        roleLabel = `Korban (${chrono.writer_name})`;
                    } else if (chrono.role === 'Saksi') {
                        roleLabel = `Saksi (${chrono.writer_name})`;
                    }
                    return `
                        <div class="p-4 bg-slate-50 border border-slate-100 rounded-xl">
                            <div class="flex justify-between items-start gap-3 flex-wrap">
                                <span class="text-xs font-bold text-slate-800 bg-slate-200 px-2 py-0.5 rounded">
                                    ${escapeHtml(roleLabel)}
                                </span>
                                <span class="text-[10px] text-gray-400 font-medium">
                                    ${escapeHtml(chrono.created_at)}
                                </span>
                            </div>
                            <p class="text-sm text-gray-700 mt-2 leading-relaxed whitespace-pre-line">${escapeHtml(chrono.description)}</p>
                        </div>
                    `;
                }).join('');
            } else {
                chronologyList.innerHTML = `
                    <div class="text-center py-6 text-gray-400 text-sm italic" id="chronology-empty-state">
                        Belum ada kronologi tambahan.
                    </div>
                `;
            }
        }

        // Update Evidence List secara dinamis & real-time
        const evidenceListContainer = document.getElementById('evidence-list-container');
        if (evidenceListContainer && payload.evidences) {
            if (payload.evidences.length > 0) {
                const roles = ['Korban', 'Saksi', 'Mitra'];
                const grouped = { Korban: [], Saksi: [], Mitra: [] };
                payload.evidences.forEach(ev => {
                    const role = ev.uploader_role || 'Saksi';
                    if (!grouped[role]) grouped[role] = [];
                    grouped[role].push(ev);
                });

                let html = '';
                roles.forEach(role => {
                    const list = grouped[role];
                    if (list && list.length > 0) {
                        html += `
                            <div class="bg-slate-50/50 border border-slate-100 rounded-xl p-4 mb-3" id="evidence-group-${role}">
                                <h4 class="text-xs font-bold text-slate-800 bg-slate-200 px-2 py-1 rounded inline-block mb-3">
                                    ${role}
                                </h4>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3" id="evidence-grid-${role}">
                        `;

                        list.forEach(ev => {
                            const isImage = (ev.file_type || '').startsWith('image/');
                            const isVideo = (ev.file_type || '').startsWith('video/');
                            const isAudio = (ev.file_type || '').startsWith('audio/');
                            const canView = payload.can_view_evidence;
                            const evidenceUrl = ev.file_url;

                            let cardBg = 'bg-blue-50/50 border-blue-150';
                            let badgeBg = 'bg-blue-100 text-blue-800';
                            let badgeLabel = 'BERKAS';
                            let svgIcon = '';

                            if (isImage) {
                                cardBg = 'bg-rose-50/50 border-rose-150';
                                badgeBg = 'bg-rose-100 text-rose-800';
                                badgeLabel = 'FOTO';
                                svgIcon = `<svg class="w-10 h-10 text-rose-500 group-hover:scale-110 transition-transform duration-300 ${!canView ? 'blur-[3px]' : ''}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>`;
                            } else if (isVideo) {
                                cardBg = 'bg-amber-50/50 border-amber-150';
                                badgeBg = 'bg-amber-100 text-amber-800';
                                badgeLabel = 'VIDEO';
                                svgIcon = `<svg class="w-10 h-10 text-amber-500 group-hover:scale-110 transition-transform duration-300 ${!canView ? 'blur-[3px]' : ''}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>`;
                            } else if (isAudio) {
                                cardBg = 'bg-emerald-50/50 border-emerald-150';
                                badgeBg = 'bg-emerald-100 text-emerald-800';
                                badgeLabel = 'REKAMAN';
                                svgIcon = `<svg class="w-10 h-10 text-emerald-500 group-hover:scale-110 transition-transform duration-300 ${!canView ? 'blur-[3px]' : ''}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>`;
                            } else {
                                svgIcon = `<svg class="w-10 h-10 text-blue-500 group-hover:scale-110 transition-transform duration-300 ${!canView ? 'blur-[3px]' : ''}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>`;
                            }

                            html += `
                                <a href="javascript:void(0)" 
                                   data-url="${canView ? evidenceUrl : '#'}" 
                                   data-type="${ev.file_type || ''}" 
                                   onclick="${canView ? 'viewEvidence(this.dataset.url, this.dataset.type)' : "alert('Bukti hanya bisa dibuka oleh korban / mitra')"}" 
                                   class="aspect-square rounded-2xl border ${cardBg} p-4 relative group flex flex-col items-center justify-center transition-all duration-300 hover:scale-[1.03] hover:shadow-md">
                                    ${svgIcon}
                                    <span class="mt-3 text-[10px] font-bold tracking-widest uppercase ${badgeBg} px-2 py-0.5 rounded-full">${badgeLabel}</span>
                                    ${!canView ? `
                                        <div class="absolute inset-0 bg-white/60 backdrop-blur-[4px] rounded-2xl flex items-center justify-center">
                                            <div class="bg-white/80 p-2 rounded-full shadow-sm border border-gray-100 flex items-center justify-center">
                                                <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                </svg>
                                            </div>
                                        </div>
                                    ` : ''}
                                </a>
                            `;
                        });

                        html += `
                                </div>
                            </div>
                        `;
                    }
                });

                evidenceListContainer.innerHTML = html;
            } else {
                evidenceListContainer.innerHTML = `
                    <div class="text-center py-6 text-gray-400 text-sm italic" id="evidence-empty-state">
                        Belum ada berkas bukti diunggah.
                    </div>
                `;
            }
        }
    }

    let countdownInterval = null;
    function startCountdownTimer(seconds) {
        if (window._countdownActive) return;
        window._countdownActive = true;
        
        if (countdownInterval) clearInterval(countdownInterval);
        
        let remaining = seconds;
        const countdownSpan = document.getElementById('re-alert-countdown');
        const btnReAlert = document.getElementById('btn-re-alert');
        
        if (!countdownSpan) {
            window._countdownActive = false;
            return;
        }
        
        countdownSpan.classList.remove('hidden');
        if (btnReAlert) {
            btnReAlert.disabled = true;
            btnReAlert.classList.remove('bg-red-600', 'hover:bg-red-700');
            btnReAlert.classList.add('bg-gray-400', 'cursor-not-allowed');
        }
        
        function updateDisplay() {
            const minutes = Math.floor(remaining / 60);
            const secs = remaining % 60;
            countdownSpan.textContent = `${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
        }
        
        updateDisplay();
        
        countdownInterval = setInterval(() => {
            remaining--;
            if (remaining <= 0) {
                clearInterval(countdownInterval);
                window._countdownActive = false;
                countdownSpan.classList.add('hidden');
                if (btnReAlert) {
                    btnReAlert.disabled = false;
                    btnReAlert.classList.add('bg-red-600', 'hover:bg-red-700');
                    btnReAlert.classList.remove('bg-gray-400', 'cursor-not-allowed');
                }
            } else {
                updateDisplay();
            }
        }, 1000);
    }

    async function triggerReAlert() {
        const btnReAlert = document.getElementById('btn-re-alert');
        if (!btnReAlert || btnReAlert.disabled) return;
        
        if (!await window.showConfirm('Apakah Anda yakin ingin mengirim ulang alert WhatsApp ke mitra terdekat?')) return;
        
        btnReAlert.disabled = true;
        const originalContent = btnReAlert.innerHTML;
        btnReAlert.innerHTML = `
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Mengirimkan Alert...</span>
        `;
        
        try {
            const response = await fetch(`/tracking/${reportId}/re-alert`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            if (response.ok && data.ok) {
                alert('Alert WhatsApp berhasil dikirim ulang ke mitra!');
                startCountdownTimer(600);
                pollLive();
            } else {
                alert(data.error || 'Gagal mengirim ulang alert.');
                btnReAlert.innerHTML = originalContent;
                btnReAlert.disabled = false;
            }
        } catch (error) {
            alert('Terjadi kesalahan sistem saat menghubungi mitra.');
            btnReAlert.innerHTML = originalContent;
            btnReAlert.disabled = false;
        }
    }

    async function pollLive() {
        try {
            const response = await fetch(`/tracking/${reportId}/live`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (response.ok) {
                render(await response.json());
                const liveDot = document.getElementById('live-dot');
                if (liveDot) liveDot.textContent = 'Live';
            }
        } catch (error) {
            const liveDot = document.getElementById('live-dot');
            if (liveDot) liveDot.textContent = 'Mencoba ulang';
        }
    }

    let watchId = window._locationWatchId || null;

    function pushLocation() {
        if (!navigator.geolocation) return;

        const updateCoords = async (pos) => {
            try {
                await fetch(`/tracking/${reportId}/location`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        latitude: pos.coords.latitude,
                        longitude: pos.coords.longitude
                    })
                });
            } catch (e) {}
        };

        // Dapatkan lokasi awal dengan cepat
        navigator.geolocation.getCurrentPosition(updateCoords, () => {}, {
            enableHighAccuracy: true,
            timeout: 5000
        });

        if (window._locationWatchId) {
            navigator.geolocation.clearWatch(window._locationWatchId);
        }

        // Pantau perubahan lokasi secara real-time
        watchId = navigator.geolocation.watchPosition(updateCoords, (err) => {}, {
            enableHighAccuracy: true,
            maximumAge: 0,
            timeout: 10000
        });
        window._locationWatchId = watchId;
    }

    render(initialPayload);

    if (window._pollLiveInterval) {
        clearInterval(window._pollLiveInterval);
    }
    window._pollLiveInterval = setInterval(pollLive, 4000);

    const reportLat = initialPayload.report.location.latitude;
    const reportLng = initialPayload.report.location.longitude;

    function haversineKm(lat1, lon1, lat2, lon2) {
        const R = 6371;
        const dLat = (lat2 - lon1) * Math.PI / 180; // Tunggu, sebenarnya:
        // const dLat = (lat2 - lat1) * Math.PI / 180;
        // mari kita tulis secara standar:
        const dLatRad = (lat2 - lat1) * Math.PI / 180;
        const dLonRad = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLatRad/2)**2 + Math.cos(lat1*Math.PI/180) * Math.cos(lat2*Math.PI/180) * Math.sin(dLonRad/2)**2;
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    }

    function runGeolocationCheck() {
        const checking = document.getElementById('chat-checking');
        const noGps    = document.getElementById('chat-no-gps');
        const chatLink  = document.getElementById('chat-link');

        if (!navigator.geolocation) {
            if (checking) checking.classList.add('hidden');
            if (noGps) {
                const p = noGps.querySelector('p');
                if (p) p.textContent = '📍 Browser Anda tidak mendukung GPS atau koneksi tidak aman (HTTPS).';
                noGps.classList.remove('hidden');
            }
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;

                // Simpan di window agar fungsi render dapat menggunakannya
                window._userLat = lat;
                window._userLng = lng;

                const tooFar    = document.getElementById('chat-too-far');
                const distNote  = document.getElementById('chat-distance-note');

                if (checking) checking.classList.add('hidden');
                if (noGps) noGps.classList.add('hidden');

                // Set URL dengan koordinat agar server bisa verifikasi
                if (chatLink) chatLink.href = `/chat/report/${reportId}?lat=${lat}&lng=${lng}`;

                if (reportLat && reportLng) {
                    const dist = haversineKm(lat, lng, parseFloat(reportLat), parseFloat(reportLng));
                    if (dist <= 5.0) {
                        window._chatAllowed = true;
                        if (chatLink) {
                            chatLink.classList.remove('hidden');
                            chatLink.classList.add('inline-flex');
                        }
                        if (distNote) distNote.classList.remove('hidden');
                        
                        // Tampilkan tombol Tambah Kronologi jika saksi di bawah 5 km
                        const chronoBtn = document.getElementById('btn-add-chronology');
                        if (chronoBtn) chronoBtn.classList.remove('hidden');
                    } else {
                        window._chatAllowed = false;
                        // Terlalu jauh — tampilkan pesan, sembunyikan tombol
                        if (tooFar) tooFar.classList.remove('hidden');
                        if (noGps) {
                            const p = noGps.querySelector('p');
                            if (p) p.textContent = `📍 Lokasi Anda terlalu jauh (${dist.toFixed(1)} km). Chat & tambah kronologi saksi hanya tersedia dalam radius 5 km.`;
                            noGps.classList.remove('hidden');
                            // Sembunyikan tombol coba lagi jika lokasinya memang di luar radius
                            const retryBtn = document.getElementById('btn-retry-gps');
                            if (retryBtn) retryBtn.classList.add('hidden');
                        }
                    }
                } else {
                    // Laporan tidak punya GPS — tidak bisa verifikasi jarak
                    window._chatAllowed = true;
                    if (chatLink) {
                        chatLink.classList.remove('hidden');
                        chatLink.classList.add('inline-flex');
                    }
                    if (distNote) {
                        distNote.textContent = '📍 Lokasi laporan tidak tersedia — tombol chat tetap bisa dicoba.';
                        distNote.classList.remove('hidden');
                    }
                }
            },
            (err) => {
                // Gagal ambil lokasi browser
                if (checking) checking.classList.add('hidden');
                if (noGps) {
                    const p = noGps.querySelector('p');
                    let errMsg = 'Gagal memverifikasi lokasi.';
                    if (err.code === err.PERMISSION_DENIED) {
                        errMsg = '📍 Akses lokasi ditolak. Silakan izinkan akses GPS pada browser Anda.';
                    } else if (err.code === err.POSITION_UNAVAILABLE) {
                        errMsg = '📍 Informasi lokasi tidak tersedia. Pastikan GPS aktif.';
                    } else if (err.code === err.TIMEOUT) {
                        errMsg = '📍 Waktu verifikasi lokasi habis (timeout). Coba lagi di area terbuka.';
                    }
                    if (p) p.textContent = errMsg;
                    // Pastikan tombol coba lagi terlihat
                    const retryBtn = document.getElementById('btn-retry-gps');
                    if (retryBtn) retryBtn.classList.remove('hidden');
                    noGps.classList.remove('hidden');
                }
            },
            { enableHighAccuracy: true, timeout: 8000, maximumAge: 60000 }
        );
    }

    function retryGeolocation() {
        const checking = document.getElementById('chat-checking');
        const noGps    = document.getElementById('chat-no-gps');
        if (checking) checking.classList.remove('hidden');
        if (noGps)    noGps.classList.add('hidden');
        
        runGeolocationCheck();
    }

    // Panggil jika bukan pelapor langsung
    if (!hasDirectChatAccess) {
        runGeolocationCheck();
    }


    // Pelapor: push GPS location secara real-time untuk tracking
    if (isCreator) {
        pushLocation();
    }

    const activeUploads = {};
    let uploadedEvidenceIdsInSession = [];
    let uploadedEvidencesInSession = [];
    const canViewEvidence = <?php echo json_encode($report->show_evidence || (auth()->check() && (auth()->id() === $report->user_id || auth()->user()->role === 'mitra')) || ($report->user_id === null && in_array($report->id, session('my_reports', [])))) ?>;

    let uploadQueue = [];
    let isUploading = false;

    function compressImageOnClient(file, maxWidth = 1920, maxHeight = 1920, quality = 0.75) {
        return new Promise((resolve) => {
            if (!file.type.startsWith('image/') || file.type === 'image/gif') {
                resolve(file);
                return;
            }

            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = function(event) {
                const img = new Image();
                img.src = event.target.result;
                img.onload = function() {
                    let width = img.width;
                    let height = img.height;
                    
                    if (width > maxWidth || height > maxHeight) {
                        if (width > height) {
                            height = Math.round((height * maxWidth) / width);
                            width = maxWidth;
                        } else {
                            width = Math.round((width * maxHeight) / height);
                            height = maxHeight;
                        }
                    }
                    
                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);
                    
                    const outputType = 'image/jpeg';
                    canvas.toBlob(function(blob) {
                        if (!blob) {
                            resolve(file);
                            return;
                        }
                        let newName = file.name;
                        if (!newName.endsWith('.jpg') && !newName.endsWith('.jpeg')) {
                            newName = newName.replace(/\.[a-zA-Z0-9]+$/, '.jpg');
                        }
                        const compressedFile = new File([blob], newName, {
                            type: outputType,
                            lastModified: Date.now()
                        });
                        
                        if (compressedFile.size >= file.size) {
                            resolve(file);
                        } else {
                            resolve(compressedFile);
                        }
                    }, outputType, quality);
                };
                img.onerror = function() {
                    resolve(file);
                };
            };
            reader.onerror = function() {
                resolve(file);
            };
        });
    }

    function createUploadPlaceholderDOM(fileName, uniqueId) {
        const uploadList = document.getElementById('upload-list');
        if (!uploadList) return;
        
        const item = document.createElement('div');
        item.id = `upload-${uniqueId}`;
        item.className = "flex items-center justify-between bg-white border border-gray-100 p-3 rounded-xl shadow-sm mb-2";
        item.innerHTML = `
            <div class="flex-1 min-w-0 mr-3">
                <p class="text-sm font-semibold text-gray-800 truncate">${fileName}</p>
                <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1.5 overflow-hidden" id="progress-container-${uniqueId}">
                    <div class="bg-green-500 h-1.5 rounded-full transition-all duration-300" style="width: 0%" id="progress-${uniqueId}"></div>
                </div>
                <div class="flex items-center gap-1 mt-1">
                    <svg class="w-3 h-3 text-green-500 hidden" id="check-${uniqueId}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    <p class="text-[10px] text-gray-500" id="text-${uniqueId}">Dalam antrean...</p>
                </div>
            </div>
            <div class="flex items-center gap-2" id="action-${uniqueId}">
                <!-- Cancel button during upload -->
                <button type="button" onclick="cancelUpload('${uniqueId}')" class="text-red-500 hover:text-red-700 transition p-1" title="Batalkan Upload">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        `;
        uploadList.appendChild(item);
    }

    function processUploadQueue() {
        if (isUploading || uploadQueue.length === 0) return;
        isUploading = true;
        const queueItem = uploadQueue.shift();
        const file = queueItem.file;
        const uniqueId = queueItem.uniqueId;

        const txt = document.getElementById(`text-${uniqueId}`);
        if (txt) txt.innerText = 'Menyiapkan berkas...';

        compressImageOnClient(file).then(processedFile => {
            const item = document.getElementById(`upload-${uniqueId}`);
            if (!item || (txt && txt.innerText === 'Dibatalkan')) {
                isUploading = false;
                setTimeout(processUploadQueue, 300);
                return;
            }

            uploadFile(processedFile, uniqueId, function() {
                isUploading = false;
                setTimeout(processUploadQueue, 300);
            });
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const fileInput = document.getElementById('evf');
        if (fileInput) {
            fileInput.addEventListener('change', function() {
                Array.from(this.files).forEach(file => {
                    const uniqueId = Math.random().toString(36).substring(2, 15);
                    createUploadPlaceholderDOM(file.name, uniqueId);
                    uploadQueue.push({ file: file, uniqueId: uniqueId });
                });
                this.value = '';
                processUploadQueue();
            });
        }
    });

    function uploadFile(file, uniqueId, onComplete) {
        const txt = document.getElementById(`text-${uniqueId}`);
        if (txt) txt.innerText = 'Mengunggah...';

        const formData = new FormData();
        formData.append('evidence[]', file);
        formData.append('_token', '<?php echo e(csrf_token()); ?>');

        const xhr = new XMLHttpRequest();
        activeUploads[uniqueId] = xhr;

        xhr.open('POST', '/tracking/<?php echo e($report->id); ?>/evidence', true);
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.upload.onprogress = function(e) {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 100);
                const bar = document.getElementById(`progress-${uniqueId}`);
                if (bar) bar.style.width = percent + '%';
                const txt = document.getElementById(`text-${uniqueId}`);
                if (txt) txt.innerText = `Mengunggah ${percent}%`;
            }
        };

        xhr.onload = function() {
            delete activeUploads[uniqueId];
            if (xhr.status === 200) {
                let res = {};
                try {
                    res = JSON.parse(xhr.responseText);
                } catch(e) {}

                const txt = document.getElementById(`text-${uniqueId}`);
                if (txt) {
                    txt.innerText = 'Selesai';
                    txt.classList.replace('text-gray-500', 'text-green-600');
                }
                const pContainer = document.getElementById(`progress-container-${uniqueId}`);
                if (pContainer) pContainer.classList.add('hidden');
                const chk = document.getElementById(`check-${uniqueId}`);
                if (chk) chk.classList.remove('hidden');

                // Show delete button
                if (res.evidences && res.evidences.length > 0) {
                    const evidenceObj = res.evidences[0];
                    const evidenceId = evidenceObj.id;
                    uploadedEvidenceIdsInSession.push(evidenceId);
                    uploadedEvidencesInSession.push(evidenceObj);
                    
                    const actionContainer = document.getElementById(`action-${uniqueId}`);
                    if (actionContainer) {
                        actionContainer.innerHTML = `
                            <button type="button" onclick="deleteUploadedEvidence('${uniqueId}', '${evidenceId}')" class="text-red-600 hover:text-red-800 transition p-1" title="Hapus Bukti">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        `;
                    }
                } else {
                    const actionContainer = document.getElementById(`action-${uniqueId}`);
                    if (actionContainer) actionContainer.innerHTML = '';
                }
                if (onComplete) onComplete();
            } else {
                const txt = document.getElementById(`text-${uniqueId}`);
                let errMsg = 'Gagal upload';
                try {
                    const errData = JSON.parse(xhr.responseText);
                    if (errData) {
                        if (typeof errData.error === 'string') {
                            errMsg = errData.error;
                        } else if (errData.error && typeof errData.error === 'object' && errData.error.message) {
                            errMsg = errData.error.message;
                        } else if (typeof errData.message === 'string') {
                            errMsg = errData.message;
                        } else if (errData.errors) {
                            const errorsList = [];
                            for (const key in errData.errors) {
                                if (Array.isArray(errData.errors[key])) {
                                    errorsList.push(...errData.errors[key]);
                                } else {
                                    errorsList.push(errData.errors[key]);
                                }
                            }
                            if (errorsList.length > 0) {
                                errMsg = errorsList.join(' ');
                            }
                        }
                    }
                } catch(e) {}
                if (txt) {
                    txt.innerText = errMsg;
                    txt.classList.add('text-red-500');
                }
                const bar = document.getElementById(`progress-${uniqueId}`);
                if (bar) bar.classList.replace('bg-green-500', 'bg-red-500');
                
                // Tampilkan tombol hapus untuk item yang gagal
                const actionContainer = document.getElementById(`action-${uniqueId}`);
                if (actionContainer) {
                    actionContainer.innerHTML = `
                        <button type="button" onclick="removeFailedItem('${uniqueId}')" class="text-red-500 hover:text-red-700 transition p-1" title="Hapus dari daftar">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    `;
                }
                if (onComplete) onComplete();
            }
        };

        xhr.onerror = function() {
            delete activeUploads[uniqueId];
            const txt = document.getElementById(`text-${uniqueId}`);
            if (txt) {
                txt.innerText = 'Gagal koneksi';
                txt.classList.add('text-red-500');
            }
            const bar = document.getElementById(`progress-${uniqueId}`);
            if (bar) bar.classList.replace('bg-green-500', 'bg-red-500');
            
            // Tampilkan tombol hapus untuk koneksi yang gagal
            const actionContainer = document.getElementById(`action-${uniqueId}`);
            if (actionContainer) {
                actionContainer.innerHTML = `
                    <button type="button" onclick="removeFailedItem('${uniqueId}')" class="text-red-500 hover:text-red-700 transition p-1" title="Hapus dari daftar">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                `;
            }
            if (onComplete) onComplete();
        };

        xhr.onabort = function() {
            delete activeUploads[uniqueId];
            if (onComplete) onComplete();
        };

        xhr.send(formData);
    }

    function cancelUpload(uniqueId) {
        if (activeUploads[uniqueId]) {
            activeUploads[uniqueId].abort();
            delete activeUploads[uniqueId];
        }
        uploadQueue = uploadQueue.filter(item => item.uniqueId !== uniqueId);
        const item = document.getElementById(`upload-${uniqueId}`);
        if (item) {
            item.classList.add('opacity-50');
            item.querySelector(`#text-${uniqueId}`).innerText = 'Dibatalkan';
            item.querySelector(`#text-${uniqueId}`).classList.add('text-red-500');
            const pBar = item.querySelector(`#progress-${uniqueId}`);
            if (pBar) pBar.classList.replace('bg-green-500', 'bg-red-500');
            const action = item.querySelector(`#action-${uniqueId}`);
            if (action) action.innerHTML = '';
            setTimeout(() => item.remove(), 1500);
        }
    }

    function removeFailedItem(uniqueId) {
        const item = document.getElementById(`upload-${uniqueId}`);
        if (item) item.remove();
    }

    async function deleteUploadedEvidence(uniqueId, evidenceId) {
        const item = document.getElementById(`upload-${uniqueId}`);
        if (item) {
            item.classList.add('opacity-50');
            item.querySelector(`#text-${uniqueId}`).innerText = 'Menghapus...';
            item.querySelector(`#text-${uniqueId}`).classList.replace('text-green-600', 'text-gray-500');
        }

        try {
            const res = await fetch(`/evidence/${evidenceId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                    'Accept': 'application/json'
                }
            });
            if (res.ok) {
                uploadedEvidenceIdsInSession = uploadedEvidenceIdsInSession.filter(id => id !== evidenceId && id != evidenceId);
                uploadedEvidencesInSession = uploadedEvidencesInSession.filter(ev => ev.id !== evidenceId && ev.id != evidenceId);
                if (item) {
                    item.querySelector(`#text-${uniqueId}`).innerText = 'Terhapus';
                    item.querySelector(`#text-${uniqueId}`).classList.add('text-red-500');
                    setTimeout(() => item.remove(), 1000);
                }
            } else {
                alert('Gagal menghapus file.');
                if (item) {
                    item.classList.remove('opacity-50');
                    item.querySelector(`#text-${uniqueId}`).innerText = 'Selesai';
                    item.querySelector(`#text-${uniqueId}`).classList.replace('text-gray-500', 'text-green-600');
                }
            }
        } catch(e) {
            alert('Kesalahan koneksi saat menghapus.');
            if (item) {
                item.classList.remove('opacity-50');
                item.querySelector(`#text-${uniqueId}`).innerText = 'Selesai';
            }
        }
    }

    function getOrCreateEvidenceGroup(role) {
        let group = document.getElementById(`evidence-group-${role}`);
        if (group) return group;

        group = document.createElement('div');
        group.id = `evidence-group-${role}`;
        group.className = "bg-slate-50/50 border border-slate-100 rounded-xl p-4 fade-in";
        group.innerHTML = `
            <h4 class="text-xs font-bold text-slate-800 bg-slate-200 px-2 py-1 rounded inline-block mb-3">
                ${role}
            </h4>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3" id="evidence-grid-${role}"></div>
        `;

        const container = document.getElementById('evidence-list-container');
        const emptyState = document.getElementById('evidence-empty-state');
        if (emptyState) emptyState.remove();

        if (role === 'Korban') {
            container.insertBefore(group, container.firstChild);
        } else if (role === 'Saksi') {
            const korbanGroup = document.getElementById('evidence-group-Korban');
            if (korbanGroup) {
                korbanGroup.parentNode.insertBefore(group, korbanGroup.nextSibling);
            } else {
                container.insertBefore(group, container.firstChild);
            }
        } else if (role === 'Mitra') {
            container.appendChild(group);
        }

        return group;
    }

    function insertEvidenceDOM(evidence) {
        const role = evidence.uploader_role || 'Saksi';
        getOrCreateEvidenceGroup(role);
        const grid = document.getElementById(`evidence-grid-${role}`);
        if (!grid) return;

        const isImage = evidence.file_type && evidence.file_type.startsWith('image/');
        const isVideo = evidence.file_type && evidence.file_type.startsWith('video/');
        const isAudio = evidence.file_type && evidence.file_type.startsWith('audio/');

        const a = document.createElement('a');
        a.href = "javascript:void(0)";
        a.setAttribute('data-url', canViewEvidence ? evidence.file_url : '#');
        a.setAttribute('data-type', evidence.file_type || '');
        if (canViewEvidence) {
            a.onclick = function() {
                viewEvidence(this.dataset.url, this.dataset.type);
            };
        } else {
            a.onclick = function() {
                alert('Bukti hanya bisa dibuka oleh korban / mitra');
            };
        }

        let cardBg = 'bg-blue-50/50 border-blue-150';
        let badgeBg = 'bg-blue-100 text-blue-800';
        let badgeLabel = 'BERKAS';
        let svgIcon = '';
        let blurClass = !canViewEvidence ? 'blur-[3px]' : '';

        if (isImage) {
            cardBg = 'bg-rose-50/50 border-rose-150';
            badgeBg = 'bg-rose-100 text-rose-800';
            badgeLabel = 'FOTO';
            svgIcon = `
                <svg class="w-10 h-10 text-rose-500 group-hover:scale-110 transition-transform duration-300 ${blurClass}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            `;
        } else if (isVideo) {
            cardBg = 'bg-amber-50/50 border-amber-150';
            badgeBg = 'bg-amber-100 text-amber-800';
            badgeLabel = 'VIDEO';
            svgIcon = `
                <svg class="w-10 h-10 text-amber-500 group-hover:scale-110 transition-transform duration-300 ${blurClass}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
            `;
        } else if (isAudio) {
            cardBg = 'bg-emerald-50/50 border-emerald-150';
            badgeBg = 'bg-emerald-100 text-emerald-800';
            badgeLabel = 'REKAMAN';
            svgIcon = `
                <svg class="w-10 h-10 text-emerald-500 group-hover:scale-110 transition-transform duration-300 ${blurClass}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                </svg>
            `;
        } else {
            svgIcon = `
                <svg class="w-10 h-10 text-blue-500 group-hover:scale-110 transition-transform duration-300 ${blurClass}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            `;
        }

        a.className = `aspect-square rounded-2xl border ${cardBg} p-4 relative group flex flex-col items-center justify-center transition-all duration-300 hover:scale-[1.03] hover:shadow-md fade-in`;

        let lockContent = '';
        if (!canViewEvidence) {
            lockContent = `
                <div class="absolute inset-0 bg-white/60 backdrop-blur-[4px] rounded-2xl flex items-center justify-center">
                    <div class="bg-white/80 p-2 rounded-full shadow-sm border border-gray-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                </div>
            `;
        }

        a.innerHTML = `
            ${svgIcon}
            <span class="mt-3 text-[10px] font-bold tracking-widest uppercase ${badgeBg} px-2 py-0.5 rounded-full">${badgeLabel}</span>
            ${lockContent}
        `;
        grid.appendChild(a);
    }

    function saveEvidenceSession() {
        if (uploadedEvidencesInSession.length > 0) {
            uploadedEvidencesInSession.forEach(evidence => {
                insertEvidenceDOM(evidence);
            });
        }
        uploadedEvidenceIdsInSession = [];
        uploadedEvidencesInSession = [];
        closeEvidenceModal();
    }

    async function cancelEvidenceSession() {
        const idsToDiscard = [...uploadedEvidenceIdsInSession];
        uploadedEvidenceIdsInSession = [];
        uploadedEvidencesInSession = [];
        
        for (const uniqueId in activeUploads) {
            if (activeUploads[uniqueId]) {
                activeUploads[uniqueId].abort();
            }
        }
        
        if (idsToDiscard.length > 0) {
            const list = document.getElementById('upload-list');
            if (list) list.innerHTML = '<div class="text-xs text-red-500 font-semibold italic text-center py-2 animate-pulse">Membatalkan & menghapus seluruh berkas...</div>';
            
            try {
                await Promise.all(idsToDiscard.map(id => 
                    fetch(`/evidence/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                            'Accept': 'application/json'
                        }
                    })
                ));
            } catch(e) {
                console.error('Error discarding session uploads:', e);
            }
        }
        closeEvidenceModal();
    }

    function openEvidenceModal() {
        uploadedEvidenceIdsInSession = [];
        uploadedEvidencesInSession = [];
        const m = document.getElementById('evidence-modal');
        if (!m) return;
        document.body.style.overflow = 'hidden';
        m.classList.remove('hidden');
        m.classList.add('flex');
    }

    function closeEvidenceModal() {
        const m = document.getElementById('evidence-modal');
        if (!m) return;
        m.classList.add('hidden');
        m.classList.remove('flex');
        document.body.style.overflow = '';
        document.getElementById('upload-list').innerHTML = '';
    }



    // Pengiriman lokasi terkini mitra secara real-time
    let mitraWatchId = window._mitraWatchId || null;
    function pushMitraLocation() {
        if (!navigator.geolocation) return;

        const updateMitraCoords = async (pos) => {
            try {
                await fetch(`/tracking/${reportId}/mitra-location`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        latitude: pos.coords.latitude,
                        longitude: pos.coords.longitude
                    })
                });
            } catch (e) {}
        };

        // Ambil posisi awal
        navigator.geolocation.getCurrentPosition(updateMitraCoords, () => {}, {
            enableHighAccuracy: true,
            timeout: 5000
        });

        if (window._mitraWatchId) {
            navigator.geolocation.clearWatch(window._mitraWatchId);
        }

        // Pantau perubahan posisi secara real-time
        mitraWatchId = navigator.geolocation.watchPosition(updateMitraCoords, (err) => {}, {
            enableHighAccuracy: true,
            maximumAge: 0,
            timeout: 10000
        });
        window._mitraWatchId = mitraWatchId;
    }

    if (isMitra && isMitraHandling) {
        pushMitraLocation();
    }

    function openChronologyModal() {
        const m = document.getElementById('chronology-modal');
        if (!m) return;
        document.body.style.overflow = 'hidden';
        m.classList.remove('hidden');
        m.classList.add('flex');
    }

    function closeChronologyModal() {
        const m = document.getElementById('chronology-modal');
        if (!m) return;
        m.classList.add('hidden');
        m.classList.remove('flex');
        document.body.style.overflow = '';
        document.getElementById('chrono-description').value = '';
    }

    async function submitChronology(event) {
        event.preventDefault();
        const desc = document.getElementById('chrono-description').value.trim();
        if (!desc) return;

        const submitBtn = event.target.querySelector('button[type="submit"]');
        const originalBtnHtml = submitBtn.innerHTML;

        // Atur status memuat secara manual
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-70', 'cursor-not-allowed');
        submitBtn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block align-text-bottom" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg> Menyimpan...
        `;

        const lat = window._userLat || null;
        const lng = window._userLng || null;

        try {
            const res = await fetch(`/tracking/${reportId}/chronology`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    description: desc,
                    latitude: lat,
                    longitude: lng
                })
            });

            const data = await res.json();
            if (!res.ok) {
                alert(data.error || 'Gagal menambahkan kronologi.');
                return;
            }

            const emptyState = document.getElementById('chronology-empty-state');
            if (emptyState) emptyState.remove();

            const list = document.getElementById('chronology-list');
            const item = document.createElement('div');
            item.className = "p-4 bg-slate-50 border border-slate-100 rounded-xl fade-in";
            item.innerHTML = `
                <div class="flex justify-between items-start gap-3 flex-wrap">
                    <span class="text-xs font-bold text-slate-800 bg-slate-200 px-2 py-0.5 rounded">
                        ${data.chronology.writer_name}
                    </span>
                    <span class="text-[10px] text-gray-400 font-medium">
                        ${data.chronology.created_at}
                    </span>
                </div>
                <p class="text-sm text-gray-700 mt-2 leading-relaxed whitespace-pre-line">${data.chronology.description}</p>
            `;
            list.insertBefore(item, list.firstChild);

            closeChronologyModal();
        } catch (e) {
            alert('Terjadi kesalahan sistem.');
        } finally {
            // Restore button state
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-70', 'cursor-not-allowed');
            submitBtn.innerHTML = originalBtnHtml;
        }
    }

    // Tampilkan tombol "+ Tambah Kronologi" jika pelapor adalah Korban atau Kontak Darurat, dan kasus tidak sedang ditangani oleh mitra lain
    const isTrustedContact = <?php echo json_encode($isTrustedContact, 15, 512) ?>;
    const isOtherMitraHandling = <?php echo json_encode($isOtherMitraHandling, 15, 512) ?>;
    if ((isCreator || isTrustedContact) && !isOtherMitraHandling) {
        const btn = document.getElementById('btn-add-chronology');
        if (btn) btn.classList.remove('hidden');
    }
</script>


<div id="evidence-modal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-[9999] items-center justify-center px-4 transition-all duration-300">
    <div class="bg-white rounded-2xl w-full max-w-lg p-6 shadow-2xl">
        <div class="flex items-start justify-between gap-3 mb-4">
            <div>
                <h2 class="font-black text-xl text-gray-900 mb-1 font-unbounded">Tambah Bukti</h2>
                <p class="text-gray-500 text-xs">Unggah berkas foto, rekaman suara, atau video untuk melengkapi laporan.</p>
            </div>
            <button type="button" onclick="cancelEvidenceSession()" class="text-gray-400 hover:text-gray-600 transition p-2 rounded-full">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <input type="file" id="evf" multiple class="hidden" accept="image/*,video/*,audio/*">
        <div class="border-2 border-dashed border-gray-300 hover:border-gray-400 rounded-2xl p-8 text-center transition cursor-pointer bg-slate-50 hover:bg-slate-100/50" onclick="document.getElementById('evf').click()">
            <p class="text-4xl mb-3">📁</p>
            <p class="text-gray-700 font-bold text-base">Klik untuk pilih file</p>
            <p class="text-gray-400 text-xs mt-1">Bisa pilih lebih dari 1 (Foto, video, dll)</p>
        </div>
        
        <div id="upload-list" class="mt-4 space-y-2 max-h-60 overflow-y-auto"></div>
        
        <div class="flex flex-col gap-2 mt-5">
            <button type="button" onclick="saveEvidenceSession()" class="w-full bg-red-600 hover:bg-red-700 text-white py-3 rounded-xl font-bold text-sm transition">Simpan Bukti</button>
        </div>
    </div>
</div>


<div id="chronology-modal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-[9999] items-center justify-center px-4 transition-all duration-300">
    <div class="bg-white rounded-2xl w-full max-w-lg p-6 shadow-2xl">
        <div class="flex items-start justify-between gap-3 mb-4">
            <div>
                <h2 class="font-black text-xl text-gray-900 mb-1 font-unbounded">Tambah Kronologi</h2>
                <p class="text-gray-500 text-xs">Berikan detail kronologi atau pemutakhiran situasi terkini untuk membantu mitra krisis.</p>
            </div>
            <button type="button" onclick="closeChronologyModal()" class="text-gray-400 hover:text-gray-600 transition p-2 rounded-full">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="chronology-form" class="no-loading" onsubmit="submitChronology(event)">
            <?php echo csrf_field(); ?>
            <div class="mb-5">
                <label class="block text-sm font-bold text-gray-900 mb-1">Catatan Kronologi / Perkembangan</label>
                <textarea name="description" id="chrono-description" rows="4" required class="w-full border border-gray-200 bg-gray-50 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-red-500 focus:bg-white resize-none" placeholder="Tulis kronologi atau situasi terbaru di sini..."></textarea>
            </div>
            
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeChronologyModal()" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold text-sm transition">Batal</button>
                <button type="submit" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl font-semibold text-sm transition shadow-sm">Simpan Catatan</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
<?php /**PATH D:\CODING\olivia_final\resources\views/pages/tracking.blade.php ENDPATH**/ ?>