<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap');
        * { font-family: 'Space Grotesk', sans-serif; }
        h1 { font-family: 'Space Grotesk', sans-serif !important; }
        .font-unbounded { font-family: 'Space Grotesk', sans-serif !important; }
    </style>

</head>
<body class="min-h-screen bg-gray-50 text-gray-900 antialiased">
@php
    $backUrl = null;
    $showBrand = true;
    $statusClass = [
        'Submitted' => 'bg-gray-100 text-gray-700',
        'Routed' => 'bg-blue-100 text-blue-800',
        'Viewed' => 'bg-yellow-100 text-yellow-900',
        'In Progress' => 'bg-orange-100 text-orange-800',
        'Resolved' => 'bg-green-100 text-green-800',
    ];
@endphp
@include('partials.nav-auth')

<main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">


    <header class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-gray-500">Pusat Monitoring</p>
            <h1 class="mt-1 text-3xl font-black text-gray-950">Dashboard Admin</h1>
            <p class="mt-1 text-sm text-gray-500">Pantau laporan, respons mitra, dan aktivitas platform Safora.</p>
        </div>
        <a href="{{ route('admin.mitras') }}" class="rounded-lg bg-gray-950 px-5 py-3 text-sm font-bold text-white transition hover:bg-gray-800">Manajemen Mitra</a>
    </header>

    <section class="mb-8 grid grid-cols-2 gap-3 lg:grid-cols-6">
        @foreach([
            ['Total Laporan', $stats['reports'], 'text-gray-950'],
            ['Laporan Hari Ini', $stats['today'], 'text-blue-700'],
            ['Laporan Darurat', $stats['emergency'], 'text-red-700'],
            ['Belum Ditangani', $stats['unhandled'], 'text-orange-700'],
            ['Selesai', $stats['resolved'], 'text-green-700'],
            ['Mitra Aktif', $stats['active_mitras'], 'text-indigo-700'],
        ] as [$label, $value, $color])
            <div class="rounded-lg border border-gray-200 bg-white p-4">
                <p class="text-xs font-bold uppercase text-gray-500">{{ $label }}</p>
                <p class="mt-2 text-3xl font-black {{ $color }}">{{ $value }}</p>
            </div>
        @endforeach
    </section>

    <section class="mb-8" id="unhandled-reports-section">
        <div class="mb-4 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-xl font-black text-orange-950">Laporan Tidak Tertangani</h2>
                <p class="text-sm text-orange-800">Tidak ada mitra yang menerima (accept) dan tidak ada rute tertunda yang masih valid.</p>
            </div>
            <span class="rounded-full bg-white px-3 py-1 text-sm font-bold text-orange-800">{{ $unhandledReports->count() }} laporan</span>
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
                    @forelse($unhandledReports as $report)
                        <tr>
                            <td class="px-4 py-3 font-mono text-xs">{{ strtoupper(substr($report->id, 0, 8)) }}</td>
                            <td class="px-4 py-3 font-semibold">{{ $report->category }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $report->created_at->format('d M Y, H:i') }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $report->created_at->diffForHumans(null, true) }}</td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('admin.reports.reroute', $report->id) }}">
                                    @csrf
                                    <button class="rounded-lg bg-orange-700 px-3 py-2 text-xs font-bold text-white transition hover:bg-orange-800">Re-route Manual</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">Tidak ada laporan tidak tertangani.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="mb-8 rounded-lg border border-gray-200 bg-white p-5">
        <h2 class="mb-4 text-xl font-black text-gray-950">Monitoring Mitra</h2>
        <div class="overflow-x-auto">
            <table class="sortable min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-bold uppercase text-gray-500">
                    <tr>
                        <th class="cursor-pointer px-4 py-3">Nama Mitra</th>
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
                    @foreach($mitras as $mitra)
                        @php
                            $pTypeLabel = match($mitra->mitra_type) {
                                'ambulance' => 'Medis Darurat',
                                'legal' => 'Bantuan Hukum',
                                'counselor' => 'Psikososial',
                                'pemadam' => 'Pemadam / Rescue',
                                'police' => 'Kepolisian',
                                default => 'Mitra Krisis'
                            };
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-bold">{{ $mitra->mitra_name }}</td>
                            <td class="px-4 py-3">{{ $pTypeLabel }}</td>
                            <td class="px-4 py-3">{{ $mitra->city }}</td>
                            <td class="px-4 py-3">{{ $mitra->verified ? 'Ya' : 'Tidak' }}</td>
                            <td class="px-4 py-3">{{ $mitra->accepted_count }}</td>
                            <td class="px-4 py-3">{{ $mitra->average_response_minutes !== null ? $mitra->average_response_minutes . ' menit' : '-' }}</td>
                            <td class="px-4 py-3">{{ $mitra->active_reports_count }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $mitra->activity_status === 'Aktif' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">{{ $mitra->activity_status }}</span>
                            </td>
                        </tr>
                    @endforeach
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
            <form method="GET" action="{{ route('admin.index') }}" class="grid gap-2 sm:grid-cols-2 lg:grid-cols-6">
                <select name="status" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">Semua Status</option>
                    @foreach(['Submitted' => 'Diajukan', 'Routed' => 'Diteruskan', 'Viewed' => 'Ditinjau', 'In Progress' => 'Diproses', 'Resolved' => 'Selesai'] as $statusVal => $statusLbl)
                        <option value="{{ $statusVal }}" @selected(request('status') === $statusVal)>{{ $statusLbl }}</option>
                    @endforeach
                </select>
                <select name="category" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                    @endforeach
                </select>
                <select name="report_type" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">Semua Tipe</option>
                    <option value="Emergency" @selected(request('report_type') === 'Emergency')>Emergency</option>
                    <option value="quick_emergency" @selected(request('report_type') === 'quick_emergency')>Quick Emergency</option>
                </select>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                <select name="mitra_id" class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">Semua Mitra</option>
                    @foreach($mitras as $mitra)
                        <option value="{{ $mitra->id }}" @selected(request('mitra_id') === $mitra->id)>{{ $mitra->mitra_name }}</option>
                    @endforeach
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
                        <th class="px-4 py-3">Mitra Penangan</th>
                        <th class="px-4 py-3">Waktu</th>
                        <th class="px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($reports as $report)
                        @php
                            $handler = $report->mitraRoutings->firstWhere('status', 'accepted')?->mitra;
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
                        @endphp
                        <tr>
                            <td class="px-4 py-3 font-mono text-xs">{{ strtoupper(substr($report->id, 0, 8)) }}</td>
                            <td class="px-4 py-3">{{ $report->report_type }}</td>
                            <td class="px-4 py-3 font-semibold">{{ $report->category }}</td>
                            <td class="px-4 py-3"><span class="rounded-full px-3 py-1 text-xs font-bold {{ $statusClass[$report->status] ?? 'bg-gray-100 text-gray-700' }}">{{ $statusIndo }}</span></td>
                            <td class="px-4 py-3">{{ $report->anonymous ? 'Anonim' : ($report->user?->name ?? 'Tanpa user') }}</td>
                            <td class="px-4 py-3">{{ $handler?->mitra_name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $report->created_at->format('d M Y, H:i') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('tracking.show', $report->id) }}" class="rounded-lg border border-gray-200 px-3 py-2 text-xs font-bold text-gray-700">Lihat Detail</a>
                                    @if(!$handler)
                                        <form method="POST" action="{{ route('admin.reports.reroute', $report->id) }}">@csrf<button class="rounded-lg border border-orange-200 px-3 py-2 text-xs font-bold text-orange-800">Re-route</button></form>
                                    @endif
                                    @if($report->status !== 'Resolved')
                                        <form method="POST" action="{{ route('admin.reports.resolve', $report->id) }}">@csrf<button class="rounded-lg border border-green-200 px-3 py-2 text-xs font-bold text-green-800">Resolve Manual</button></form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
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
                    @forelse($auditLogs as $log)
                        <tr>
                            <td class="px-4 py-3">{{ optional($log->created_at)->format('d M Y, H:i') }}</td>
                            <td class="px-4 py-3 font-semibold">{{ $log->action }}</td>
                            <td class="px-4 py-3">{{ $log->target_type ?? '-' }} {{ $log->target_id ? strtoupper(substr($log->target_id, 0, 8)) : '' }}</td>
                            <td class="px-4 py-3">{{ $log->user_id ? 'User ' . strtoupper(substr($log->user_id, 0, 8)) : 'system' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">Belum ada aktivitas.</td></tr>
                    @endforelse
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
