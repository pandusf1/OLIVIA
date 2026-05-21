<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Safora — Dashboard Admin</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

        *{
            font-family:'Inter',sans-serif;
        }
    </style>
</head>

<body class="bg-[#faf9f7] text-gray-900 antialiased min-h-screen">

@php $backUrl = null; @endphp

    @php $showBrand = true; @endphp
    @include('partials.nav-auth')

    <div class="max-w-6xl mx-auto px-6 py-10">

        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">

            <div>

                <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-1">
                    CONTROL CENTER
                </p>

                <h1 class="font-unbounded text-3xl font-black text-gray-900">
                    Dashboard Admin
                </h1>

                <p class="text-gray-400 text-sm mt-1">
                    Monitoring platform, partner, dan aktivitas laporan Safora.
                </p>

            </div>

            <div class="bg-white border border-gray-200 rounded-2xl px-5 py-3">

                <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">
                    Total Laporan
                </p>

                <p class="text-2xl font-black text-gray-900">
                    {{ $stats['reports'] }}
                </p>

            </div>

        </div>

        {{-- STATS --}}
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">

            <div class="bg-white border border-gray-200 rounded-2xl p-5">

                <p class="text-xs uppercase tracking-wider text-gray-400 font-semibold mb-2">
                    Emergency
                </p>

                <p class="text-3xl font-black text-red-700">
                    {{ $stats['emergency'] }}
                </p>

            </div>

            <div class="bg-white border border-gray-200 rounded-2xl p-5">

                <p class="text-xs uppercase tracking-wider text-gray-400 font-semibold mb-2">
                    Resolved
                </p>

                <p class="text-3xl font-black text-green-700">
                    {{ $stats['resolved'] }}
                </p>

            </div>

            <div class="bg-white border border-gray-200 rounded-2xl p-5">

                <p class="text-xs uppercase tracking-wider text-gray-400 font-semibold mb-2">
                    Partner
                </p>

                <p class="text-3xl font-black text-blue-700">
                    {{ $stats['partners'] }}
                </p>

            </div>

            <div class="bg-white border border-gray-200 rounded-2xl p-5">

                <p class="text-xs uppercase tracking-wider text-gray-400 font-semibold mb-2">
                    User
                </p>

                <p class="text-3xl font-black text-gray-900">
                    {{ $stats['users'] }}
                </p>

            </div>

            <div class="bg-white border border-gray-200 rounded-2xl p-5">

                <p class="text-xs uppercase tracking-wider text-gray-400 font-semibold mb-2">
                    Active Cases
                </p>

                <p class="text-3xl font-black text-orange-600">
                    {{ $stats['reports'] - $stats['resolved'] }}
                </p>

            </div>

        </div>

        {{-- QUICK MENU --}}
        <div class="grid md:grid-cols-2 gap-4 mb-8">

            {{-- PARTNER MANAGEMENT --}}
            <a href="{{ route('admin.partners') }}"
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

            {{-- MONITORING --}}
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

        {{-- RECENT REPORTS --}}
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
                    {{ $reports->count() }} laporan
                </div>

            </div>

            @php
                $sc = [
                    'Submitted' => 'bg-gray-100 text-gray-600',
                    'Routed' => 'bg-blue-50 text-blue-700',
                    'Viewed' => 'bg-yellow-50 text-yellow-700',
                    'In Progress' => 'bg-orange-50 text-orange-700',
                    'Resolved' => 'bg-green-50 text-green-700',
                ];
            @endphp

            @if($reports->count() > 0)

                <div class="space-y-3">

                    @foreach($reports as $report)

                    <a href="/partner/report/{{ $report->id }}"
                       class="flex items-center justify-between border border-gray-200 hover:border-gray-300 rounded-2xl px-5 py-4 transition">

                        <div class="flex-1 min-w-0">

                            <div class="flex items-center gap-2 flex-wrap mb-1">

                                <p class="font-bold text-gray-900">
                                    {{ $report->category }}
                                </p>

                                @if($report->anonymous)
                                    <span class="bg-purple-50 text-purple-700 text-xs px-2 py-0.5 rounded-full">
                                        Anonim
                                    </span>
                                @endif

                            </div>

                            @if($report->description)
                                <p class="text-sm text-gray-400 truncate">
                                    {{ $report->description }}
                                </p>
                            @endif

                            <p class="text-xs text-gray-400 mt-1">
                                {{ $report->created_at->format('d M Y, H:i') }}
                            </p>

                        </div>

                        <span class="text-xs font-semibold px-3 py-1.5 rounded-full ml-4 {{ $sc[$report->status] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ $report->status }}
                        </span>

                    </a>

                    @endforeach

                </div>

            @else

                <div class="text-center py-16">

                    <p class="text-5xl mb-4">
                        📭
                    </p>

                    <p class="text-gray-400">
                        Belum ada laporan masuk.
                    </p>

                </div>

            @endif

        </div>

    </div>

</body>
</html>
