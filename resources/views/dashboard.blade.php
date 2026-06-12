<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Space Grotesk', sans-serif; }
        h1 { font-family: 'Space Grotesk', sans-serif !important; }
        .font-unbounded { font-family: 'Space Grotesk', sans-serif !important; }
        @keyframes pulse-ring {
            0% { box-shadow: 0 0 0 0 rgba(185,28,28,0.4); }
            70% { box-shadow: 0 0 0 14px rgba(185,28,28,0); }
            100% { box-shadow: 0 0 0 0 rgba(185,28,28,0); }
        }
        .panic-pulse { animation: pulse-ring 2s infinite; }
        @keyframes fade-in { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }
        .fade-in { animation: fade-in 0.35s ease both; }
        @keyframes modalIn { from{opacity:0;transform:scale(.95)} to{opacity:1;transform:scale(1)} }
        .modal-in { animation: modalIn 0.25s ease forwards; }
    </style>
    @include('partials.vercel-analytics')
</head>
<body class="bg-[#f5f4f1] text-gray-900 antialiased min-h-screen">

    @php $backUrl = null; @endphp
    @include('partials.nav-auth')
 
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-6 sm:py-10 fade-in">
  
        {{-- ===== BIG EMERGENCY BUTTON ===== --}}
        <div class="flex justify-center mb-10 mt-6 fade-in">
            <div class="relative flex flex-col items-center">
                {{-- Decorative background pulse --}}
                <div class="absolute inset-0 bg-red-100 rounded-full scale-[1.3] opacity-60 panic-pulse pointer-events-none"></div>
                <div class="absolute inset-0 bg-red-200 rounded-full scale-[1.15] opacity-80 pointer-events-none"></div>

                <button onclick="startPanic()" id="panic-btn" class="relative z-10 w-52 h-52 sm:w-64 sm:h-64 bg-red-700 hover:bg-red-800 text-white rounded-full flex flex-col items-center justify-center shadow-[0_0_50px_rgba(185,28,28,0.4)] transition-transform active:scale-95">
                    <svg class="w-16 h-16 sm:w-20 sm:h-20 mb-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <span class="font-unbounded text-sm sm:text-base text-red-100 font-bold mt-2 tracking-widest">DARURAT</span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 mb-6 fade-in">
            @foreach([
                [route('trusted-contact.index'), '👤', 'Kontak Darurat', 'Orang terpercaya'],
                [route('report.create'),         '🛡️', 'Buat Laporan',    'Laporan kejadian masa lalu'],
                ['/evidence',                   '🗂️', 'Galeri Bukti',  'Aman tersimpan'],
                [route('chat.threads'),         '💬', 'Chat', 'Riwayat chat'],
            ] as [$url, $icon, $title, $sub])
            <a href="{{ $url }}" class="bg-white border border-gray-100 hover:border-gray-200 rounded-2xl p-4 flex flex-col items-center text-center transition group shadow-sm active:scale-95">
                <div class="w-13 h-13 rounded-full flex items-center justify-center text-xl mb-3 group-hover:scale-110 transition-transform">
                    {{ $icon }}
                </div>
                <p class="font-bold text-gray-900 text-sm mb-0.5">{{ $title }}</p>
                <p class="text-[11px] text-gray-500">{{ $sub }}</p>
            </a>
            @endforeach
        </div>

        <div class="grid lg:grid-cols-2 gap-4 sm:gap-6 fade-in">

            {{-- ===== LEFT COLUMN ===== --}}
            <div class="space-y-6">

                {{-- Map Mitra (Style Card berurut jarak) --}}
                <div class="bg-white border border-gray-100 rounded-2xl p-4 sm:p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="font-semibold text-gray-900">Mitra Terdekat</h2>
                            <p class="text-gray-400 text-xs mt-0.5">Urut berdasarkan jarak dari lokasi kamu.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" id="btn-view-all-mitras" aria-label="Lihat semua mitra" title="Lihat semua mitra"
                                class="hidden sm:inline-flex text-xs font-semibold text-gray-700 hover:text-gray-900 bg-gray-50 hover:bg-gray-100 px-3 py-1.5 rounded-full transition border border-gray-150">
                                Lihat Semua
                            </button>
                            <button type="button" id="btn-reload-location" aria-label="Reload lokasi" title="Reload lokasi"
                                class="text-xs font-semibold text-gray-700 hover:text-gray-900 bg-gray-50 hover:bg-gray-100 px-3 py-1.5 rounded-full transition border border-gray-150">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div id="nearby-map" class="relative overflow-hidden rounded-xl bg-[#faf9f7] border border-gray-100">
                        <div class="p-3 sm:p-4 relative">
                            {{-- Map canvas --}}
                            <div id="leaflet-map" class="relative w-full h-48 sm:h-64 rounded-xl border border-gray-100 bg-gray-100 overflow-hidden" style="z-index: 1;" aria-label="Peta mitra">
                            </div>

                            <div class="flex items-center gap-2 mb-3 mt-3">
                                <select id="map-search-type" class="w-1/2 min-w-0 border border-gray-200 rounded-2xl px-3 py-2 text-xs bg-white focus:outline-none focus:border-gray-400">
                                    <option value="">Semua</option>
                                    <option value="ambulance">Medis Darurat</option>
                                    <option value="legal">Bantuan Hukum</option>
                                    <option value="counselor">Psikososial</option>
                                    <option value="pemadam">Pemadam / Rescue</option>
                                    <option value="pppa">Layanan PPPA</option>
                                </select>
                                <input id="map-search-query" type="text" placeholder="Cari (cth. lbh semarang)" class="w-1/2 min-w-0 border border-gray-200 rounded-2xl px-3 py-2 text-xs bg-white focus:outline-none focus:border-gray-400">
                            </div>

                            <div id="nearby-mitras" class="space-y-2">
                                <div class="animate-pulse flex items-center justify-between p-3 bg-[#faf9f7] border border-gray-100 rounded-xl">
                                    <div class="flex-1 space-y-2">
                                        <div class="h-4 bg-gray-200 rounded w-2/3"></div>
                                        <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                                    </div>
                                    <div class="w-4 h-4 bg-gray-200 rounded-full"></div>
                                </div>
                                <div class="animate-pulse flex items-center justify-between p-3 bg-[#faf9f7] border border-gray-100 rounded-xl">
                                    <div class="flex-1 space-y-2">
                                        <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                                        <div class="h-3 bg-gray-200 rounded w-1/3"></div>
                                    </div>
                                    <div class="w-4 h-4 bg-gray-200 rounded-full"></div>
                                </div>
                                <div class="animate-pulse flex items-center justify-between p-3 bg-[#faf9f7] border border-gray-100 rounded-xl">
                                    <div class="flex-1 space-y-2">
                                        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                                        <div class="h-3 bg-gray-200 rounded w-2/5"></div>
                                    </div>
                                    <div class="w-4 h-4 bg-gray-200 rounded-full"></div>
                                </div>
                                <div class="animate-pulse flex items-center justify-between p-3 bg-[#faf9f7] border border-gray-100 rounded-xl">
                                    <div class="flex-1 space-y-2">
                                        <div class="h-4 bg-gray-200 rounded w-3/5"></div>
                                        <div class="h-3 bg-gray-200 rounded w-1/4"></div>
                                    </div>
                                    <div class="w-4 h-4 bg-gray-200 rounded-full"></div>
                                </div>
                            </div>

                            <button type="button" onclick="openAllMitrasModal()" class="w-full mt-3 sm:hidden text-xs font-semibold text-gray-700 hover:text-gray-900 bg-gray-50 hover:bg-gray-100 py-2.5 rounded-xl transition border border-gray-150 text-center block">
                                Lihat Semua Mitra
                            </button>

                        </div>
                    </div>

                </div>
            </div>

            {{-- ===== RIGHT COLUMN ===== --}}
            <div class="space-y-6">
                {{-- Laporan Saya --}}
                <div class="bg-white border border-gray-100 rounded-2xl p-4 sm:p-6 shadow-sm">
                    <div class="flex items-center justify-between flex-wrap gap-3 mb-5">
                        <div>
                            <h2 class="font-semibold text-gray-900">Riwayat Laporan</h2>
                            <p id="total-reports-text" class="text-gray-400 text-xs mt-0.5">Memuat laporan...</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" id="btn-view-all-reports" onclick="openAllReportsModal()" class="hidden sm:inline-flex text-xs font-semibold text-gray-700 hover:text-gray-900 bg-gray-50 hover:bg-gray-100 px-3 py-1.5 rounded-full transition border border-gray-150">
                                Lihat Semua
                            </button>
                            <a href="{{ route('report.create') }}" class="text-xs font-semibold text-red-700 hover:text-red-800 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-full transition border border-red-100 whitespace-nowrap">+ Laporan Baru</a>
                        </div>
                    </div>

                    {{-- Pencarian Laporan --}}
                    <div class="mb-5">
                        <form action="/tracking-search" method="GET" class="flex gap-2">
                            <div class="relative flex-1 min-w-0">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </span>
                                <input type="text" name="id" placeholder="Cari ID / Kode Laporan (cth. e439d57a)..." class="w-full border border-gray-200 focus:border-gray-400 rounded-xl pl-9 pr-4 py-2.5 text-xs focus:outline-none transition bg-white min-w-0" required>
                            </div>
                            <button type="submit" class="text-xs font-semibold text-gray-700 hover:text-gray-900 bg-gray-50 hover:bg-gray-100 px-4 py-2.5 rounded-xl transition border border-gray-150 flex items-center justify-center shadow-sm flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </button>
                        </form>
                    </div>

                    <div id="reports-skeleton" class="space-y-3">
                        <div class="animate-pulse flex items-center justify-between p-6 bg-[#faf9f7] rounded-xl border border-gray-100 gap-4">
                            <div class="flex-1 space-y-2">
                                <div class="h-4 bg-gray-200 rounded w-1/3"></div>
                                <div class="h-3 bg-gray-200 rounded w-1/4"></div>
                            </div>
                            <div class="h-6 bg-gray-200 rounded-full w-20"></div>
                        </div>
                        <div class="animate-pulse flex items-center justify-between p-6 bg-[#faf9f7] rounded-xl border border-gray-100 gap-4">
                            <div class="flex-1 space-y-2">
                                <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                                <div class="h-3 bg-gray-200 rounded w-1/3"></div>
                            </div>
                            <div class="h-6 bg-gray-200 rounded-full w-16"></div>
                        </div>
                        <div class="animate-pulse flex items-center justify-between p-6 bg-[#faf9f7] rounded-xl border border-gray-100 gap-4">
                            <div class="flex-1 space-y-2">
                                <div class="h-4 bg-gray-200 rounded w-1/4"></div>
                                <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                            </div>
                            <div class="h-6 bg-gray-200 rounded-full w-24"></div>
                        </div>
                    </div>

                    <div id="reports-list" class="space-y-3 hidden"></div>

                    <div id="reports-empty" class="text-center py-10 hidden">
                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <p class="text-gray-400 text-sm font-medium">Belum ada laporan</p>
                        <p class="text-gray-300 text-xs mt-1">Laporan kamu akan muncul di sini</p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ===== KATEGORI MODAL ===== --}}
    <div id="all-mitras-modal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 items-center justify-center px-4">
        <div class="bg-white rounded-2xl w-full max-w-lg p-5 shadow-2xl max-h-[85vh] overflow-hidden flex flex-col">
            <div class="flex items-start justify-between gap-3 mb-3">
                <div class="min-w-0">
                    <h2 class="font-black text-xl text-gray-900 mb-1 font-unbounded leading-tight">Mitra</h2>
                </div>
                <button type="button" onclick="closeAllMitrasModal()" class="text-gray-400 hover:text-gray-600 transition p-2 rounded-full">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="flex items-center gap-2 mb-3">
                <select id="all-mitras-type" class="w-1/2 border border-gray-200 rounded-2xl px-3 py-2 text-xs bg-white focus:outline-none focus:border-gray-400">
                    <option value="">Semua</option>
                    <option value="ambulance">Medis Darurat</option>
                    <option value="legal">Bantuan Hukum</option>
                    <option value="counselor">Psikososial</option>
                    <option value="pemadam">Pemadam / Rescue</option>
                    <option value="pppa">Layanan PPPA</option>
                </select>
                <input id="all-mitras-query" type="text" placeholder="Cari (cth. lbh semarang)" class="w-1/2 border border-gray-200 rounded-2xl px-3 py-2 text-xs bg-white focus:outline-none focus:border-gray-400">
            </div>

            <div id="all-mitras-scroll-container" class="flex-1 overflow-auto" onscroll="handleAllMitrasScroll()">
                <div id="all-mitras-list" class="space-y-2">
                    <div class="text-sm text-gray-400">Memuat mitra...</div>
                </div>
            </div>

            <div class="mt-3">
                <button type="button" onclick="closeAllMitrasModal()" class="w-full bg-gray-900 hover:bg-gray-700 text-white py-3 rounded-xl font-semibold text-sm transition">Tutup</button>
            </div>
        </div>
    </div>

    {{-- ===== ALL REPORTS MODAL ===== --}}
    <div id="all-reports-modal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 items-center justify-center px-4">
        <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl max-h-[90vh] overflow-hidden flex flex-col">
            {{-- Header --}}
            <div class="flex items-start justify-between gap-3 p-5 border-b border-gray-100">
                <div class="min-w-0">
                    <h2 class="font-black text-xl text-gray-900 font-unbounded leading-tight">Riwayat Lengkap</h2>
                    <p id="all-reports-subtitle" class="text-gray-400 text-xs mt-0.5">Menampilkan semua laporan kamu.</p>
                </div>
                <button type="button" onclick="closeAllReportsModal()" class="text-gray-400 hover:text-gray-600 transition p-2 rounded-full flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Filters --}}
            <div class="p-4 border-b border-gray-100 space-y-2">
                <div class="flex gap-2">
                    <select id="all-reports-category" class="flex-1 min-w-0 border border-gray-200 rounded-xl px-3 py-2 text-xs bg-white focus:outline-none focus:border-gray-400">
                        <option value="">Semua Kategori</option>
                        <option value="ambulance">Medis Darurat</option>
                        <option value="legal">Bantuan Hukum</option>
                        <option value="counselor">Psikososial</option>
                        <option value="pemadam">Pemadam / Rescue</option>
                        <option value="pppa">Layanan PPPA</option>
                    </select>
                    <select id="all-reports-status" class="flex-1 min-w-0 border border-gray-200 rounded-xl px-3 py-2 text-xs bg-white focus:outline-none focus:border-gray-400">
                        <option value="">Semua Status</option>
                        <option value="Submitted">Submitted</option>
                        <option value="Routed">Routed</option>
                        <option value="Viewed">Viewed</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Resolved">Resolved</option>
                    </select>
                </div>
                <div class="flex gap-2 items-center">
                    <div class="relative flex-1 min-w-0">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </span>
                        <input id="all-reports-start" type="date" class="w-full border border-gray-200 rounded-xl pl-8 pr-3 py-2 text-xs bg-white focus:outline-none focus:border-gray-400 min-w-0" placeholder="Dari tanggal">
                    </div>
                    <span class="text-gray-400 text-xs flex-shrink-0">s/d</span>
                    <div class="relative flex-1 min-w-0">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </span>
                        <input id="all-reports-end" type="date" class="w-full border border-gray-200 rounded-xl pl-8 pr-3 py-2 text-xs bg-white focus:outline-none focus:border-gray-400 min-w-0" placeholder="Sampai tanggal">
                    </div>
                </div>
            </div>

            {{-- List --}}
            <div id="all-reports-scroll-container" class="flex-1 overflow-auto p-4" onscroll="handleAllReportsScroll()">
                <div id="all-reports-list" class="space-y-2">
                    <div class="text-sm text-gray-400">Memuat laporan...</div>
                </div>
                <div id="all-reports-loader" class="hidden text-center py-3">
                    <div class="inline-block w-5 h-5 border-2 border-gray-300 border-t-red-600 rounded-full animate-spin"></div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="p-4 border-t border-gray-100">
                <button type="button" onclick="closeAllReportsModal()" class="w-full bg-gray-900 hover:bg-gray-700 text-white py-3 rounded-xl font-semibold text-sm transition">Tutup</button>
            </div>
        </div>
    </div>

    {{-- EMERGENCY MODAL --}}
    <div id="emergency-modal" class="fixed inset-0 z-[999] items-end sm:items-center justify-center bg-black/60 backdrop-blur-sm hidden">
        <div class="modal-in bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-md shadow-2xl overflow-hidden">

            {{-- STEP 1: Pilih Kategori --}}
            <div id="step-category">
                <div class="px-6 pt-6 pb-4 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-widest text-red-600 mb-0.5">Mode Darurat Safora</p>
                            <h2 class="text-xl font-black text-gray-900">Apa yang terjadi?</h2>
                        </div>
                        <button onclick="closeEmergencyModal()" class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 transition text-gray-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <p class="text-gray-400 text-xs mt-1">Pilih kategori</p>
                </div>
                <div class="grid grid-cols-2 gap-2.5 p-5">
                    @foreach(['Kekerasan','Medis & Kecelakaan','Pelecehan & Bullying','Kebakaran & Penyelamatan','Krisis Mental','Hukum & Keamanan','Lainnya'] as $cat)
                    <button onclick="selectCategory('{{ $cat }}', this)"
                        class="category-btn flex flex-col items-start gap-1 bg-gray-50 hover:bg-red-50 border border-gray-200 hover:border-red-300 rounded-2xl px-4 py-4 text-left transition-all group">
                        <span class="category-name font-bold text-gray-900 text-sm group-hover:text-red-700 transition">{{ $cat }}</span>
                    </button>
                    @endforeach
                </div>
                <div class="px-5 pb-2.5">
                    <button type="button" id="btn-next-step" onclick="goToSendingStep()" disabled class="w-full bg-gray-300 text-white py-3 rounded-xl font-semibold text-sm transition opacity-50 cursor-not-allowed">
                        Lanjut
                    </button>
                </div>
                <div class="px-5 pb-5">
                    <p class="text-center text-xs text-gray-400">Jika nyawa terancam, hubungi langsung:
                        <a href="tel:112" class="text-red-700 font-bold">112</a> ·
                        <a href="tel:119" class="text-red-700 font-bold">119</a> ·
                        <a href="tel:110" class="text-red-700 font-bold">110</a>
                    </p>
                </div>
            </div>

            {{-- STEP 2: Kirim --}}
            <div id="step-sending" class="hidden">
                <div class="px-6 pt-6 pb-4 border-b border-gray-100">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-red-600 mb-0.5">Mengirim laporan...</p>
                        <h2 class="text-xl font-black text-gray-900" id="modal-category-label">Kategori</h2>
                    </div>
                </div>
                <div class="px-6 py-5 text-center">
                    <div class="w-10 h-10 border-4 border-red-200 border-t-red-600 rounded-full animate-spin mx-auto mb-3"></div>
                    <p id="modal-status" class="text-gray-500 text-sm">Mengambil lokasi GPS...</p>
                    <button onclick="cancelEmergency()" class="mt-4 text-sm text-gray-400 hover:text-gray-600 underline transition">Batalkan</button>
                </div>
            </div>

        </div>
    </div>



    {{-- ===== EDIT LAPORAN MODAL ===== --}}
    <div id="edit-report-modal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-[110] items-center justify-center px-4 transition-all duration-300">
        <div class="bg-white rounded-2xl w-full max-w-lg p-6 shadow-2xl">
            <div class="flex items-start justify-between gap-3 mb-4">
                <div>
                    <h2 class="font-black text-xl text-gray-900 mb-1 font-unbounded">Edit Laporan</h2>
                    <p class="text-gray-500 text-xs">Perbarui laporan. Sistem otomatis mengirim ulang notifikasi ke mitra.</p>
                </div>
                <button type="button" onclick="closeEditReportModal()" class="text-gray-400 hover:text-gray-600 transition p-2 rounded-full">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form id="edit-report-form" method="POST" action="">
                @csrf
                @method('PATCH')
                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-900 mb-1">Kategori Laporan</label>
                    <select name="category" id="edit-category" class="w-full border border-gray-200 bg-gray-50 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-red-500 focus:bg-white" required>
                        <option value="Kekerasan">Kekerasan</option>
                        <option value="Medis & Kecelakaan">Medis & Kecelakaan</option>
                        <option value="Pelecehan & Bullying">Pelecehan & Bullying</option>
                        <option value="Kebakaran & Penyelamatan">Kebakaran & Penyelamatan</option>
                        <option value="Krisis Mental">Krisis Mental</option>
                        <option value="Hukum & Keamanan">Hukum & Keamanan</option>
                        <option value="Salah Tangkap">Salah Tangkap / Kriminalisasi</option>
                        <option value="Konseling & Trauma">Konseling & Trauma</option>
                        <option value="Sosial">Sosial / Anak Terlantar</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                
                <div class="mb-5">
                    <label class="block text-sm font-bold text-gray-900 mb-1">Deskripsi Kejadian</label>
                    <textarea name="description" id="edit-description" rows="3" class="w-full border border-gray-200 bg-gray-50 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-red-500 focus:bg-white resize-none" placeholder="Deskripsi kejadian..."></textarea>
                </div>
                
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeEditReportModal()" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold text-sm transition">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl font-semibold text-sm transition shadow-sm">Simpan & Kirim Ulang</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    const csrf = '{{ csrf_token() }}';

    function formatDate(dateStr) {
        if (!dateStr) return '-';
        try {
            const cleanStr = String(dateStr).replace(' ', 'T');
            const d = new Date(cleanStr);
            if (isNaN(d.getTime())) {
                const parts = String(dateStr).split(/[-T :.]/);
                if (parts.length >= 3) {
                    const year = parseInt(parts[0], 10);
                    const month = parseInt(parts[1], 10) - 1;
                    const day = parseInt(parts[2], 10);
                    const hour = parts[3] ? parseInt(parts[3], 10) : 0;
                    const minute = parts[4] ? parseInt(parts[4], 10) : 0;
                    const second = parts[5] ? parseInt(parts[5], 10) : 0;
                    const parsedDate = new Date(year, month, day, hour, minute, second);
                    if (!isNaN(parsedDate.getTime())) {
                        return formatLocalDate(parsedDate);
                    }
                }
                return String(dateStr);
            }
            return formatLocalDate(d);
        } catch (e) {
            return String(dateStr);
        }
    }

    function formatLocalDate(d) {
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const day = String(d.getDate()).padStart(2, '0');
        const month = months[d.getMonth()];
        const year = d.getFullYear();
        const hour = String(d.getHours()).padStart(2, '0');
        const minute = String(d.getMinutes()).padStart(2, '0');
        return `${day} ${month} ${year} ${hour}:${minute}`;
    }

    function dismissDashboardHelper() {
        const card = document.getElementById('dashboard-helper-guide');
        if (card) {
            card.classList.add('opacity-0', 'max-h-0', 'py-0', 'my-0', 'overflow-hidden');
            setTimeout(() => {
                card.remove();
            }, 500);
        }
        localStorage.setItem('safora_dashboard_guide_dismissed', '1');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const guideDismissed = localStorage.getItem('safora_dashboard_guide_dismissed');
        const card = document.getElementById('dashboard-helper-guide');
        if (!guideDismissed && card) {
            card.classList.remove('hidden');
        }
    });
    // Indonesian UI Mappers
    function getMitraTypeLabel(type) {
        return {
            ambulance: 'Medis Darurat',
            legal: 'Bantuan Hukum',
            counselor: 'Psikososial',
            pemadam: 'Pemadam / Rescue',
            pppa: 'Layanan PPPA'
        }[type] || type;
    }

    const _arStatusNames = {
        'Submitted': 'Diajukan',
        'Routed': 'Diteruskan',
        'Viewed': 'Ditinjau',
        'Assigned': 'Diterima',
        'In Progress': 'Diproses',
        'Resolved': 'Selesai',
        'Rejected': 'Ditolak'
    };

    let cdInterval=null, lat=null, lng=null;
    let map = null;
    let userMarker = null;
    let mitraMarkers = [];
    let watchId = null;

    function initMap(initialLat, initialLng) {
        if (!map) {
            map = L.map('leaflet-map').setView([initialLat, initialLng], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            }).addTo(map);

            const userIcon = L.divIcon({
                className: 'custom-user-marker',
                html: `<div class="relative">
                            <div class="w-4 h-4 rounded-full bg-blue-600 border-2 border-white shadow-md z-10 relative"></div>
                            <div class="w-10 h-10 rounded-full bg-blue-500/30 absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 animate-ping" style="animation-duration: 2s;"></div>
                       </div>`,
                iconSize: [16, 16],
                iconAnchor: [8, 8]
            });

            userMarker = L.marker([initialLat, initialLng], {
                icon: userIcon,
                interactive: false,
                keyboard: false,
                zIndexOffset: 0
            }).addTo(map);
            
            map.on('dragstart', () => { window.__mapUserPanned = true; });

            // Muat ulang marker jika marker sudah dimuat sebelum peta diinisialisasi
            if (window.__lastNearbyItems) {
                renderMapMarkers(window.__lastNearbyItems);
            }
        } else {
            userMarker.setLatLng([initialLat, initialLng]);
            if (!window.__mapUserPanned) {
                map.panTo([initialLat, initialLng]);
            }
        }
    }

    function openAllMitrasModal(){
        const m = document.getElementById('all-mitras-modal');
        if(!m) return;
        document.body.style.overflow = 'hidden';
        m.classList.remove('hidden');
        m.classList.add('flex');

        const mapType = document.getElementById('map-search-type')?.value || '';
        const mapQuery = document.getElementById('map-search-query')?.value || '';
        
        const allType = document.getElementById('all-mitras-type');
        const allQuery = document.getElementById('all-mitras-query');
        if(allType) allType.value = mapType;
        if(allQuery) allQuery.value = mapQuery;

        const items = (window.__lastNearbyItems || []).slice(0, 20);
        renderAllMitras(items);
        fetchAllMitras(1, false);
    }

    function closeAllMitrasModal(){
        const m = document.getElementById('all-mitras-modal');
        if(!m) return;
        m.classList.remove('flex');
        m.classList.add('hidden');
        document.body.style.overflow = '';
    }

    if(navigator.geolocation){
        watchId = navigator.geolocation.watchPosition(
            p=>{
                lat=p.coords.latitude;
                lng=p.coords.longitude;
                const latEl = document.getElementById('f-lat');
                const lngEl = document.getElementById('f-lng');
                if(latEl) latEl.value=lat;
                if(lngEl) lngEl.value=lng;
                
                const locInfoEl = document.getElementById('loc-info');
                if(locInfoEl) {
                    locInfoEl.innerHTML='<div class="w-2 h-2 bg-green-500 rounded-full"></div><span class="text-green-600">Lokasi: '+lat.toFixed(4)+', '+lng.toFixed(4)+'</span>';
                }
                
                initMap(lat, lng);

                @if(isset($userHasLocation) && !$userHasLocation)
                    if (!window.__hasSavedInitialLocation) {
                        window.__hasSavedInitialLocation = true;
                        fetch('/user-location/reload', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ latitude: lat, longitude: lng })
                        }).then(() => {
                            loadNearbyMitras({
                                type: document.getElementById('map-search-type')?.value || '',
                                query: document.getElementById('map-search-query')?.value || ''
                            });
                        }).catch(console.error);
                    }
                @endif
            },
            ()=>{
                const locInfoEl = document.getElementById('loc-info');
                if(locInfoEl) {
                    locInfoEl.innerHTML='<div class="w-2 h-2 bg-red-400 rounded-full"></div><span class="text-red-500">GPS tidak tersedia.</span>';
                }
            },
            { enableHighAccuracy: true, maximumAge: 10000, timeout: 10000 }
        );
    }



    let emergencyMarkers = [];

    function mitraMarkerVisual(type) {
        const normalized = String(type || '').toLowerCase();
        let key = 'default';
        if (normalized.includes('ambulance') || normalized.includes('ambulans')) {
            key = 'ambulance';
        } else if (normalized.includes('legal') || normalized.includes('lbh') || normalized.includes('pengacara')) {
            key = 'legal';
        } else if (normalized.includes('counselor') || normalized.includes('konselor') || normalized.includes('psikolog')) {
            key = 'counselor';
        } else if (normalized.includes('pemadam') || normalized.includes('fire') || normalized.includes('rescue')) {
            key = 'pemadam';
        } else if (normalized.includes('pppa')) {
            key = 'pppa';
        }

        const visuals = {
            ambulance: {
                bg: 'bg-red-600',
                svg: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17h6m-3-3v6M4 16V8a2 2 0 0 1 2-2h8v10H4Zm10-7h3l3 3v4h-6V9ZM7 18.5h.01M17 18.5h.01"/></svg>'
            },
            legal: {
                bg: 'bg-slate-700',
                svg: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v18M6 7h12M6 7l-3 6h6L6 7Zm12 0-3 6h6l-3-6ZM8 21h8"/></svg>'
            },
            counselor: {
                bg: 'bg-emerald-600',
                svg: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21s-7-4.35-7-11a4 4 0 0 1 7-2.65A4 4 0 0 1 19 10c0 6.65-7 11-7 11Z"/></svg>'
            },
            pemadam: {
                bg: 'bg-orange-600',
                svg: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 22a6 6 0 0 0 6-6c0-3.5-2.5-5.5-4.5-8.5-.5 2-2 3-3.5 4.5.2-2.5-.8-4.5-2-6C6.5 9.5 6 12 6 16a6 6 0 0 0 6 6Z"/></svg>'
            },
            pppa: {
                bg: 'bg-purple-600',
                svg: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>'
            },
            default: {
                bg: 'bg-gray-700',
                svg: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6M9 10h.01M15 10h.01"/></svg>'
            }
        };

        return visuals[key];
    }

    function renderMapMarkers(items, emergencies = []) {
        if (!map) return;
        
        mitraMarkers.forEach(m => map.removeLayer(m));
        mitraMarkers = [];

        emergencyMarkers.forEach(m => map.removeLayer(m));
        emergencyMarkers = [];
        
        const bounds = [];
        if (lat && lng) bounds.push([lat, lng]);

        items.forEach((x, i) => {
            const p = x.mitra;
            const km = Number(x.distance_km) || 0;
            if (p.latitude && p.longitude) {
                if (km <= 30) {
                    bounds.push([p.latitude, p.longitude]);
                }
                const visual = mitraMarkerVisual(p.mitra_type);
                
                const mitraIcon = L.divIcon({
                    className: 'custom-mitra-marker',
                    html: `<div class="w-7 h-7 rounded-full ${visual.bg} border-2 border-white shadow-md flex items-center justify-center text-white relative z-20">${visual.svg}</div>`,
                    iconSize: [28, 28],
                    iconAnchor: [14, 14]
                });

                const popupHtml = `
                    <div class="text-xs p-1">
                        <div class="font-bold">${String(p.mitra_name).replace(/</g,'&lt;')}</div>
                        <div class="text-gray-500">${getMitraTypeLabel(p.mitra_type)} • ${km.toFixed(2)} km</div>
                        <a href="/data-mitra/${p.id}" class="text-blue-600 hover:underline mt-1 block">Lihat Detail &rarr;</a>
                    </div>
                `;

                const m = L.marker([p.latitude, p.longitude], {icon: mitraIcon})
                    .bindPopup(popupHtml)
                    .addTo(map);
                mitraMarkers.push(m);
            }
        });

        emergencies.forEach((e) => {
            const km = Number(e.distance_km) || 0;
            if (e.latitude && e.longitude) {
                if (km <= 30) {
                    bounds.push([e.latitude, e.longitude]);
                }

                const emergencyIcon = L.divIcon({
                    className: 'custom-emergency-marker',
                    html: `<div class="w-6 h-6 rounded-full bg-orange-500 border-2 border-white shadow-md flex items-center justify-center text-white relative z-[9999] animate-pulse">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                           </div>`,
                    iconSize: [24, 24],
                    iconAnchor: [12, 12]
                });

                const popupHtml = `
                    <div class="text-xs p-1">
                        <div class="font-bold text-orange-600">Darurat: ${e.category}</div>
                        <div class="text-gray-500">Korban: ${e.victim_name} • ${Number(e.distance_km).toFixed(2)} km</div>
                        <a href="/tracking/${e.id}" class="text-blue-600 hover:underline mt-1 block">Lacak Laporan &rarr;</a>
                    </div>
                `;

                const m = L.marker([e.latitude, e.longitude], {
                        icon: emergencyIcon,
                        riseOnHover: true,
                        zIndexOffset: 2000
                    })
                    .bindPopup(popupHtml)
                    .addTo(map);
                emergencyMarkers.push(m);
            }
        });
        
        if (bounds.length > 1 && !window.__mapUserPanned) {
            map.fitBounds(bounds, { padding: [30, 30], maxZoom: 15 });
        } else if (lat && lng && !window.__mapUserPanned) {
            map.setView([lat, lng], 11);
        }
    }

    // Load mitra gabungan (ambulans + LBH + psikolog + dll) + marker + search
    async function loadNearbyMitras({type = '', query = ''} = {}){
        const el = document.getElementById('nearby-mitras');

        if(!el) return;

        try {
            el.innerHTML = `
                <div class="animate-pulse flex items-center justify-between p-3 bg-white border border-gray-100 rounded-xl">
                    <div class="flex-1 space-y-2">
                        <div class="h-4 bg-gray-200 rounded w-2/3"></div>
                        <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                    </div>
                    <div class="w-4 h-4 bg-gray-200 rounded-full"></div>
                </div>
                <div class="animate-pulse flex items-center justify-between p-3 bg-white border border-gray-100 rounded-xl">
                    <div class="flex-1 space-y-2">
                        <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                        <div class="h-3 bg-gray-200 rounded w-1/3"></div>
                    </div>
                    <div class="w-4 h-4 bg-gray-200 rounded-full"></div>
                </div>
                <div class="animate-pulse flex items-center justify-between p-3 bg-white border border-gray-100 rounded-xl">
                    <div class="flex-1 space-y-2">
                        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                        <div class="h-3 bg-gray-200 rounded w-2/5"></div>
                    </div>
                    <div class="w-4 h-4 bg-gray-200 rounded-full"></div>
                </div>
                <div class="animate-pulse flex items-center justify-between p-3 bg-white border border-gray-100 rounded-xl">
                    <div class="flex-1 space-y-2">
                        <div class="h-4 bg-gray-200 rounded w-3/5"></div>
                        <div class="h-3 bg-gray-200 rounded w-1/4"></div>
                    </div>
                    <div class="w-4 h-4 bg-gray-200 rounded-full"></div>
                </div>
            `;

            const params = new URLSearchParams();
            if(type) params.set('type', type);
            if(query) params.set('query', query);

            const res = await fetch(`/map-search?${params.toString()}`, { headers: { 'Accept':'application/json' } });
            if(!res.ok) throw new Error('HTTP '+res.status);

            const json = await res.json();
            const items = json.data || [];
            const emergencies = json.emergencies || [];

            if(items.length===0){
                el.innerHTML = '<div class="text-sm text-gray-400">Belum ada mitra yang cocok.</div>';
                if (map) {
                    mitraMarkers.forEach(m => map.removeLayer(m));
                    mitraMarkers = [];
                    emergencyMarkers.forEach(m => map.removeLayer(m));
                    emergencyMarkers = [];
                    
                    if (emergencies.length > 0) {
                        renderMapMarkers([], emergencies);
                    }
                }
                return;
            }

            // Cache full result untuk dipakai fitur “Lihat Semua”
            window.__lastNearbyItems = items;
            window.__lastNearbyEmergencies = emergencies;
            
            if (map) {
                renderMapMarkers(items, emergencies);
            }

            // Di bawah map hanya tampilkan 4 mitra terdekat (preview)
            const previewTop = items.slice(0,4);

            el.innerHTML = previewTop.map((x,i)=>{
                const p = x.mitra;
                return `
                    <a href="/data-mitra/${p.id}" class="block bg-white border border-gray-100 rounded-xl p-3 hover:bg-gray-50 transition group">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <p class="font-bold text-gray-900 text-sm truncate">${p.mitra_name}</p>
                                <p class="text-xs text-gray-500 mt-1">${getMitraTypeLabel(p.mitra_type)} • ${Number(x.distance_km).toFixed(2)} km</p>
                            </div>
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-gray-500 transition shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </a>
                `;
            }).join('');


        }catch(e){
            const el = document.getElementById('nearby-mitras');
            if(el){
                el.innerHTML = `<div class="text-sm text-red-700 bg-red-50 border border-red-200 rounded-xl px-3 py-2">Gagal memuat mitra: ${String(e && e.message ? e.message : e)}</div>`;
            }
        }
    }


    // Filter map (type + query) -> refresh marker & list
    const mapTypeEl = document.getElementById('map-search-type');
    const mapQueryEl = document.getElementById('map-search-query');

    function triggerMapSearch(){
        const t = mapTypeEl?.value || '';
        const q = mapQueryEl?.value || '';
        loadNearbyMitras({ type: t, query: q });

        // jika modal sedang terbuka, ikut refresh juga
        const allModal = document.getElementById('all-mitras-modal');
        if(allModal && !allModal.classList.contains('hidden')){
            const items = window.__lastNearbyItems || [];
            renderAllMitras(items);
        }
    }


    if(mapTypeEl){
        mapTypeEl.addEventListener('change', ()=>triggerMapSearch());
    }
    if(mapQueryEl){
        let mapDebounceTimer = null;
        mapQueryEl.addEventListener('input', ()=>{
            if(mapDebounceTimer) clearTimeout(mapDebounceTimer);
            mapDebounceTimer = setTimeout(()=>triggerMapSearch(), 250);
        });
    }

    async function fetchDashboardSummary() {
        const skeleton = document.getElementById('reports-skeleton');
        const list = document.getElementById('reports-list');
        const empty = document.getElementById('reports-empty');
        const totalText = document.getElementById('total-reports-text');
        const viewAllBtn = document.getElementById('btn-view-all-reports');

        try {
            const res = await fetch('/dashboard/summary-data', { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const json = await res.json();

            // 1. Update total text
            if (totalText) {
                totalText.textContent = json.totalReports + ' laporan tercatat';
            }

            // 2. Perbarui visibilitas tombol lihat semua
            if (viewAllBtn) {
                if (json.totalReports > 7) {
                    viewAllBtn.classList.remove('hidden');
                } else {
                    viewAllBtn.classList.add('hidden');
                }
            }

            // 3. Render reports list
            if (json.reports && json.reports.length > 0) {
                let html = json.reports.map(r => {
                    const anonBadge = r.anonymous ? `<span class="text-[10px] bg-purple-50 text-purple-700 px-2 py-0.5 rounded-full font-semibold">Anonim</span>` : '';
                    const evBadge = r.evidences_count > 0 ? `<span class="text-[10px] bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full font-semibold">${r.evidences_count} bukti</span>` : '';
                    
                    let actionButtons = '';
                    if (r.is_editable_deletable) {
                        actionButtons = `
                            <div class="items-center gap-1.5 report-action-buttons hidden sm:flex" data-time="${r.created_at_timestamp}">
                                <button type="button" data-id="${r.id}" data-category="${r.category}" data-desc="${r.description || ''}" onclick="event.preventDefault(); event.stopPropagation(); openEditReportModal(this)" class="text-xs bg-white hover:bg-gray-100 text-gray-700 px-2.5 py-1.5 rounded-lg border border-gray-200 transition font-medium">Edit</button>
                                <form action="/report/${r.id}" method="POST" data-confirm="Apakah Anda yakin ingin menghapus laporan ini? Tindakan ini tidak dapat dibatalkan." onclick="event.stopPropagation();" class="inline">
                                    <input type="hidden" name="_token" value="${csrf}">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="text-xs bg-white hover:bg-red-50 text-red-600 px-2.5 py-1.5 rounded-lg border border-gray-200 transition font-medium">Hapus</button>
                                </form>
                            </div>
                        `;
                    }

                    return `
                        <a href="/tracking/${r.id}" class="flex items-center justify-between p-4 sm:p-5 bg-[#faf9f7] hover:bg-gray-50 rounded-xl border border-gray-100 hover:border-gray-200 transition group gap-3">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="font-semibold text-gray-900 text-sm truncate">${r.category}</p>
                                    ${anonBadge}
                                    ${evBadge}
                                </div>
                                <p class="text-gray-400 text-xs mt-1">${r.incident_date}</p>
                            </div>
                            <div class="flex items-center gap-3 flex-shrink-0">
                                ${actionButtons}
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-full ${r.status_classes.bg} ${r.status_classes.text} flex items-center gap-1.5 whitespace-nowrap">
                                    <span class="w-1.5 h-1.5 rounded-full ${r.status_classes.dot}"></span>
                                    ${r.status_label}
                                </span>
                                <svg class="w-4 h-4 text-gray-300 group-hover:text-gray-500 transition shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </div>
                        </a>
                    `;
                }).join('');

                if (json.totalReports > 7) {
                    html += `
                        <button type="button" onclick="openAllReportsModal()" class="w-full mt-3 sm:hidden text-xs font-semibold text-gray-700 hover:text-gray-900 bg-gray-50 hover:bg-gray-100 py-2.5 rounded-xl transition border border-gray-150 text-center block">
                            Lihat ${json.totalReports - 7} laporan lainnya →
                        </button>
                    `;
                }

                if (list) {
                    list.innerHTML = html;
                    list.classList.remove('hidden');
                }
                if (skeleton) skeleton.classList.add('hidden');
                if (empty) empty.classList.add('hidden');

                // Recalculate edit/delete button timer hide
                checkReportActionTime();
            } else {
                if (skeleton) skeleton.classList.add('hidden');
                if (list) list.classList.add('hidden');
                if (empty) empty.classList.remove('hidden');
            }
        } catch (err) {
            console.error('Gagal memuat ringkasan dashboard:', err);
            if (skeleton) skeleton.classList.add('hidden');
            if (list) {
                list.innerHTML = `<div class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-xl px-3 py-2">Gagal memuat ringkasan: ${err.message}</div>`;
                list.classList.remove('hidden');
            }
        }
    }


    // initial load (semua)
    loadNearbyMitras();
    fetchDashboardSummary();

    // Lihat Semua
    const btnViewAll = document.getElementById('btn-view-all-mitras');
    const allTypeEl = document.getElementById('all-mitras-type');
    const allQueryEl = document.getElementById('all-mitras-query');

    let allMitrasPage = 1;
    let allMitrasHasMore = false;
    let isFetchingAllMitras = false;

    function handleAllMitrasScroll() {
        const container = document.getElementById('all-mitras-scroll-container');
        if(!container) return;
        
        // If scrolled to bottom (within 50px)
        if(container.scrollHeight - container.scrollTop - container.clientHeight < 50) {
            if(allMitrasHasMore && !isFetchingAllMitras) {
                fetchAllMitras(allMitrasPage + 1, true);
            }
        }
    }

    function renderAllMitras(items, append = false){
        const listEl = document.getElementById('all-mitras-list');
        const subtitleEl = document.getElementById('all-mitras-subtitle');
        if(!listEl) return;

        if(!append && (!items || items.length === 0)){
            listEl.innerHTML = '<div class="text-sm text-gray-400">Belum ada mitra yang cocok.</div>';
            if(subtitleEl) subtitleEl.textContent = '0 hasil untuk filter & pencarian saat ini.';
            return;
        }

        const html = items.map((x, i)=>{
            const p = x.mitra;
            const km = Number(x.distance_km) || 0;
            return `
                <a href="/data-mitra/${p.id}" class="block bg-white border border-gray-100 rounded-xl p-3 hover:bg-gray-50 transition group">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-gray-900 text-sm truncate">${p.mitra_name}</p>
                            <p class="text-xs text-gray-500 mt-1">${getMitraTypeLabel(p.mitra_type)} • ${km.toFixed(2)} km</p>
                        </div>
                        <svg class="w-4 h-4 text-gray-300 group-hover:text-gray-500 transition shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>
            `;
        }).join('');

        if (append) {
            listEl.insertAdjacentHTML('beforeend', html);
        } else {
            listEl.innerHTML = html;
        }
    }

    async function fetchAllMitras(page = 1, append = false) {
        if(isFetchingAllMitras) return;
        isFetchingAllMitras = true;
        allMitrasPage = page;
        
        const listEl = document.getElementById('all-mitras-list');
        const t = allTypeEl?.value || '';
        const q = allQueryEl?.value || '';

        if(page === 1) {
            if(listEl) listEl.innerHTML = '<div class="text-sm text-gray-400">Memuat mitra...</div>';
        } else {
            if(listEl) {
                const loadingEl = document.createElement('div');
                loadingEl.id = 'all-mitras-loading-indicator';
                loadingEl.className = 'text-sm text-center text-gray-400 py-3';
                loadingEl.innerHTML = '<div class="animate-pulse">Memuat lebih banyak...</div>';
                listEl.appendChild(loadingEl);
            }
        }

        try{
            const params = new URLSearchParams();
            if(t) params.set('type', t);
            if(q) params.set('query', q);
            params.set('page', page);
            params.set('limit', 20);

            const res = await fetch(`/map-search?${params.toString()}`, { headers: { 'Accept':'application/json' } });
            if(!res.ok) throw new Error('HTTP '+res.status);
            const json = await res.json();
            const items = json.data || [];
            allMitrasHasMore = json.has_more || false;
            
            document.getElementById('all-mitras-loading-indicator')?.remove();
            
            renderAllMitras(items, append);
            
            if(page === 1) window.__lastNearbyItems = items;
        }catch(e){
            document.getElementById('all-mitras-loading-indicator')?.remove();
            if(page === 1 && listEl) {
                listEl.innerHTML = `<div class="text-sm text-red-700 bg-red-50 border border-red-200 rounded-xl px-3 py-2">Gagal memuat mitra: ${String(e && e.message ? e.message : e)}</div>`;
            }
        }finally{
            isFetchingAllMitras = false;
        }
    }

    // open modal sync input ke filter map yang aktif
    btnViewAll && btnViewAll.addEventListener('click', () => {
        openAllMitrasModal();
    });

    // modal search realtime (gunakan API yang sama)
    let allDebounceTimer = null;
    const allTriggerSearch = ()=>{
        if(allDebounceTimer) clearTimeout(allDebounceTimer);
        allDebounceTimer = setTimeout(async ()=>{
            fetchAllMitras(1, false);
            // refresh map juga agar marker & preview sesuai modal
            const t = allTypeEl?.value || '';
            const q = allQueryEl?.value || '';
            loadNearbyMitras({ type: t, query: q });
        }, 250);
    };

    if(allTypeEl) allTypeEl.addEventListener('change', ()=>allTriggerSearch());
    if(allQueryEl) allQueryEl.addEventListener('input', ()=>allTriggerSearch());

    // Tutup jika bagian luar modal (backdrop) diklik
    const allModal = document.getElementById('all-mitras-modal');
    allModal && allModal.addEventListener('click', function(e){
        if(e.target === this) closeAllMitrasModal();
    });


    // Reload lokasi user -> simpan ke backend -> reload mitra
    const reloadBtn = document.getElementById('btn-reload-location');
    if(reloadBtn){
        reloadBtn.addEventListener('click', async () => {
            reloadBtn.disabled = true;
            // Jangan ubah isi tombol (ikon) pakai innerText, karena icon hilang.
            reloadBtn.dataset.originalLabel = reloadBtn.dataset.originalLabel || 'Reload';
            reloadBtn.classList.add('opacity-70');

            // Saat loading: ikon disembunyikan, hanya tampilkan teks.
            const imgEl = reloadBtn.querySelector('svg');
            if(imgEl){ imgEl.style.display = 'none'; }

            let loadingSpan = document.getElementById('reload-loading-span');
            if(!loadingSpan){
                loadingSpan = document.createElement('span');
                loadingSpan.id = 'reload-loading-span';
                loadingSpan.className = 'text-xs font-semibold';
                loadingSpan.textContent = 'Memuat...';
                reloadBtn.appendChild(loadingSpan);
            }
            reloadBtn.setAttribute('data-loading','1');
            reloadBtn.setAttribute('title','Memuat...');

            try{
                if(!navigator.geolocation){
                    throw new Error('Geolocation tidak didukung');
                }

                const pos = await new Promise((resolve, reject) => {
                    navigator.geolocation.getCurrentPosition(resolve, reject, {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    });
                });

                const latitude = pos.coords.latitude;
                const longitude = pos.coords.longitude;

                // update hidden inputs emergency (opsional)
                const latEl = document.getElementById('f-lat');
                const lngEl = document.getElementById('f-lng');
                if(latEl) latEl.value = latitude;
                if(lngEl) lngEl.value = longitude;

                console.log('attempt reload location', { latitude, longitude });

                // Pastikan request benar-benar terkirim, dan jangan silent fallback bila reload lokasi gagal.
                // (Jika lokasi belum tersimpan, fallback loadNearbyMitras akan menampilkan error dari API map-search)
                const res = await fetch('/user-location/reload', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ latitude, longitude })
                });

                // Debug: pastikan response benar-benar ada ok=true
                let reloadJson = null;
                try {
                    reloadJson = await res.json();
                } catch(e) {
                    reloadJson = { parse_error: String(e) };
                }
                console.log('user-location/reload response', reloadJson);

                if(!res.ok) {
                    throw new Error('HTTP ' + res.status + ' body=' + JSON.stringify(reloadJson));
                }

                // Setelah lokasi tersimpan di backend, langsung reload mitra & marker.
                // Menghapus quickCheck `/map-search` supaya 1 klik tidak melakukan request tambahan.


                // Muat ulang daftar/peta mitra
                await loadNearbyMitras({
                    type: mapTypeEl?.value || '',
                    query: mapQueryEl?.value || ''
                });

                // refresh emergency markers juga
                if (window.__loadEmergencyMarkers) {
                    await window.__loadEmergencyMarkers();
                }
            }catch(e){
                const msg = (e && e.message) ? e.message : String(e);
                console.error('Reload lokasi gagal:', msg);

                // tampilkan error agar tidak silent fallback
                const el = document.getElementById('nearby-mitras');
                if(el){
                    el.innerHTML = `<div class="text-sm text-red-700 bg-red-50 border border-red-200 rounded-xl px-3 py-2">Gagal menyimpan lokasi: ${msg}</div>`;
                }

                // fallback tetap mencoba reload mitra (tapi UI error sudah ditampilkan)
                await loadNearbyMitras({
                    type: mapTypeEl?.value || '',
                    query: mapQueryEl?.value || ''
                });

                // refresh emergency markers juga
                if (window.__loadEmergencyMarkers) {
                    await window.__loadEmergencyMarkers();
                }
            }finally{ 
                reloadBtn.disabled = false;
                reloadBtn.classList.remove('opacity-70');
                reloadBtn.removeAttribute('data-loading');
                reloadBtn.setAttribute('title','Reload lokasi');

                // Hilangkan teks loading setelah selesai.
                const loadingSpan = document.getElementById('reload-loading-span');
                if(loadingSpan){ loadingSpan.remove(); }

                // Tampilkan kembali ikon.
                const imgEl = reloadBtn.querySelector('svg');
                if(imgEl){ imgEl.style.display = ''; }


            }
        });
    }

    // Logika Modal Darurat
    const isLoggedIn = true;
    window.hasActiveReport = false;
    let locationPayload = { latitude: null, longitude: null };
    let locationPromise = Promise.resolve();
    let isSubmitting = false;
    let selectedCategory = null;

    function openEmergencyModal() {
        const modal = document.getElementById('emergency-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.getElementById('step-category').classList.remove('hidden');
        document.getElementById('step-sending').classList.add('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeEmergencyModal() {
        cancelEmergency();
        const modal = document.getElementById('emergency-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }

    function selectCategory(cat, element) {
        selectedCategory = cat;

        // Atur ulang semua tombol kategori menjadi tidak aktif
        document.querySelectorAll('.category-btn').forEach(btn => {
            btn.classList.remove('border-red-600', 'bg-red-50', 'text-red-700');
            btn.classList.add('bg-gray-50', 'border-gray-200');
            const nameSpan = btn.querySelector('.category-name');
            if (nameSpan) {
                nameSpan.classList.remove('text-red-700');
                nameSpan.classList.add('text-gray-900');
            }
        });

        // Tandai tombol kategori yang dipilih sebagai aktif
        if (element) {
            element.classList.remove('bg-gray-50', 'border-gray-200');
            element.classList.add('border-red-600', 'bg-red-50', 'text-red-700');
            const nameSpan = element.querySelector('.category-name');
            if (nameSpan) {
                nameSpan.classList.remove('text-gray-900');
                nameSpan.classList.add('text-red-700');
            }
        }

        // Enable next button
        const nextBtn = document.getElementById('btn-next-step');
        if (nextBtn) {
            nextBtn.removeAttribute('disabled');
            nextBtn.classList.remove('bg-gray-300', 'opacity-50', 'cursor-not-allowed');
            nextBtn.classList.add('bg-red-700', 'hover:bg-red-800');
        }
    }

    function goToSendingStep() {
        if (!selectedCategory) return;
        requestLocation();

        // Pindah ke langkah 2
        document.getElementById('step-category').classList.add('hidden');
        document.getElementById('step-sending').classList.remove('hidden');
        document.getElementById('modal-category-label').textContent = selectedCategory;
        document.getElementById('modal-status').textContent = 'Mengambil lokasi GPS...';

        submitEmergency();
    }

    function requestLocation() {
        if (!navigator.geolocation) return;
        locationPromise = new Promise((resolve) => {
            navigator.geolocation.getCurrentPosition(
                (pos) => { locationPayload = { latitude: pos.coords.latitude, longitude: pos.coords.longitude }; resolve(); },
                () => { locationPayload = { latitude: null, longitude: null }; resolve(); },
                { enableHighAccuracy: true, timeout: 5000, maximumAge: 30000 }
            );
        });
    }

    function cancelEmergency() {
        isSubmitting = false;
        selectedCategory = null;
        locationPayload = { latitude: null, longitude: null };

        // Atur ulang semua tombol kategori menjadi tidak aktif
        document.querySelectorAll('.category-btn').forEach(btn => {
            btn.classList.remove('border-red-600', 'bg-red-50', 'text-red-700');
            btn.classList.add('bg-gray-50', 'border-gray-200');
            const nameSpan = btn.querySelector('.category-name');
            if (nameSpan) {
                nameSpan.classList.remove('text-red-700');
                nameSpan.classList.add('text-gray-900');
            }
        });

        // Disable next button
        const nextBtn = document.getElementById('btn-next-step');
        if (nextBtn) {
            nextBtn.setAttribute('disabled', 'true');
            nextBtn.classList.add('opacity-50', 'cursor-not-allowed');
            nextBtn.classList.remove('bg-red-700', 'hover:bg-red-800');
            nextBtn.classList.add('bg-gray-300');
        }

        document.getElementById('step-sending').classList.add('hidden');
        document.getElementById('step-category').classList.remove('hidden');
    }

    async function submitEmergency() {
        if (isSubmitting) return;
        isSubmitting = true;

        document.getElementById('modal-status').textContent = 'Mengirim laporan ke mitra terdekat...';

        await Promise.race([
            locationPromise,
            new Promise(r => setTimeout(r, 1000))
        ]);

        const payload = {
            category: selectedCategory || 'Lainnya',
            description: null,
            latitude: locationPayload.latitude || lat,
            longitude: locationPayload.longitude || lng,
            anonymous: 0,
        };

        try {
            const res = await fetch('/emergency', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(payload),
            });

            if (!res.ok) {
                let errMsg = 'Gagal terhubung. Coba lagi atau hubungi 112.';
                try {
                    const errData = await res.json();
                    if (errData && errData.error) errMsg = errData.error;
                } catch(ex) {}
                throw new Error(errMsg);
            }

            const data = await res.json();
            document.body.style.overflow = '';

            // Simpan ID laporan langsung ke localStorage sebelum mengalihkan untuk mendukung sesi/cookie sementara di perangkat seluler
            if (data.report_id) {
                try {
                    let storedReports = JSON.parse(localStorage.getItem('safora_guest_reports') || '[]');
                    storedReports.push(data.report_id);
                    localStorage.setItem('safora_guest_reports', JSON.stringify(Array.from(new Set(storedReports))));
                } catch (err) {
                    console.error('Error saving to localStorage:', err);
                }
            }

            let redirectUrl = data.tracking_url;
            if (data.call_phone) {
                // Panggil dialer telepon bawaan perangkat segera selagi masih dalam aksi klik pengguna
                window.location.href = 'tel:' + data.call_phone;
                sessionStorage.setItem('safora_call_triggered_' + data.report_id, 'true');

                // Teruskan nomor telepon ke halaman pelacakan untuk menghindari masalah race condition waktu/pengalihan di perangkat seluler/situs web yang dideploy
                try {
                    const urlObj = new URL(redirectUrl, window.location.origin);
                    urlObj.searchParams.set('call', data.call_phone);
                    redirectUrl = urlObj.toString();
                } catch (e) {
                    redirectUrl += (redirectUrl.indexOf('?') !== -1 ? '&' : '?') + 'call=' + encodeURIComponent(data.call_phone);
                }

                // Tunda sedikit pengalihan halaman agar dialer bawaan perangkat sempat terbuka
                setTimeout(() => {
                    window.location.href = redirectUrl;
                }, 800);
            } else {
                window.location.href = redirectUrl;
            }
        } catch (e) {
            document.getElementById('modal-status').textContent = e.message;
            isSubmitting = false;
            alert(e.message);
            closeEmergencyModal();
        }
    }

    function startPanic(){
        openEmergencyModal();
    }

    // Listener klik backdrop dihapus untuk mencegah modal tertutup ketika mengklik di luar area popup

    // Fungsi Modal Edit Laporan
    function openEditReportModal(btn) {
        const id = btn.getAttribute('data-id');
        const category = btn.getAttribute('data-category');
        const description = btn.getAttribute('data-desc');

        const modal = document.getElementById('edit-report-modal');
        const form = document.getElementById('edit-report-form');
        const catSelect = document.getElementById('edit-category');
        const descInput = document.getElementById('edit-description');

        form.action = `/report/${id}`;
        
        if (!catSelect.querySelector(`option[value="${category}"]`)) {
            const option = document.createElement('option');
            option.value = category;
            option.text = category.charAt(0).toUpperCase() + category.slice(1);
            catSelect.appendChild(option);
        }
        catSelect.value = category;
        descInput.value = description || '';

        document.body.style.overflow = 'hidden';
        modal.classList.add('flex');
        modal.classList.remove('hidden');
    }
    function closeEditReportModal() {
        const modal = document.getElementById('edit-report-modal');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
    document.getElementById('edit-report-modal').addEventListener('click', function(e) { if(e.target === this) closeEditReportModal(); });

    // Realtime hide edit/hapus report
    function checkReportActionTime() {
        const now = Math.floor(Date.now() / 1000);
        document.querySelectorAll('.report-action-buttons').forEach(el => {
            const time = parseInt(el.getAttribute('data-time'));
            if (now - time > 15 * 60) {
                el.style.display = 'none';
            }
        });
    }
    checkReportActionTime();
    setInterval(checkReportActionTime, 10000);

    // Modal Semua Laporan
    let _arPage = 1;
    let _arHasMore = false;
    let _arFetching = false;
    let _arDebounce = null;

    const _arStatusColors = {
        'Submitted':   { bg: 'bg-gray-100',   text: 'text-gray-600',   dot: 'bg-gray-400' },
        'Routed':      { bg: 'bg-blue-50',     text: 'text-blue-700',   dot: 'bg-blue-500' },
        'Viewed':      { bg: 'bg-yellow-50',   text: 'text-yellow-700', dot: 'bg-yellow-500' },
        'In Progress': { bg: 'bg-orange-50',   text: 'text-orange-700', dot: 'bg-orange-500' },
        'Resolved':    { bg: 'bg-green-50',    text: 'text-green-700',  dot: 'bg-green-500' },
    };

    function openAllReportsModal() {
        const modal = document.getElementById('all-reports-modal');
        if (!modal) return;
        document.body.style.overflow = 'hidden';
        modal.classList.add('flex');
        modal.classList.remove('hidden');
        _arPage = 1;
        document.getElementById('all-reports-list').innerHTML = '';
        fetchAllReports(true);
    }

    function closeAllReportsModal() {
        const modal = document.getElementById('all-reports-modal');
        if (!modal) return;
        modal.classList.remove('flex');
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    document.getElementById('all-reports-modal').addEventListener('click', function(e) {
        if (e.target === this) closeAllReportsModal();
    });

    async function fetchAllReports(reset = false) {
        if (_arFetching) return;
        if (!reset && !_arHasMore) return;

        _arFetching = true;
        if (reset) { _arPage = 1; }

        const loader = document.getElementById('all-reports-loader');
        const list   = document.getElementById('all-reports-list');
        loader.classList.remove('hidden');

        const category  = document.getElementById('all-reports-category')?.value || '';
        const status    = document.getElementById('all-reports-status')?.value   || '';
        const startDate = document.getElementById('all-reports-start')?.value    || '';
        const endDate   = document.getElementById('all-reports-end')?.value      || '';

        const params = new URLSearchParams({ page: _arPage });
        if (category)  params.set('category',   category);
        if (status)    params.set('status',      status);
        if (startDate) params.set('start_date',  startDate);
        if (endDate)   params.set('end_date',    endDate);

        try {
            const res  = await fetch(`/dashboard/reports?${params.toString()}`, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const json = await res.json();

            const items = json.data || [];
            _arHasMore  = json.next_page_url != null;

            // Subtitle
            const subtitle = document.getElementById('all-reports-subtitle');
            if (subtitle) subtitle.textContent = `${json.total} laporan ditemukan.`;

            if (reset && items.length === 0) {
                list.innerHTML = '<div class="text-sm text-gray-400 text-center py-6">Tidak ada laporan yang cocok.</div>';
            } else {
                const html = items.map(r => {
                    const s   = _arStatusColors[r.status] || _arStatusColors['Submitted'];
                    const date = r.incident_date
                        ? formatDate(r.incident_date)
                        : formatDate(r.created_at);
                    const catLabel = { ambulance:'Medis Darurat', legal:'Bantuan Hukum', counselor:'Psikososial', pemadam:'Pemadam / Rescue' }[r.category] || r.category;
                    const anonBadge = r.anonymous ? `<span class="text-[10px] bg-purple-50 text-purple-700 px-2 py-0.5 rounded-full font-semibold">Anonim</span>` : '';
                    const evBadge  = r.evidences_count > 0 ? `<span class="text-[10px] bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full font-semibold">${r.evidences_count} bukti</span>` : '';
                    return `
                    <a href="/tracking/${r.id}" class="flex items-center justify-between p-4 sm:p-5 bg-[#faf9f7] hover:bg-gray-50 rounded-xl border border-gray-100 hover:border-gray-200 transition group gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="font-semibold text-gray-900 text-sm truncate">${catLabel}</p>
                                ${anonBadge}${evBadge}
                            </div>
                            <p class="text-gray-400 text-xs mt-1">${date}</p>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full ${s.bg} ${s.text} flex items-center gap-1.5 whitespace-nowrap">
                                <span class="w-1.5 h-1.5 rounded-full ${s.dot}"></span>${_arStatusNames[r.status] || r.status}
                            </span>
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-gray-500 transition shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </a>`;
                }).join('');
                if (reset) list.innerHTML = html;
                else       list.insertAdjacentHTML('beforeend', html);
            }
            _arPage++;
        } catch (err) {
            list.innerHTML = `<div class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-xl px-3 py-2">Gagal memuat laporan: ${err.message}</div>`;
        } finally {
            loader.classList.add('hidden');
            _arFetching = false;
        }
    }

    function handleAllReportsScroll() {
        const container = document.getElementById('all-reports-scroll-container');
        if (!container) return;
        if (container.scrollTop + container.clientHeight >= container.scrollHeight - 100) {
            fetchAllReports(false);
        }
    }

    function triggerAllReportsFilter() {
        if (_arDebounce) clearTimeout(_arDebounce);
        _arDebounce = setTimeout(() => {
            document.getElementById('all-reports-list').innerHTML = '';
            fetchAllReports(true);
        }, 300);
    }

    ['all-reports-category', 'all-reports-status'].forEach(id => {
        document.getElementById(id)?.addEventListener('change', triggerAllReportsFilter);
    });
    ['all-reports-start', 'all-reports-end'].forEach(id => {
        document.getElementById(id)?.addEventListener('change', triggerAllReportsFilter);
    });
    </script>
</body>
</html>
