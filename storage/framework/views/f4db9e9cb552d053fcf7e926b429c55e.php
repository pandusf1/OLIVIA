<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Klien - <?php echo e($client->name); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap');
        * { font-family: 'Space Grotesk', sans-serif; }
        h1, h2, h3, h4, .font-unbounded { font-family: 'Space Grotesk', sans-serif !important; }
    </style>
    <?php if($location && $location->latitude && $location->longitude): ?>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <style>
        #map {
            height: 380px;
            width: 100%;
            border-radius: 16px;
            border: 1px solid #e5e7eb;
            z-index: 1;
        }
    </style>
    <?php endif; ?>
</head>
<body class="min-h-screen bg-gray-50 text-gray-900 antialiased">

<?php
    $backUrl = route('mitra.index');
    $showBrand = true;
?>

<?php echo $__env->make('partials.nav-auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<main class="max-w-6xl mx-auto px-6 py-10">
    <!-- Tombol Kembali -->
    <div class="mb-6">
        <a href="<?php echo e(route('mitra.index')); ?>" class="inline-flex items-center gap-2 text-sm font-bold text-gray-600 hover:text-gray-900 transition">
            &larr; Kembali ke Dashboard
        </a>
    </div>

    <!-- Page Title / Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-2xl bg-gray-950 flex items-center justify-center text-white text-2xl font-black">
                <?php echo e(strtoupper(substr($client->name, 0, 2))); ?>

            </div>
            <div>
                <h1 class="text-2xl font-black text-gray-950"><?php echo e($client->name); ?></h1>
                <p class="text-sm text-gray-500">Detail Informasi Klien & Layanan yang Dipesan</p>
            </div>
        </div>
        <div>
            <a href="<?php echo e(route('chat.messages', ['mitraId' => $client->id])); ?>" class="inline-flex items-center gap-2 bg-gray-950 hover:bg-black text-white font-bold px-6 py-3 rounded-xl transition shadow-sm text-sm">
                💬 Mulai Chat dengan Klien
            </a>
        </div>
    </div>

    <div class="grid lg:grid-cols-12 gap-8">
        <!-- Left Column: Client Profile & Service details -->
        <div class="lg:col-span-7 space-y-6">
            <!-- Profil Klien Card -->
            <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                <h2 class="text-lg font-black text-gray-950 mb-5 pb-3 border-b border-gray-100 flex items-center gap-2">
                    <span>👤</span> Profil Lengkap Klien
                </h2>
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Nama Lengkap</span>
                        <span class="md:col-span-2 text-sm font-semibold text-gray-900"><?php echo e($client->name); ?></span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Email</span>
                        <span class="md:col-span-2 text-sm font-semibold text-gray-900"><?php echo e($client->email); ?></span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-400">No. WhatsApp / HP</span>
                        <span class="md:col-span-2 text-sm font-semibold text-gray-900"><?php echo e($client->phone ?: '-'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Daftar Layanan Dipesan -->
            <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                <h2 class="text-lg font-black text-gray-950 mb-5 pb-3 border-b border-gray-100 flex items-center gap-2">
                    <span>💳</span> Layanan yang Dipesan
                </h2>
                <div class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $purchasedServices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ps): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="py-4 first:pt-0 last:pb-0">
                            <div class="flex justify-between items-start gap-4">
                                <div>
                                    <h3 class="font-bold text-gray-950 text-sm leading-snug"><?php echo e($ps->priceList?->service_name ?: 'Layanan Umum'); ?></h3>
                                    <p class="text-xs text-gray-400 mt-1 flex items-center gap-2">
                                        <?php if($ps->priceList?->duration && (str_contains(strtolower($ps->priceList->duration), 'sesi') || str_contains(strtolower($ps->priceList->duration), 'session'))): ?>
                                            <span>⏱️ <?php echo e($ps->priceList->duration); ?></span>
                                            <span>•</span>
                                        <?php endif; ?>
                                        <span>📅 <?php echo e($ps->paid_at ? $ps->paid_at->format('d M Y, H:i') : '-'); ?></span>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-gray-900 text-sm">Rp <?php echo e(number_format($ps->priceList?->price ?? 0, 0, ',', '.')); ?></p>
                                    <span class="inline-block mt-2 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider <?php echo e($ps->status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800'); ?>">
                                        <?php echo e($ps->status === 'paid' ? 'Sudah Bayar' : 'Negosiasi'); ?>

                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="py-4 text-center text-sm text-gray-500">Klien belum memesan layanan berbayar.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Column: Map & Coordinates -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Lokasi Klien Card -->
            <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm flex flex-col h-full">
                <h2 class="text-lg font-black text-gray-950 mb-5 pb-3 border-b border-gray-100 flex items-center gap-2">
                    <span>📍</span> Peta Lokasi Kejadian
                </h2>
                <?php if($location && $location->latitude && $location->longitude): ?>
                    <div class="space-y-4 flex-1 flex flex-col">
                        <div id="map"></div>
                        <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 text-xs text-gray-500 space-y-1">
                            <p class="font-bold text-gray-700">Koordinat Lokasi:</p>
                            <p>Latitude: <?php echo e($location->latitude); ?></p>
                            <p>Longitude: <?php echo e($location->longitude); ?></p>
                            <p class="mt-2 text-[10px] italic">Note: Peta ini menunjukkan titik pelaporan masyarakat pada saat meminta bantuan.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="flex-1 flex flex-col items-center justify-center py-12 px-4 border border-dashed border-gray-300 rounded-2xl bg-gray-50/50 text-center">
                        <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center text-3xl mb-4">🔕</div>
                        <h3 class="font-bold text-gray-700 text-sm">Lokasi Tidak Tersedia</h3>
                        <p class="text-xs text-gray-400 mt-2 max-w-xs leading-relaxed">Masyarakat/pelapor tidak membagikan koordinat lokasi GPS untuk kasus atau pemesanan ini.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php if($location && $location->latitude && $location->longitude): ?>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        var latitude = <?php echo e($location->latitude); ?>;
        var longitude = <?php echo e($location->longitude); ?>;
        var clientName = "<?php echo e($client->name); ?>";

        var map = L.map('map').setView([latitude, longitude], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        var clientMarker = L.marker([latitude, longitude], {
            icon: L.icon({
                iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            })
        }).addTo(map);

        clientMarker.bindPopup("<b>" + clientName + "</b><br>Lokasi Pelaporan Klien.").openPopup();
    });
</script>
<?php endif; ?>

</body>
</html>
<?php /**PATH D:\CODING\olivia_final\resources\views/pages/mitra/client_details.blade.php ENDPATH**/ ?>