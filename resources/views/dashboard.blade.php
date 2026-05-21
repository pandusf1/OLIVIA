<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Safora — Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
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
    </style>
</head>
<body class="bg-[#f5f4f1] text-gray-900 antialiased min-h-screen">

    @php $backUrl = null; @endphp
    @include('partials.nav-auth')

    <div class="max-w-6xl mx-auto px-6 py-10 fade-in">

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl mb-6 text-sm flex items-center gap-2">
            <svg class="w-4 h-4 text-green-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
        @endif


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

                {{-- Countdown Area --}}
                <div id="countdown-area" class="hidden relative z-20 w-52 h-52 sm:w-64 sm:h-64 bg-white rounded-full flex flex-col items-center justify-center shadow-2xl border-[6px] border-red-700">
                    <p class="text-[10px] sm:text-xs text-gray-500 font-bold uppercase tracking-widest mb-1">MENGIRIM DALAM</p>
                    <p id="cd-num" class="text-7xl sm:text-8xl font-black text-red-700 font-unbounded leading-none mb-1">5</p>
                    <div class="w-2/3 bg-gray-100 rounded-full h-2 mb-4 overflow-hidden">
                        <div id="cd-bar" class="bg-red-700 h-2 rounded-full transition-all duration-1000" style="width:100%"></div>
                    </div>
                    <button onclick="cancelPanic()" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-6 py-2 rounded-full font-bold text-xs uppercase transition shadow-sm border border-gray-200">BATAL</button>
                    <div id="cd-category" class="hidden"></div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 mb-6 fade-in">
            @foreach([
                [route('trusted-contact.index'), '👤', 'Kontak Darurat', 'Orang terpercaya', 'bg-blue-50'],
                ['/evidence',                   '🗂️', 'Galeri Bukti',  'Aman tersimpan', 'bg-purple-50'],
                [route('chat.threads'),         '💬', 'Chat', 'Riwayat chat', 'bg-orange-50'],
                ['/witness',                    '🛡️', 'Mode Saksi',    'Bantu korban', 'bg-green-50'],
            ] as [$url, $icon, $title, $sub, $bg])
            <a href="{{ $url }}" class="bg-white border border-gray-100 hover:border-gray-200 rounded-2xl p-4 flex flex-col items-center text-center transition group shadow-sm active:scale-95">
                <div class="w-12 h-12 {{ $bg }} rounded-full flex items-center justify-center text-xl mb-3 group-hover:scale-110 transition-transform">
                    {{ $icon }}
                </div>
                <p class="font-bold text-gray-900 text-sm mb-0.5">{{ $title }}</p>
                <p class="text-[11px] text-gray-500">{{ $sub }}</p>
            </a>
            @endforeach
        </div>

        <div class="grid lg:grid-cols-2 gap-6 fade-in">

            {{-- ===== LEFT COLUMN ===== --}}
            <div class="space-y-6">

                {{-- Map Partner (Style Card berurut jarak) --}}
                <div class="bg-white border border-gray-200 rounded-2xl p-6">
                        <div class="flex items-center justify-between mb-4">
                        <div>
