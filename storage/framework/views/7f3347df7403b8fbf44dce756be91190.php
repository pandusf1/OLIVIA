<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mitra</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap');*{font-family:'Inter',sans-serif;} h1,.font-unbounded{font-family:'Space Grotesk',sans-serif!important;}
        * { font-family: 'Space Grotesk', sans-serif; }
        h1 { font-family: 'Space Grotesk', sans-serif !important; }
        .font-unbounded { font-family: 'Space Grotesk', sans-serif !important; }
    </style>
</head>
<body class="min-h-screen bg-gray-50 text-gray-900 antialiased">
<?php
    $backUrl = null;
    $showBrand = true;
    $categoryClass = [
        'kekerasan' => 'bg-red-100 text-red-800 border-red-200',
        'salah tangkap' => 'bg-blue-100 text-blue-800 border-blue-200',
        'pelecehan' => 'bg-yellow-100 text-yellow-900 border-yellow-200',
        'kecelakaan' => 'bg-green-100 text-green-800 border-green-200',
    ];
    $typeLabel = match($mitra->mitra_type) {
        'ambulance' => 'Medis Darurat',
        'legal' => 'Bantuan Hukum',
        'counselor' => 'Psikososial',
        'pemadam' => 'Pemadam / Rescue',
        default => 'Mitra Krisis'
    };
    $monthsId = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
