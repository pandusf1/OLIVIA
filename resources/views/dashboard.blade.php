<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SuraRa — Dashboard</title>
    @vite('resources/css/app.css')
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

        {{-- ===== HERO HEADER ===== --}}
        <div class="mb-8 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-red-600 mb-2">
                    Selamat datang kembali
                </p>
                <h1 class="font-unbounded text-3xl font-black text-gray-900 leading-tight">
                    {{ auth()->user()->name }}
                </h1>
                <p class="text-gray-500 text-sm mt-1">
                    {{ now()->isoFormat('dddd, D MMMM Y') }}
                </p>
            </div>
            {{-- Emergency pill button --}}
            <a href="/emergency" class="inline-flex items-center gap-2 bg-red-700 hover:bg-red-800 text-white px-5 py-2.5 rounded-full font-semibold text-sm transition shadow-md shadow-red-200 self-start sm:self-auto">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                Laporan Darurat
            </a>
        </div>

        {{-- ===== STAT CARDS ===== --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            @php
            $stats = [
                ['label'=>'Total Laporan',  'value'=>$totalReports,    'color'=>'bg-[#f0ede8] border border-[#e2ddd6]', 'text'=>'text-gray-800', 'sub'=>'semua waktu'],
                ['label'=>'Aktif',          'value'=>$activeReports,   'color'=>'bg-[#fef3ec] border border-[#fddec4]', 'text'=>'text-orange-800','sub'=>'sedang diproses'],
                ['label'=>'Selesai',        'value'=>$resolvedReports, 'color'=>'bg-[#edf7f0] border border-[#c6e8d0]', 'text'=>'text-green-800', 'sub'=>'sudah resolved'],
                ['label'=>'Barang Bukti',   'value'=>$totalEvidences,  'color'=>'bg-[#eef3fd] border border-[#c9d9f8]', 'text'=>'text-blue-800',  'sub'=>'file terupload'],
            ];
            @endphp
            @foreach($stats as $s)
            <div class="rounded-2xl p-5 {{ $s['color'] }} {{ $s['text'] }}">
                <p class="text-4xl font-unbounded font-black leading-none mb-2">{{ $s['value'] }}</p>
                <p class="font-semibold text-sm opacity-90">{{ $s['label'] }}</p>
                <p class="text-xs opacity-50 mt-0.5">{{ $s['sub'] }}</p>
            </div>
            @endforeach
        </div>

        <div class="grid lg:grid-cols-3 gap-6">

            {{-- ===== LEFT: Map Partner + Emergency + Laporan ===== --}}
            <div class="lg:col-span-2 space-y-6">
                                {{-- Panic Button Card --}}
                <div class="bg-white border border-gray-200 rounded-2xl p-6 relative overflow-hidden">
                    {{-- decorative bg --}}
                    <div class="absolute right-0 top-0 w-40 h-40 bg-red-50 rounded-full -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>

                    <div class="flex items-center gap-2 mb-1">
                        <div class="w-2 h-2 rounded-full bg-red-600 animate-pulse"></div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-red-600">Tombol Darurat</p>
                    </div>
                    <p class="text-gray-400 text-xs mb-4">Tekan untuk mengirim sinyal bantuan dan lokasi kamu.</p>

                    <button onclick="startPanic()"
                        id="panic-btn"
                        class="panicdashboard:574 Reload lokasi gagal: Cannot read properties of null (reading 'getAttribute')
(anonymous)	@	dashboard:574-pulse w-full bg-red-700 hover:bg-red-800 text-white py-5 rounded-xl font-bold text-base tracking-wide transition flex items-center justify-center gap-3 relative z-10">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        TEKAN UNTUK BANTUAN
                    </button>

                    {{-- Countdown --}}
                    <div id="countdown-area" class="hidden mt-4">
                        <p class="text-xs text-gray-400 text-center uppercase tracking-wider mb-2">MENGIRIM DALAM</p>
                        <p id="cd-num" class="text-6xl font-black text-red-700 text-center font-unbounded">5</p>
                        <div class="w-full bg-gray-100 rounded-full h-1.5 my-3 overflow-hidden">
                            <div id="cd-bar" class="bg-red-700 h-1.5 rounded-full transition-all duration-1000" style="width:100%"></div>
                        </div>
                        <div id="cd-category" class="text-center text-gray-400 text-xs mb-3"></div>
                        <button onclick="cancelPanic()" class="w-full border border-gray-300 hover:border-gray-400 text-gray-700 py-3 rounded-xl font-semibold text-sm transition">BATAL</button>
                    </div>
                </div>

                {{-- Map Partner (Style Card berurut jarak) --}}
                <div class="bg-white border border-gray-200 rounded-2xl p-6">
                        <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="font-bold text-gray-900">Partner Terdekat</h2>
                            <p class="text-gray-400 text-xs mt-0.5">Urut berdasarkan jarak dari lokasi kamu.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" id="btn-view-all-partners" aria-label="Lihat semua partner" title="Lihat semua partner"
                                class="hidden sm:inline-flex text-xs font-semibold text-gray-700 hover:text-gray-900 bg-gray-50 hover:bg-gray-100 px-3 py-1.5 rounded-full transition">
                                Lihat Semua
                            </button>
                            <button type="button" id="btn-reload-location" aria-label="Reload lokasi" title="Reload lokasi"
                                class="text-xs font-semibold text-gray-700 hover:text-gray-900 bg-gray-50 hover:bg-gray-100 px-3 py-1.5 rounded-full transition">
                                <img src="/reload.png" alt="Reload lokasi" class="w-4 h-4 object-cover rounded" onerror="this.style.display='none'">
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

                        </div>
                    </div>

                </div>

                {{-- Laporan Saya --}}
                <div class="bg-white border border-gray-200 rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <h2 class="font-bold text-gray-900">Riwayat Laporan</h2>
                            <p class="text-gray-400 text-xs mt-0.5">{{ $totalReports }} laporan tercatat</p>
                        </div>
                        <a href="/emergency" class="text-xs font-semibold text-red-700 hover:text-red-800 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-full transition">+ Buat Laporan</a>
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

            {{-- ===== RIGHT: Menu + Kontak ===== --}}
            <div class="space-y-4">

                {{-- Quick Menu --}}
                <div class="bg-white border border-gray-200 rounded-2xl p-5">
                    <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-3">Menu Cepat</p>
                    <div class="space-y-1">
                        @foreach([
                            [route('trusted-contact.index'), '👤', 'Trusted Contact',   'Kelola kontak terpercaya'],
                            ['/evidence',                   '🗂️', 'Evidence Locker',    'Galeri bukti kamu'],
                            ['/emergency',                  '📄', 'Laporan Detail',     'Arsip / kejadian lama'],
                            ['/witness',                    '🛡️', 'Mode Saksi',         'Bantu laporan orang lain'],
                        ] as [$url, $icon, $title, $sub])
                        <a href="{{ $url }}" class="flex items-center gap-3 px-3 py-3 rounded-xl hover:bg-gray-50 transition group">
                            <div class="w-9 h-9 bg-gray-100 rounded-xl flex items-center justify-center text-base group-hover:bg-gray-200 transition shrink-0">{{ $icon }}</div>
                            <div class="min-w-0">
                                <p class="font-semibold text-gray-900 text-sm">{{ $title }}</p>
                                <p class="text-gray-400 text-xs">{{ $sub }}</p>
                            </div>
                            <svg class="w-4 h-4 text-gray-200 group-hover:text-gray-400 transition ml-auto shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                        @endforeach
                    </div>
                </div>

                {{-- Trusted Contacts --}}
                <div class="bg-white border border-gray-200 rounded-2xl p-5">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-7 h-7 bg-green-100 rounded-lg flex items-center justify-center text-sm">📞</div>
                        <div>
                            <p class="font-bold text-gray-900 text-sm">Kontak Terpercaya</p>
                            <p class="text-gray-400 text-xs">Dihubungi saat darurat</p>
                        </div>
                    </div>

                    <form action="/trusted-contact" method="POST" class="space-y-2 mb-4">
                        @csrf
                        <input type="text" name="contact_name" placeholder="Nama..." class="w-full border border-gray-200 focus:border-gray-400 rounded-lg px-3 py-2 text-sm focus:outline-none transition bg-gray-50 focus:bg-white" required>
                        <input type="text" name="contact_phone" placeholder="628xxxxxxxxx" class="w-full border border-gray-200 focus:border-gray-400 rounded-lg px-3 py-2 text-sm focus:outline-none transition bg-gray-50 focus:bg-white" required>
                        <button class="w-full bg-gray-900 hover:bg-gray-700 text-white py-2.5 rounded-lg font-semibold text-sm transition">+ Tambah Kontak</button>
                    </form>

                    @if(auth()->user()->trustedContacts->count() > 0)
                    <div class="space-y-1.5 border-t border-gray-100 pt-3">
                        @foreach(auth()->user()->trustedContacts as $c)
                        <div class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2.5">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-7 h-7 bg-green-600 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0">
                                    {{ strtoupper(substr($c->contact_name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $c->contact_name }}</p>
                                    <p class="text-xs text-gray-400">{{ $c->contact_phone }}</p>
                                </div>
                            </div>
                            <form action="/trusted-contact/{{ $c->id }}" method="POST">
                                @csrf @method('DELETE')
                                <button class="text-gray-300 hover:text-red-500 transition p-1 ml-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-4">
                        <p class="text-gray-300 text-xs">Belum ada kontak terpercaya.</p>
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
            <form action="/emergency" method="POST">
                @csrf
                <input type="hidden" name="latitude" id="f-lat">
                <input type="hidden" name="longitude" id="f-lng">
                <div class="grid grid-cols-2 gap-2 mb-4">
                    @foreach([['Salah Tangkap','⚖️'],['Pelecehan','🛡️'],['Kekerasan','👊'],['Kecelakaan','🚑']] as [$val,$icon])
                    <label class="cursor-pointer">
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
                    marker.href = `/pembayaran/partner/${p.id}`;
                    marker.className = 'absolute -translate-x-1/2 -translate-y-1/2 group';
                    marker.style.left = `${xPct}%`;
                    marker.style.top = `${yPct}%`;

                    marker.innerHTML = `
                        <div class="w-2.5 h-2.5 rounded-full bg-red-300 border border-red-200 shadow-sm"></div>
                        <div class="absolute -top-2 left-1/2 -translate-x-1/2">
                            <div class="hidden group-hover:block">
                                <div class="text-[11px] bg-white/95 border border-gray-100 rounded-lg px-2 py-1 shadow text-gray-700 whitespace-nowrap">
                                    ${String(p.partner_name).replace(/</g,'<').replace(/>/g,'>')}<br/>
                                    ${p.partner_type} • ${Number(km).toFixed(2)} km
                                </div>
                            </div>
                        </div>
                    `;

                    marker.style.zIndex = '2';
                    markersEl.appendChild(marker);
                });
            }

            el.innerHTML = previewTop.map((x,i)=>{
                const p = x.partner;
                return `
                    <a href="/pembayaran/partner/${p.id}" class="block bg-white border border-gray-100 rounded-xl p-3 hover:bg-gray-50 transition">
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
                <a href="/pembayaran/partner/${p.id}" class="block bg-white border border-gray-100 rounded-xl p-3 hover:bg-gray-50 transition">
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
            const imgEl = reloadBtn.querySelector('img');
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

                // Pastikan lokasi benar-benar tersimpan dengan cara mencoba load partner.
                // Jika map-search mengeluh lokasi belum tersedia, berarti relasi userLocation yang dipakai MapSearch belum terbentuk.
                const quickCheckRes = await fetch(`/map-search?type=&query=`, { headers: { 'Accept':'application/json' } });
                let quickCheckJson = null;
                try { quickCheckJson = await quickCheckRes.json(); } catch(e) { quickCheckJson = { parse_error: String(e) }; }
                console.log('quickCheck map-search after reload', { ok: quickCheckRes.ok, json: quickCheckJson });

                if(!quickCheckRes.ok || (quickCheckJson && quickCheckJson.error)){
                    const msg = quickCheckJson && quickCheckJson.error ? quickCheckJson.error : 'Lokasi tidak tersimpan / belum terbaca oleh map-search.';
                    throw new Error(msg);
                }




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
                const imgEl = reloadBtn.querySelector('img');
                if(imgEl){ imgEl.style.display = ''; }


            }
        });
    }








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
    function openCatModal(){document.getElementById('cat-modal').classList.remove('hidden');document.body.style.overflow='hidden';}
    function closeCatModal(){document.getElementById('cat-modal').classList.add('hidden');document.body.style.overflow='';}
    document.getElementById('cat-modal').addEventListener('click',function(e){if(e.target===this)closeCatModal();});
    </script>
</body>
</html>