<h2 class="font-semibold text-gray-900">Partner Terdekat</h2>
                            <p class="text-gray-400 text-xs mt-0.5">Urut berdasarkan jarak dari lokasi kamu.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" id="btn-view-all-partners" aria-label="Lihat semua partner" title="Lihat semua partner"
                                class="hidden sm:inline-flex text-xs font-semibold text-gray-700 hover:text-gray-900 bg-gray-50 hover:bg-gray-100 px-3 py-1.5 rounded-full transition">
                                Lihat Semua
                            </button>
                            <button type="button" id="btn-reload-location" aria-label="Reload lokasi" title="Reload lokasi"
                                class="text-xs font-semibold text-gray-700 hover:text-gray-900 bg-gray-50 hover:bg-gray-100 px-3 py-1.5 rounded-full transition">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                </svg>
                            </button>
                        </div>
                    </div>


                    <div id="nearby-map" class="relative overflow-hidden rounded-xl bg-[#faf9f7] border border-gray-100">
                        {{-- Topographic / map-like background (no real map API) --}}
                        <div class="absolute inset-0 pointer-events-none" style="
                            background:
                                radial-gradient(circle at 30% 25%, rgba(239,68,68,0.10) 0%, rgba(239,68,68,0.00) 55%),
                                radial-gradient(circle at 70% 65%, rgba(239,68,68,0.08) 0%, rgba(239,68,68,0.00) 50%),
                                repeating-linear-gradient(135deg, rgba(0,0,0,0.05) 0, rgba(0,0,0,0.05) 1px, transparent 1px, transparent 14px),
                                repeating-linear-gradient(45deg, rgba(0,0,0,0.03) 0, rgba(0,0,0,0.03) 1px, transparent 1px, transparent 18px);
                            filter: saturate(1.05) contrast(1.02);
                        "></div>

                        <div class="absolute -top-24 -left-24 w-56 h-56 bg-red-50/60 rounded-full blur-2xl pointer-events-none"></div>
                        <div class="absolute -bottom-24 -right-24 w-56 h-56 bg-red-50/40 rounded-full blur-2xl pointer-events-none"></div>

                        <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                            <div id="range-ring" class="w-[78%] h-[78%] rounded-full border border-red-200/70 bg-red-50/20" style="box-shadow: inset 0 0 0 1px rgba(220,38,38,0.08);"></div>
                        </div>

                        <div class="p-4 relative">
                            {{-- Map header row --}}
                            <div class="flex items-center gap-2 mb-3">
                                <span class="w-2.5 h-2.5 rounded-full bg-red-700" title="Kamu"></span>
                                <p class="text-xs font-semibold text-gray-600">Lokasi kamu</p>
                            </div>



                            {{-- Map canvas (visual only) --}}
                            <div class="relative w-full h-44 rounded-xl border border-gray-100 bg-white/40 overflow-hidden" aria-label="Peta semu partner terdekat">
                                {{-- Center user marker --}}
                                <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2">
                                    <div class="w-3 h-3 rounded-full bg-red-700 shadow-md shadow-red-200"></div>
                                    <div class="w-6 h-6 rounded-full border-2 border-red-200/80 absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 animate-pulse" style="animation-duration:2.2s"></div>
                                </div>

                            <div id="nearby-markers" class="absolute inset-0"></div>




                            <div class="absolute left-3 bottom-3 flex items-center gap-2 text-[11px] text-gray-500">
                                    <span class="w-2 h-2 rounded-full bg-red-700"></span><span>Kamu</span>
                                    <span class="w-2 h-2 rounded-full bg-red-300"></span><span>Lokasi</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 mb-3 mt-3">
                                <select id="map-search-type" class="w-1/2 border border-gray-200 rounded-xl px-3 py-2 text-xs bg-white focus:outline-none focus:border-gray-400">
                                    <option value="">Semua</option>
                                    <option value="ambulance">Ambulans</option>
                                    <option value="legal">LBH / Pengacara</option>
                                    <option value="counselor">Psikolog</option>
                                </select>
                                <input id="map-search-query" type="text" placeholder="Cari (mis. Semarang)" class="w-1/2 border border-gray-200 rounded-xl px-3 py-2 text-xs bg-white focus:outline-none focus:border-gray-400">
                            </div>

                            <div id="nearby-partners" class="space-y-2">
                                <div class="text-sm text-gray-400">Memuat partner terdekat...</div>
                            </div>

                            <button type="button" onclick="openAllPartnersModal()" class="w-full mt-3 sm:hidden text-xs font-semibold text-gray-700 hover:text-gray-900 bg-gray-50 hover:bg-gray-100 py-2.5 rounded-xl transition border border-gray-100 text-center block">
                                Lihat Semua Partner
                            </button>

                        </div>
                    </div>

                </div>
            </div>

            {{-- ===== RIGHT COLUMN ===== --}}
            <div class="space-y-6">
                {{-- Laporan Saya --}}
                <div class="bg-white border border-gray-200 rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-5">
                        <div>