?>
<?php echo $__env->make('partials.nav-auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<main class="max-w-6xl mx-auto px-6 py-10 fade-in">

    <?php if(in_array($mitra->mitra_type, ['legal', 'counselor'], true)): ?>
    <style>
        .accordion-content {
            max-height: 0;
            opacity: 0;
            overflow: hidden;
            transition: max-height 0.35s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.25s ease-out, margin-top 0.35s ease-out;
        }
        .accordion-content.open {
            opacity: 1;
        }
    </style>
    <section class="mb-8 bg-white border border-gray-200 rounded-2xl p-4 sm:p-6 shadow-sm">
        <h2 onclick="toggleProfilPembayaran()" class="text-lg font-black text-gray-950 flex items-center justify-between cursor-pointer select-none">
            <div class="flex items-center gap-2">
                Kelola Profil & Informasi Pembayaran
            </div>
            <svg id="profil-pembayaran-arrow" class="w-5 h-5 text-gray-500 transition-transform duration-300 transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
            </svg>
        </h2>
        
        <div id="profil-pembayaran-content" class="accordion-content">
            <div class="border-t border-gray-100 pt-5 mt-4">
                <div class="grid md:grid-cols-12 gap-6 md:gap-8">
                    <!-- Form Update Profil & Pembayaran (Kiri) -->
                    <div class="md:col-span-6 space-y-4">
                        <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider text-gray-400 mb-3">Informasi Umum & Rekening</h3>
                        
                        <form method="POST" action="<?php echo e(route('mitra.profile.update')); ?>" class="space-y-4">
                            <?php echo csrf_field(); ?>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Catatan Mitra (Jam Kerja/Keterangan)</label>
                                <textarea name="catatan" rows="8" placeholder="Masukkan deskripsi layanan, jam kerja, atau catatan penting..." class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-xs focus:border-gray-900 focus:outline-none leading-relaxed h-52"><?php echo e($mitra->catatan); ?></textarea>
                            </div>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Nama Bank</label>
                                    <select name="bank_name" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-xs focus:border-gray-900 focus:outline-none">
                                        <option value="">-- Pilih Bank --</option>
                                        <?php $__currentLoopData = ['BCA', 'Mandiri', 'BNI', 'BRI', 'CIMB Niaga', 'Permata', 'Danamon']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($b); ?>" <?php echo e($mitra->bank_name === $b ? 'selected' : ''); ?>><?php echo e($b); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">No. Rekening</label>
                                    <input type="text" name="nomor_rekening" placeholder="1234567890" value="<?php echo e($mitra->nomor_rekening); ?>" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-xs focus:border-gray-900 focus:outline-none">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">E-Wallet</label>
                                    <select name="ewallet_name" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-xs focus:border-gray-900 focus:outline-none">
                                        <option value="">-- Pilih E-Wallet --</option>
                                        <?php $__currentLoopData = ['GoPay', 'OVO', 'DANA', 'LinkAja', 'ShopeePay']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ew): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($ew); ?>" <?php echo e($mitra->ewallet_name === $ew ? 'selected' : ''); ?>><?php echo e($ew); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">No. HP E-Wallet</label>
                                    <input type="text" name="nomor_ewallet" placeholder="08123456789" value="<?php echo e($mitra->nomor_ewallet); ?>" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-xs focus:border-gray-900 focus:outline-none">
                                </div>
                            </div>

                            <button type="submit" class="w-full bg-gray-900 hover:bg-black text-white font-bold py-2.5 px-4 rounded-xl transition text-xs shadow-sm">
                                Simpan Perubahan
                            </button>
                        </form>
                    </div>

                    <!-- Manage Price Lists (Kanan) -->
                    <div class="md:col-span-6 space-y-6">
                        <div class="space-y-3">
                            <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider text-gray-400">Daftar Pricelist Layanan</h3>
                            
                            <?php
                                $currentPricelists = \App\Models\PriceList::where('mitra_id', $mitra->id)->get();
                            ?>

                            <div class="max-h-56 overflow-y-auto border border-gray-200 rounded-xl divide-y divide-gray-100 bg-white">
                                <?php $__empty_1 = true; $__currentLoopData = $currentPricelists; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <div class="flex justify-between items-center p-3 hover:bg-gray-50/50">
                                        <div class="min-w-0 flex-1 pr-3">
                                            <p class="font-bold text-gray-950 text-xs truncate leading-snug"><?php echo e($pl->service_name); ?></p>
                                            <p class="text-[11px] text-gray-400 mt-0.5">Rp <?php echo e(number_format($pl->price, 0, ',', '.')); ?> <?php echo e(($pl->duration && (str_contains(strtolower($pl->duration), 'sesi') || str_contains(strtolower($pl->duration), 'session'))) ? '• ' . $pl->duration : ''); ?></p>
                                        </div>
                                        <form method="POST" action="<?php echo e(route('mitra.price-list.destroy', $pl->id)); ?>" onsubmit="return confirm('Hapus pricelist ini?')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="text-[10px] font-bold text-red-600 bg-red-50 hover:bg-red-100 px-2.5 py-1.5 rounded-lg transition">Hapus</button>
                                        </form>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <div class="p-4 text-center text-xs text-gray-400">Belum ada layanan terdaftar.</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Form Tambah Pricelist -->
                        <form method="POST" action="<?php echo e(route('mitra.price-list.store')); ?>" class="p-4 border border-dashed border-gray-300 bg-gray-50/30 rounded-xl space-y-3">
                            <?php echo csrf_field(); ?>
                            <h4 class="font-bold text-gray-950 text-xs uppercase tracking-wider">Tambah Layanan Baru</h4>
                            
                            <div class="grid grid-cols-2 gap-3">
                                <input type="text" name="service_name" placeholder="Nama Layanan (cth. Konsultasi Hukum)" required class="col-span-2 rounded-xl border border-gray-300 px-3 py-2 text-xs focus:border-gray-950 focus:outline-none">
                                <input type="number" name="price" placeholder="Harga (Rp)" required class="rounded-xl border border-gray-300 px-3 py-2 text-xs focus:border-gray-950 focus:outline-none">
                                <input type="text" name="duration" placeholder="Durasi (khusus sesi, cth. 1 sesi)" class="rounded-xl border border-gray-300 px-3 py-2 text-xs focus:border-gray-950 focus:outline-none">
                            </div>

                            <button type="submit" class="w-full bg-gray-950 hover:bg-gray-800 text-white font-bold py-2.5 rounded-xl transition text-xs shadow-sm">
                                Tambah Layanan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <section class="mb-8" id="pending-reports-container">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-black text-gray-950">Laporan Masuk</h2>
                <p class="text-sm text-gray-500">Pending dan belum melewati batas respons.</p>
            </div>
        </div>

        <?php if($pendingRoutings->count() > 0): ?>
            <div class="grid gap-4 lg:grid-cols-2">
                <?php $__currentLoopData = $pendingRoutings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $routing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $report = $routing->report;
                        $catKey = strtolower($report->category);
                        $badgeClass = $categoryClass[$catKey] ?? 'bg-gray-100 text-gray-700 border-gray-200';
                        $urgencyClass = [
                            'critical' => 'bg-red-700 text-white',
                            'high' => 'bg-orange-100 text-orange-800',
                            'normal' => 'bg-gray-100 text-gray-700',
                        ][$report->urgency_level ?? 'high'] ?? 'bg-orange-100 text-orange-800';
                        $urgencyLabel = match($report->urgency_level ?? 'high') {
                            'critical' => 'Kritis',
                            'high' => 'Tinggi',
                            'normal' => 'Normal',
                            default => 'Tinggi'
                        };
                    ?>
                    <article class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm hover:shadow-md transition">
                        <a href="<?php echo e(route('mitra.show', $report->id)); ?>" class="block group mb-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full border px-3 py-1 text-xs font-bold <?php echo e($badgeClass); ?>"><?php echo e($report->category); ?></span>
                                    <span class="rounded-full px-3 py-1 text-xs font-bold uppercase <?php echo e($urgencyClass); ?>"><?php echo e($urgencyLabel); ?></span>
                                    <?php if($report->anonymous): ?>
                                        <span class="rounded-full bg-purple-50 px-3 py-1 text-xs font-bold text-purple-700">Anonim</span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-right">
                                    <p class="pt-1 text-xs font-bold uppercase text-red-700 group-hover:underline">Menunggu Respons</p>
                                </div>
                            </div>

                            <div class="mt-4 grid gap-2 text-sm text-gray-500">
                                <p><span class="font-semibold text-gray-700">Area:</span> <?php echo e($report->location_text ?: ($report->latitude ? 'Sekitar ' . number_format($report->latitude, 3) . ', ' . number_format($report->longitude, 3) : 'Lokasi belum tersedia')); ?></p>
                                <p><span class="font-semibold text-gray-700">Jarak:</span> <?php echo e($routing->distance_km !== null ? number_format($routing->distance_km, 1) . ' km' : 'Belum tersedia'); ?></p>
                                <p><span class="font-semibold text-gray-700">Waktu masuk:</span> <?php echo e($report->created_at->format('d M Y, H:i')); ?></p>
                            </div>
                        </a>

                        <form method="POST" action="<?php echo e(route('mitra.report.accept', $report->id)); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="w-full rounded-lg bg-gray-950 px-5 py-3 text-sm font-black text-white transition hover:bg-gray-800">
                                TERIMA KASUS
                            </button>
                        </form>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php else: ?>
            <div class="rounded-lg border border-dashed border-gray-300 bg-white px-6 py-12 text-center">
                <p class="text-lg font-bold text-gray-700">Tidak ada laporan menunggu saat ini</p>
                <p class="mt-1 text-sm text-gray-500">Laporan baru akan muncul di sini selama masih dalam batas respons.</p>
            </div>
        <?php endif; ?>
    </section>

    <section class="mb-8" id="active-reports-container">
        <h2 class="mb-4 text-xl font-black text-gray-950">Sedang Ditangani</h2>
        
        <?php if(in_array($mitra->mitra_type, ['legal', 'counselor'], true)): ?>
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <?php $__empty_1 = true; $__currentLoopData = $activeClients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $userId => $payments): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $firstPay = $payments->first();
                        $clientName = $firstPay->user?->name ?? 'Pelapor';
                        $date = $firstPay->paid_at ? \Carbon\Carbon::parse($firstPay->paid_at)->format('d M Y, H:i') : '-';
                        $services = $payments->map(fn($p) => $p->priceList?->service_name)->filter()->implode(', ');
                    ?>
                    <div class="flex flex-col gap-3 border-b border-gray-100 p-5 last:border-b-0 md:flex-row md:items-center md:justify-between hover:bg-gray-50/50 transition cursor-pointer" onclick="window.location.href='<?php echo e(route('mitra.client.show', $userId)); ?>'">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="font-bold text-gray-950 text-base"><?php echo e($clientName); ?></h3>
                                <span class="text-[11px] font-semibold text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full"><?php echo e($date); ?></span>
                            </div>
                            <p class="mt-1 text-xs text-gray-500 truncate"><span class="font-semibold text-gray-600">Layanan:</span> <?php echo e($services ?: 'Layanan Umum'); ?></p>
                        </div>
                        <div class="flex gap-2 shrink-0" onclick="event.stopPropagation()">
                            <a href="<?php echo e(route('mitra.client.show', $userId)); ?>" class="rounded-xl border border-gray-200 bg-white hover:bg-gray-50 px-4 py-2.5 text-center text-xs font-bold text-gray-700 transition">Detail Client</a>
                            <a href="<?php echo e(route('chat.messages', ['mitraId' => $userId])); ?>" class="rounded-xl bg-gray-900 hover:bg-black px-4 py-2.5 text-center text-xs font-bold text-white transition flex items-center gap-1.5 shadow-sm">
                                💬 Mulai Chat
                            </a>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="px-6 py-12 text-center text-sm text-gray-500">Belum ada client aktif yang memesan layanan saat ini.</div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
                <?php $__empty_1 = true; $__currentLoopData = $activeReports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php $acceptedAt = optional($report->mitraRoutings->first()?->responded_at)->format('d M Y, H:i'); ?>
                    <div class="flex flex-col gap-3 border-b border-gray-100 p-4 last:border-b-0 md:flex-row md:items-center md:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-bold text-gray-950"><?php echo e($report->category); ?></p>
                                <span class="text-xs font-semibold text-gray-500"><?php echo e($acceptedAt ?: 'Waktu diterima belum tercatat'); ?></span>
                            </div>
                            <p class="mt-1 text-sm text-gray-500"><?php echo e($report->anonymous ? 'Anonim' : ($report->user?->name ?? 'Pelapor')); ?></p>
                        </div>
                        <div class="flex flex-col gap-2 sm:flex-row">
                            <a href="<?php echo e(route('mitra.show', $report->id)); ?>" class="rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-center text-sm font-bold text-gray-800 transition hover:border-gray-500">Buka Laporan</a>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="px-6 py-8 text-center text-sm text-gray-500">Belum ada laporan yang sedang ditangani.</div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>

    <section>
        <div class="rounded-lg border border-gray-200 bg-white p-5 mt-8">
            <h2 class="mb-4 text-xl font-black text-gray-950">Semua Laporan</h2>
            
            <form method="GET" action="<?php echo e(route('mitra.index')); ?>" class="mb-6 grid gap-4 sm:grid-cols-2 md:grid-cols-4">
                <input type="text" name="search" placeholder="Cari nama..." value="<?php echo e(request('search')); ?>" class="rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-gray-500 focus:outline-none">
                <select name="handled" class="rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-gray-500 focus:outline-none">
                    <option value="">Semua Status</option>
                    <option value="yes" <?php echo e(request('handled') == 'yes' ? 'selected' : ''); ?>>Ditangani</option>
                    <option value="no" <?php echo e(request('handled') == 'no' ? 'selected' : ''); ?>>Tidak Ditangani</option>
                </select>
                <select name="month" class="rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-gray-500 focus:outline-none">
                    <option value="">Semua Bulan</option>
                    <?php for($i=1; $i<=12; $i++): ?>
                    <option value="<?php echo e($i); ?>" <?php echo e(request('month') == $i ? 'selected' : ''); ?>><?php echo e($monthsId[$i]); ?></option>
                    <?php endfor; ?>
                </select>
                <div class="flex gap-2">
                    <select name="year" class="flex-1 rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-gray-500 focus:outline-none">
                        <option value="">Semua Tahun</option>
                        <?php for($i=date('Y'); $i>=2024; $i--): ?>
                        <option value="<?php echo e($i); ?>" <?php echo e(request('year') == $i ? 'selected' : ''); ?>><?php echo e($i); ?></option>
                        <?php endfor; ?>
                    </select>
                    <button type="submit" class="rounded-lg bg-gray-950 px-4 py-2 text-sm font-bold text-white transition hover:bg-gray-800">Filter</button>
                </div>
            </form>

            <div class="border-t border-gray-100">
                <?php $__empty_1 = true; $__currentLoopData = $allReports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="flex flex-col gap-2 border-b border-gray-100 py-4 last:border-b-0 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="font-bold text-gray-900"><?php echo e($report->category); ?></p>
                            <p class="text-sm text-gray-500"><?php echo e($report->anonymous ? 'Anonim' : ($report->user?->name ?? 'Pelapor')); ?> - <?php echo e($report->created_at->format('d M Y, H:i')); ?></p>
                            <span class="inline-block mt-1 text-xs font-semibold px-2 py-1 rounded-full <?php echo e($report->handler_mitra_id === auth()->user()->mitra_id ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'); ?>">
                                <?php echo e($report->handler_mitra_id === auth()->user()->mitra_id ? 'Ditangani' : 'Tidak Ditangani'); ?>

                            </span>
                        </div>
                        <a href="<?php echo e(route('mitra.show', $report->id)); ?>" class="text-sm font-bold text-gray-700 underline">Lihat Detail</a>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="py-8 text-center text-sm text-gray-500">Tidak ada laporan yang sesuai dengan filter.</div>
                <?php endif; ?>
            </div>
            <?php if($allReports->hasPages()): ?>
            <div class="mt-4">
                <?php echo e($allReports->links()); ?>

            </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<script>
    function toggleProfilPembayaran() {
        const content = document.getElementById('profil-pembayaran-content');
        const arrow = document.getElementById('profil-pembayaran-arrow');
        if (!content || !arrow) return;
        const isOpen = content.classList.contains('open');
        
        if (isOpen) {
            content.style.maxHeight = '0px';
            content.classList.remove('open');
            arrow.style.transform = 'rotate(0deg)';
        } else {
            content.classList.add('open');
            content.style.maxHeight = content.scrollHeight + 'px';
            arrow.style.transform = 'rotate(180deg)';
            
            setTimeout(() => {
                if (content.classList.contains('open')) {
                    content.style.maxHeight = 'none';
                }
            }, 350);
        }
    }

    // Auto-refresh (AJAX Polling) to feel real-time
    setInterval(async function() {
        try {
            // Selalu fetch path dasar /mitra agar terbebas dari query string filter pencarian di bawah
            const fetchUrl = window.location.origin + window.location.pathname;
            const res = await fetch(fetchUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const text = await res.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(text, 'text/html');
            
            // Update Laporan Masuk
            const newPending = doc.querySelector('#pending-reports-container');
            const currentPending = document.querySelector('#pending-reports-container');
            if (newPending && currentPending) {
                currentPending.innerHTML = newPending.innerHTML;
            }
            
            // Update Laporan Sedang Ditangani
            const newActive = doc.querySelector('#active-reports-container');
            const currentActive = document.querySelector('#active-reports-container');
            if (newActive && currentActive) {
                currentActive.innerHTML = newActive.innerHTML;
            }
            
            updateCountdowns(); // Re-init countdowns on new elements
        } catch (e) {}
    }, 5000);
</script>
</body>
</html>
<?php /**PATH D:\CODING\olivia_final\resources\views/pages/mitra/index.blade.php ENDPATH**/ ?>