<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Safora - Dashboard Mitra</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap');*{font-family:'Inter',sans-serif;} h1,.font-unbounded{font-family:'Space Grotesk',sans-serif!important;}
        * { font-family: 'Space Grotesk', sans-serif; }
        h1 { font-family: 'Space Grotesk', sans-serif !important; }
        .font-unbounded { font-family: 'Space Grotesk', sans-serif !important; }
    </style>
</head>
<body class="min-h-screen bg-gray-50 text-gray-900 antialiased">
@php
    $backUrl = null;
    $showBrand = true;
    $categoryClass = [
        'kekerasan' => 'bg-red-100 text-red-800 border-red-200',
        'salah tangkap' => 'bg-blue-100 text-blue-800 border-blue-200',
        'pelecehan' => 'bg-yellow-100 text-yellow-900 border-yellow-200',
        'kecelakaan' => 'bg-green-100 text-green-800 border-green-200',
    ];
    $typeLabel = match($partner->partner_type) {
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
@endphp
@include('partials.nav-auth')

<main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">


    <header class="mb-8 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <div class="mb-3 flex flex-wrap items-center gap-2">
                <span class="rounded-full border border-green-200 bg-green-50 px-3 py-1 text-xs font-bold text-green-700">Mitra Terverifikasi</span>
                <span class="rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-semibold text-gray-600">{{ $typeLabel }}</span>
            </div>
            <h1 class="text-3xl font-black tracking-tight text-gray-950">{{ $partner->partner_name }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ $partner->city }} - dashboard respons laporan darurat Safora</p>
        </div>
    </header>

    <section class="mb-8" id="pending-reports-container">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-black text-gray-950">Laporan Masuk</h2>
                <p class="text-sm text-gray-500">Pending dan belum melewati batas respons.</p>
            </div>
        </div>

        @if($pendingRoutings->count() > 0)
            <div class="grid gap-4 lg:grid-cols-2">
                @foreach($pendingRoutings as $routing)
                    @php
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
                    @endphp
                    <article class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                        <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full border px-3 py-1 text-xs font-bold {{ $badgeClass }}">{{ $report->category }}</span>
                                <span class="rounded-full px-3 py-1 text-xs font-bold uppercase {{ $urgencyClass }}">{{ $urgencyLabel }}</span>
                                @if($report->anonymous)
                                    <span class="rounded-full bg-purple-50 px-3 py-1 text-xs font-bold text-purple-700">Anonim</span>
                                @endif
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-bold uppercase text-red-700">Menunggu Respons</p>
                                <p class="countdown text-sm font-black text-gray-950" data-expires-at="{{ optional($routing->expires_at)->toIso8601String() }}">--:--</p>
                            </div>
                        </div>

                        <div class="mb-5 grid gap-2 text-sm text-gray-500">
                            <p><span class="font-semibold text-gray-700">Area:</span> {{ $report->location_text ?: ($report->latitude ? 'Sekitar ' . number_format($report->latitude, 3) . ', ' . number_format($report->longitude, 3) : 'Lokasi belum tersedia') }}</p>
                            <p><span class="font-semibold text-gray-700">Jarak:</span> {{ $routing->distance_km !== null ? number_format($routing->distance_km, 1) . ' km' : 'Belum tersedia' }}</p>
                            <p><span class="font-semibold text-gray-700">Waktu masuk:</span> {{ $report->created_at->format('d M Y, H:i') }}</p>
                        </div>

                        <form method="POST" action="{{ route('partner.report.accept', $report->id) }}">
                            @csrf
                            <button type="submit" class="w-full rounded-lg bg-gray-950 px-5 py-3 text-sm font-black text-white transition hover:bg-gray-800">
                                TERIMA KASUS
                            </button>
                        </form>
                    </article>
                @endforeach
            </div>
        @else
            <div class="rounded-lg border border-dashed border-gray-300 bg-white px-6 py-12 text-center">
                <p class="text-lg font-bold text-gray-700">Tidak ada laporan menunggu saat ini</p>
                <p class="mt-1 text-sm text-gray-500">Laporan baru akan muncul di sini selama masih dalam batas respons.</p>
            </div>
        @endif
    </section>

    <section class="mb-8" id="active-reports-container">
        <h2 class="mb-4 text-xl font-black text-gray-950">Sedang Ditangani</h2>
        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
            @forelse($activeReports as $report)
                @php $acceptedAt = optional($report->partnerRoutings->first()?->responded_at)->format('d M Y, H:i'); @endphp
                <div class="flex flex-col gap-3 border-b border-gray-100 p-4 last:border-b-0 md:flex-row md:items-center md:justify-between">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="font-bold text-gray-950">{{ $report->category }}</p>
                            <span class="text-xs font-semibold text-gray-500">{{ $acceptedAt ?: 'Waktu diterima belum tercatat' }}</span>
                        </div>
                        <p class="mt-1 text-sm text-gray-500">{{ $report->anonymous ? 'Anonim' : ($report->user?->name ?? 'Pelapor') }}</p>
                    </div>
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <a href="{{ route('partner.show', $report->id) }}" class="rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-center text-sm font-bold text-gray-800 transition hover:border-gray-500">Buka Laporan</a>
                    </div>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-sm text-gray-500">Belum ada laporan yang sedang ditangani.</div>
            @endforelse
        </div>
    </section>

    <section>
        <div class="rounded-lg border border-gray-200 bg-white p-5 mt-8">
            <h2 class="mb-4 text-xl font-black text-gray-950">Semua Laporan</h2>
            
            <form method="GET" action="{{ route('partner.index') }}" class="mb-6 grid gap-4 sm:grid-cols-2 md:grid-cols-4">
                <input type="text" name="search" placeholder="Cari nama..." value="{{ request('search') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-gray-500 focus:outline-none">
                <select name="handled" class="rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-gray-500 focus:outline-none">
                    <option value="">Semua Status</option>
                    <option value="yes" {{ request('handled') == 'yes' ? 'selected' : '' }}>Ditangani</option>
                    <option value="no" {{ request('handled') == 'no' ? 'selected' : '' }}>Tidak Ditangani</option>
                </select>
                <select name="month" class="rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-gray-500 focus:outline-none">
                    <option value="">Semua Bulan</option>
                    @for($i=1; $i<=12; $i++)
                    <option value="{{ $i }}" {{ request('month') == $i ? 'selected' : '' }}>{{ $monthsId[$i] }}</option>
                    @endfor
                </select>
                <div class="flex gap-2">
                    <select name="year" class="flex-1 rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-gray-500 focus:outline-none">
                        <option value="">Semua Tahun</option>
                        @for($i=date('Y'); $i>=2024; $i--)
                        <option value="{{ $i }}" {{ request('year') == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                    <button type="submit" class="rounded-lg bg-gray-950 px-4 py-2 text-sm font-bold text-white transition hover:bg-gray-800">Filter</button>
                </div>
            </form>

            <div class="border-t border-gray-100">
                @forelse($allReports as $report)
                    <div class="flex flex-col gap-2 border-b border-gray-100 py-4 last:border-b-0 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="font-bold text-gray-900">{{ $report->category }}</p>
                            <p class="text-sm text-gray-500">{{ $report->anonymous ? 'Anonim' : ($report->user?->name ?? 'Pelapor') }} - {{ $report->created_at->format('d M Y, H:i') }}</p>
                            <span class="inline-block mt-1 text-xs font-semibold px-2 py-1 rounded-full {{ $report->handler_partner_id === auth()->user()->partner_id ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $report->handler_partner_id === auth()->user()->partner_id ? 'Ditangani' : 'Tidak Ditangani' }}
                            </span>
                        </div>
                        <a href="{{ route('partner.show', $report->id) }}" class="text-sm font-bold text-gray-700 underline">Lihat Detail</a>
                    </div>
                @empty
                    <div class="py-8 text-center text-sm text-gray-500">Tidak ada laporan yang sesuai dengan filter.</div>
                @endforelse
            </div>
            @if($allReports->hasPages())
            <div class="mt-4">
                {{ $allReports->links() }}
            </div>
            @endif
        </div>
    </section>
</main>

<script>
    function updateCountdowns() {
        document.querySelectorAll('.countdown').forEach(function (node) {
            const raw = node.dataset.expiresAt;
            if (!raw) {
                node.textContent = 'Tanpa batas';
                return;
            }

            const diff = new Date(raw).getTime() - Date.now();
            if (diff <= 0) {
                node.textContent = 'Kedaluwarsa';
                node.closest('article')?.classList.add('opacity-60');
                return;
            }

            const totalSeconds = Math.floor(diff / 1000);
            const hours = String(Math.floor(totalSeconds / 3600)).padStart(2, '0');
            const minutes = String(Math.floor((totalSeconds % 3600) / 60)).padStart(2, '0');
            const seconds = String(totalSeconds % 60).padStart(2, '0');
            node.textContent = (hours > 0 ? hours + ':' : '') + minutes + ':' + seconds;
        });
    }

    updateCountdowns();
    setInterval(updateCountdowns, 1000);

    // Auto-refresh (AJAX Polling) to feel real-time
    setInterval(async function() {
        try {
            // Selalu fetch path dasar /partner agar terbebas dari query string filter pencarian di bawah
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