<h2 class="font-semibold text-gray-900">Riwayat Laporan</h2>
                            <p class="text-gray-400 text-xs mt-0.5">{{ $totalReports }} laporan tercatat</p>
                        </div>
                        <a href="{{ route('report.create') }}" class="text-xs font-semibold text-red-700 hover:text-red-800 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-full transition">+ Buat Laporan</a>
                    </div>

                    @if($reports->count() > 0)
                    <div class="space-y-2">
                        @foreach($reports->take(5) as $report)
                        @php
                        $sc = [
                            'Submitted'   => ['bg'=>'bg-gray-100',    'text'=>'text-gray-600',  'dot'=>'bg-gray-400'],
                            'Routed'      => ['bg'=>'bg-blue-50',     'text'=>'text-blue-700',  'dot'=>'bg-blue-500'],
                            'Viewed'      => ['bg'=>'bg-yellow-50',   'text'=>'text-yellow-700','dot'=>'bg-yellow-500'],
                            'In Progress' => ['bg'=>'bg-orange-50',   'text'=>'text-orange-700','dot'=>'bg-orange-500'],
                            'Resolved'    => ['bg'=>'bg-green-50',    'text'=>'text-green-700', 'dot'=>'bg-green-500'],
                        ];
                        $s = $sc[$report->status] ?? $sc['Submitted'];
                        @endphp
                        <a href="/tracking/{{ $report->id }}" class="flex items-center justify-between p-4 bg-[#faf9f7] hover:bg-gray-50 rounded-xl border border-gray-100 hover:border-gray-200 transition group">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="font-semibold text-gray-900 text-sm">{{ $report->category }}</p>
                                    @if($report->anonymous)
                                    <span class="text-xs bg-purple-50 text-purple-700 px-2 py-0.5 rounded-full">Anonim</span>
                                    @endif
                                    @if($report->evidences_count > 0)
                                    <span class="text-xs bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full">{{ $report->evidences_count }} bukti</span>
                                    @endif
                                </div>
                                <p class="text-gray-400 text-xs mt-0.5">{{ $report->created_at->format('d M Y, H:i') }}</p>
                                @if($report->partner)
                                <div class="mt-2 bg-white border border-gray-100 rounded-lg p-2.5 shadow-sm">
                                    <p class="text-xs font-semibold text-gray-800 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                        {{ $report->partner->partner_name }}
                                    </p>
                                    <div class="mt-1 space-y-0.5">
                                        <p class="text-[11px] text-gray-500 flex items-center gap-1.5">
                                            <span class="w-3.5 flex justify-center">📞</span> {{ $report->partner->phone ?? '-' }}
                                        </p>
                                        <p class="text-[11px] text-gray-500 flex items-center gap-1.5 line-clamp-1" title="{{ $report->partner->address }}">
                                            <span class="w-3.5 flex justify-center">📍</span> {{ $report->partner->address ?? '-' }}
                                        </p>
                                    </div>
                                </div>
                                @endif
                            </div>
                            <div class="flex items-center gap-2 ml-3">
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $s['bg'] }} {{ $s['text'] }} flex items-center gap-1.5 whitespace-nowrap">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $s['dot'] }}"></span>
                                    {{ $report->status }}
                                </span>
                                <svg class="w-4 h-4 text-gray-300 group-hover:text-gray-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </div>
                        </a>
                        @endforeach
                        @if($reports->count() > 5)
                        <a href="/emergency" class="block text-center text-xs text-gray-400 hover:text-gray-600 py-2 transition">
                            Lihat {{ $reports->count() - 5 }} laporan lainnya →
                        </a>
                        @endif
                    </div>
                    @else
                    <div class="text-center py-10">
                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <p class="text-gray-400 text-sm font-medium">Belum ada laporan</p>
                        <p class="text-gray-300 text-xs mt-1">Laporan kamu akan muncul di sini</p>
                    </div>
                    @endif
                </div>

            </div>
        </div>
    </div>

    {{-- ===== KATEGORI MODAL ===== --}}
    <div id="all-partners-modal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center px-4">
        <div class="bg-white rounded-2xl w-full max-w-lg p-5 shadow-2xl max-h-[85vh] overflow-hidden flex flex-col">
            <div class="flex items-start justify-between gap-3 mb-3">
                <div class="min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <div class="w-2 h-2 bg-red-700 rounded-full animate-pulse"></div>
                        <p class="text-red-700 text-xs font-semibold uppercase tracking-widest">Semua Partner</p>
                    </div>
                    <h2 class="font-black text-xl text-gray-900 mb-1 font-unbounded leading-tight">Hasil sesuai filter</h2>
                    <p id="all-partners-subtitle" class="text-gray-400 text-xs">Menampilkan semua partner yang cocok.</p>
                </div>
                <button type="button" onclick="closeAllPartnersModal()" class="text-gray-400 hover:text-gray-600 transition p-2 rounded-full">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="flex items-center gap-2 mb-3">
                <select id="all-partners-type" class="w-1/2 border border-gray-200 rounded-xl px-3 py-2 text-xs bg-white focus:outline-none focus:border-gray-400">
                    <option value="">Semua</option>
                    <option value="ambulance">Ambulans</option>
                    <option value="legal">LBH / Pengacara</option>
                    <option value="counselor">Psikolog</option>
                </select>
                <input id="all-partners-query" type="text" placeholder="Cari (mis. Semarang)" class="w-1/2 border border-gray-200 rounded-xl px-3 py-2 text-xs bg-white focus:outline-none focus:border-gray-400">
            </div>

            <div class="flex-1 overflow-auto">
                <div id="all-partners-list" class="space-y-2">
                    <div class="text-sm text-gray-400">Memuat partner...</div>
                </div>
            </div>

            <div class="mt-3">
                <button type="button" onclick="closeAllPartnersModal()" class="w-full bg-gray-900 hover:bg-gray-700 text-white py-3 rounded-xl font-semibold text-sm transition">Tutup</button>
            </div>
        </div>
    </div>

    <div id="cat-modal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center px-4">

        <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-2xl">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-2 h-2 bg-red-700 rounded-full animate-pulse"></div>
                <p class="text-red-700 text-xs font-semibold uppercase tracking-widest">Laporan Aktif</p>
            </div>
            <h2 class="font-black text-xl text-gray-900 mb-1 font-unbounded">Apa yang terjadi?</h2>
            <p class="text-gray-400 text-sm mb-5">Pilih kategori. Lokasi sudah terekam otomatis.</p>
            <form id="emergency-form" action="/emergency" method="POST">
                @csrf
                <input type="hidden" name="latitude" id="f-lat">
                <input type="hidden" name="longitude" id="f-lng">
                <input type="hidden" name="category" id="fallback-category" value="Darurat" disabled>
                <div class="grid grid-cols-2 gap-2 mb-4">
                    @foreach([['Salah Tangkap','⚖️'],['Pelecehan','🛡️'],['Kekerasan','👊'],['Kecelakaan','🚑']] as [$val,$icon])
                    <label class="cursor-pointer" onclick="document.getElementById('fallback-category').disabled=true;">
                        <input type="radio" name="category" value="{{ $val }}" class="peer hidden" required>
                        <div class="peer-checked:bg-red-50 peer-checked:border-red-400 border-2 border-gray-100 rounded-xl p-3 text-center hover:border-gray-200 transition">
                            <p class="text-2xl mb-1">{{ $icon }}</p>
                            <p class="text-sm font-semibold text-gray-900">{{ $val }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>
                <div class="flex items-center justify-between border border-gray-200 rounded-xl px-4 py-3 mb-4">
                    <div><p class="text-sm font-semibold text-gray-900">Mode Anonim</p><p class="text-xs text-gray-400">Identitas tidak ditampilkan publik</p></div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="anonymous" value="1" checked class="sr-only peer">
                        <div class="w-10 h-6 bg-gray-200 peer-checked:bg-red-700 rounded-full transition-all after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-4"></div>
                    </label>
                </div>
                <textarea name="description" rows="2" placeholder="Deskripsi singkat (opsional)..." class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-gray-400 mb-4 resize-none transition"></textarea>
                <div id="loc-info" class="flex items-center gap-2 mb-4 text-xs text-gray-400">
                    <div class="w-2 h-2 bg-yellow-400 rounded-full animate-pulse"></div>
                    Mengambil lokasi GPS...
                </div>
                <p id="auto-submit-info" class="text-xs text-red-600 font-bold text-center mb-3 hidden">Laporan akan otomatis terkirim dalam <span id="auto-submit-cd">30</span> detik.</p>
                <button type="submit" class="w-full bg-red-700 hover:bg-red-800 text-white py-4 rounded-xl font-black text-base transition">🚨 KIRIM LAPORAN DARURAT</button>
            </form>
            <button onclick="closeCatModal()" class="w-full mt-2 text-gray-400 hover:text-gray-600 text-sm py-2 transition">Batalkan</button>
        </div>
    </div>

    <script>
    let cdInterval=null, lat=null, lng=null;

    function openAllPartnersModal(){
        const m = document.getElementById('all-partners-modal');
        if(!m) return;
        document.body.style.overflow = 'hidden';
        m.classList.remove('hidden');
    }

    function closeAllPartnersModal(){
        const m = document.getElementById('all-partners-modal');
        if(!m) return;
        m.classList.add('hidden');
        document.body.style.overflow = '';
    }

    if(navigator.geolocation){
        navigator.geolocation.getCurrentPosition(
            p=>{lat=p.coords.latitude;lng=p.coords.longitude;document.getElementById('f-lat').value=lat;document.getElementById('f-lng').value=lng;document.getElementById('loc-info').innerHTML='<div class="w-2 h-2 bg-green-500 rounded-full"></div><span class="text-green-600">Lokasi: '+lat.toFixed(4)+', '+lng.toFixed(4)+'</span>';},
            ()=>{document.getElementById('loc-info').innerHTML='<div class="w-2 h-2 bg-red-400 rounded-full"></div><span class="text-red-500">GPS tidak tersedia.</span>';}
        );
    }



    // Load partner gabungan (ambulans + LBH + psikolog + dll) + marker + search
    async function loadNearbyPartners({type = '', query = ''} = {}){
        const el = document.getElementById('nearby-partners');
        const markersEl = document.getElementById('nearby-markers');

        if(!el) return;

        try{
            el.innerHTML = '<div class="text-sm text-gray-400">Memuat partner...</div>';
            if(markersEl) markersEl.innerHTML = '';

            const params = new URLSearchParams();
            if(type) params.set('type', type);
            if(query) params.set('query', query);

            const res = await fetch(`/map-search?${params.toString()}`, { headers: { 'Accept':'application/json' } });
            if(!res.ok) throw new Error('HTTP '+res.status);

            const json = await res.json();
            const items = json.data || [];

            if(items.length===0){
                el.innerHTML = '<div class="text-sm text-gray-400">Belum ada partner yang cocok.</div>';
                return;
            }

            // Cache full result untuk dipakai fitur “Lihat Semua”
            window.__lastNearbyItems = items;

            // Di bawah map hanya tampilkan 4 partner terdekat (preview)
            const previewTop = items.slice(0,4);
            // Namun titik di map tetap menampilkan semua hasil sesuai filter
            const mapAll = items;

            // Visual marker layout: tanpa konversi koordinat ke piksel real map.
            if(markersEl){
                const maxKm = Math.max(...mapAll.map(x => Number(x.distance_km) || 0), 1);

                mapAll.forEach((x, i)=>{


                    const p = x.partner;
                    const km = Number(x.distance_km) || 0;
                    const t = Math.min(km / maxKm, 1);
                    const rPct = 18 + t * 44;
                    const angle = (i * 73 + (p.partner_name?.length || 0) * 11) * Math.PI / 180;

                    const cx = 50;
                    const cy = 50;
                    const xPct = cx + Math.cos(angle) * rPct;
                    const yPct = cy + Math.sin(angle) * rPct;

                    const marker = document.createElement('a');
                    marker.href = `/data-partner/${p.id}`;
                    marker.className = 'absolute -translate-x-1/2 -translate-y-1/2 group';
                    marker.style.zIndex = '0';

                    marker.style.left = `${xPct}%`;
                    marker.style.top = `${yPct}%`;

                    marker.innerHTML = `
                        <div class="w-2.5 h-2.5 rounded-full bg-red-300 border border-red-200 shadow-sm"></div>
                        <div class="absolute -top-2 left-1/2 -translate-x-1/2">
                            <div class="hidden group-hover:block relative z-[999]">
                                <div class="text-[11px] bg-white/95 border border-gray-100 rounded-lg px-2 py-2 shadow text-gray-700 whitespace-nowrap max-w-[240px] relative z-[1000]">

                                    <div class="flex items-start gap-2">
                                        ${p.image_url ? `
                                            <img src="${p.image_url}" class="w-12 h-12 object-cover rounded border border-gray-100 shrink-0" alt="${String(p.partner_name).replace(/</g,'<').replace(/>/g,'>')}">
                                        ` : `
                                            <div class="w-12 h-12 bg-gray-100 rounded border border-gray-100 shrink-0"></div>
                                        `}
                                        <div class="min-w-0">
                                            <div class="font-semibold leading-tight">${String(p.partner_name).replace(/</g,'<').replace(/>/g,'>')}</div>
                                            <div class="text-gray-500 leading-tight">${p.partner_type} • ${Number(km).toFixed(2)} km</div>
                                            <div class="text-gray-600 leading-tight mt-1">${p.phone ? `📞 ${p.phone}` : '-'}</div>
                                            <div class="text-gray-500 leading-tight mt-1">${p.address ? String(p.address).replace(/</g,'<').replace(/>/g,'>') : '-'}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                    marker.style.zIndex = '0';

                    markersEl.appendChild(marker);


                });
            }

            el.innerHTML = previewTop.map((x,i)=>{
                const p = x.partner;
                return `
                    <a href="/data-partner/${p.id}" class="block bg-white border border-gray-100 rounded-xl p-3 hover:bg-gray-50 transition">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-bold text-gray-900 text-sm truncate">${p.partner_name}</p>
                                <p class="text-xs text-gray-500 mt-1">${p.partner_type} • ${Number(x.distance_km).toFixed(2)} km</p>
                            </div>
                            <span class="text-xs font-semibold px-2 py-1 rounded-full bg-red-50 text-red-700 shrink-0">${i+1}</span>
                        </div>
                    </a>
                `;
            }).join('');


        }catch(e){
            const el = document.getElementById('nearby-partners');
            if(el){
                el.innerHTML = `<div class="text-sm text-red-700 bg-red-50 border border-red-200 rounded-xl px-3 py-2">Gagal memuat partner: ${String(e && e.message ? e.message : e)}</div>`;
            }
        }
    }


    // Filter map (type + query) -> refresh marker & list
    const mapTypeEl = document.getElementById('map-search-type');
    const mapQueryEl = document.getElementById('map-search-query');

    function triggerMapSearch(){
        const t = mapTypeEl?.value || '';
        const q = mapQueryEl?.value || '';
        loadNearbyPartners({ type: t, query: q });

        // jika modal sedang terbuka, ikut refresh juga
        const allModal = document.getElementById('all-partners-modal');
        if(allModal && !allModal.classList.contains('hidden')){
            const items = window.__lastNearbyItems || [];
            renderAllPartners(items);
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


    // initial load (semua)
    loadNearbyPartners();

    // Lihat Semua
    const btnViewAll = document.getElementById('btn-view-all-partners');
    const allTypeEl = document.getElementById('all-partners-type');
    const allQueryEl = document.getElementById('all-partners-query');

    function renderAllPartners(items){
        const listEl = document.getElementById('all-partners-list');
        const subtitleEl = document.getElementById('all-partners-subtitle');
        if(!listEl) return;

        if(!items || items.length === 0){
            listEl.innerHTML = '<div class="text-sm text-gray-400">Belum ada partner yang cocok.</div>';
            if(subtitleEl) subtitleEl.textContent = '0 hasil untuk filter & pencarian saat ini.';
            return;
        }

        if(subtitleEl){
            subtitleEl.textContent = `Menampilkan ${items.length} partner. (Preview map tetap 4 di bawah, tapi marker menampilkan semua.)`;
        }

        listEl.innerHTML = items.map((x, i)=>{
            const p = x.partner;
            const km = Number(x.distance_km) || 0;
            return `
                <a href="/data-partner/${p.id}" class="block bg-white border border-gray-100 rounded-xl p-3 hover:bg-gray-50 transition">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-bold text-gray-900 text-sm truncate">${p.partner_name}</p>
                            <p class="text-xs text-gray-500 mt-1">${p.partner_type} • ${km.toFixed(2)} km</p>
                        </div>
                        <span class="text-xs font-semibold px-2 py-1 rounded-full bg-red-50 text-red-700 shrink-0">${i+1}</span>
                    </div>
                </a>
            `;
        }).join('');
    }

    // open modal sync input ke filter map yang aktif
    btnViewAll && btnViewAll.addEventListener('click', async ()=>{
        const t = mapTypeEl?.value || '';
        const q = mapQueryEl?.value || '';

        if(allTypeEl) allTypeEl.value = t;
        if(allQueryEl) allQueryEl.value = q;

        openAllPartnersModal();

        const items = window.__lastNearbyItems || [];
        renderAllPartners(items);
    });

    // modal search realtime (gunakan API yang sama)
    let allDebounceTimer = null;
    const allTriggerSearch = ()=>{
        if(allDebounceTimer) clearTimeout(allDebounceTimer);
        allDebounceTimer = setTimeout(async ()=>{
            const t = allTypeEl?.value || '';
            const q = allQueryEl?.value || '';
            const el = document.getElementById('all-partners-list');
            if(el) el.innerHTML = '<div class="text-sm text-gray-400">Memuat partner...</div>';

            try{
                const params = new URLSearchParams();
                if(t) params.set('type', t);
                if(q) params.set('query', q);

                const res = await fetch(`/map-search?${params.toString()}`, { headers: { 'Accept':'application/json' } });
                if(!res.ok) throw new Error('HTTP '+res.status);
                const json = await res.json();
                const items = json.data || [];
                window.__lastNearbyItems = items; // biar konsisten dengan map
                renderAllPartners(items);

                // refresh map juga agar marker & preview sesuai modal
                loadNearbyPartners({ type: t, query: q });
            }catch(e){
                if(el) el.innerHTML = `<div class="text-sm text-red-700 bg-red-50 border border-red-200 rounded-xl px-3 py-2">Gagal memuat partner: ${String(e && e.message ? e.message : e)}</div>`;
            }
        }, 250);
    };

    if(allTypeEl) allTypeEl.addEventListener('change', ()=>allTriggerSearch());
    if(allQueryEl) allQueryEl.addEventListener('input', ()=>allTriggerSearch());

    // close on backdrop click
    const allModal = document.getElementById('all-partners-modal');
    allModal && allModal.addEventListener('click', function(e){
        if(e.target === this) closeAllPartnersModal();
    });


    // Reload lokasi user -> simpan ke backend -> reload partner
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
                // (Jika lokasi belum tersimpan, fallback loadNearbyPartners akan menampilkan error dari API map-search)
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

                // Setelah lokasi tersimpan di backend, langsung reload partner & marker.
                // Menghapus quickCheck `/map-search` supaya 1 klik tidak melakukan request tambahan.





                // reload partner list/map
                await loadNearbyPartners({
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
                const el = document.getElementById('nearby-partners');
                if(el){
                    el.innerHTML = `<div class="text-sm text-red-700 bg-red-50 border border-red-200 rounded-xl px-3 py-2">Gagal menyimpan lokasi: ${msg}</div>`;
                }

                // fallback tetap mencoba reload partner (tapi UI error sudah ditampilkan)
                await loadNearbyPartners({
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








    let autoSubmitInterval = null;

    function startPanic(){
        document.getElementById('panic-btn').classList.add('hidden');
        document.getElementById('countdown-area').classList.remove('hidden');
        let n=5; document.getElementById('cd-num').textContent=n;
        cdInterval=setInterval(()=>{
            n--; document.getElementById('cd-num').textContent=n;
            document.getElementById('cd-bar').style.width=(n/5*100)+'%';
            if(n<=0){clearInterval(cdInterval);document.getElementById('countdown-area').classList.add('hidden');document.getElementById('panic-btn').classList.remove('hidden');openCatModal();}
        },1000);
    }
    function cancelPanic(){clearInterval(cdInterval);document.getElementById('countdown-area').classList.add('hidden');document.getElementById('panic-btn').classList.remove('hidden');}
    function openCatModal(){
        document.getElementById('cat-modal').classList.remove('hidden');
        document.body.style.overflow='hidden';
        
        let cd = 30;
        document.getElementById('auto-submit-info').classList.remove('hidden');
        document.getElementById('auto-submit-cd').textContent = cd;
        autoSubmitInterval = setInterval(() => {
            cd--;
            document.getElementById('auto-submit-cd').textContent = cd;
            if (cd <= 0) {
                clearInterval(autoSubmitInterval);
                const form = document.getElementById('emergency-form');
                const radios = form.querySelectorAll('input[name="category"]');
                let checked = false;
                radios.forEach(r => { 
                    if(r.checked) checked = true; 
                    r.required = false; // Remove required to avoid HTML5 validation blocking
                });
                if (!checked) {
                    document.getElementById('fallback-category').disabled = false;
                    document.getElementById('fallback-category').value = 'Darurat';
                }
                // Use standard HTMLFormElement submit to bypass any lingering event listeners
                HTMLFormElement.prototype.submit.call(form);
            }
        }, 1000);
    }
    function closeCatModal(){
        document.getElementById('cat-modal').classList.add('hidden');
        document.body.style.overflow='';
        if (autoSubmitInterval) clearInterval(autoSubmitInterval);
    }
    document.getElementById('cat-modal').addEventListener('click',function(e){if(e.target===this)closeCatModal();});
    </script>
    
    @include('partials.emergency-markers-js')
</body>
</html>
