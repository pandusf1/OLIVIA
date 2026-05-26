<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Safora — Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
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
                ['/witness',                    '🛡️', 'Mode Saksi',    'Bantu korban', 'bg-green-50'],
                ['/evidence',                   '🗂️', 'Galeri Bukti',  'Aman tersimpan', 'bg-purple-50'],
                [route('chat.threads'),         '💬', 'Chat', 'Riwayat chat', 'bg-orange-50'],
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
                        <div class="p-4 relative">
                            {{-- Map canvas --}}
                            <div id="leaflet-map" class="relative w-full h-56 sm:h-64 rounded-xl border border-gray-100 bg-gray-100 overflow-hidden" style="z-index: 1;" aria-label="Peta partner terdekat">
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
                            </div>
                            <div class="flex items-center gap-2 ml-3 flex-shrink-0">
                                @if($report->created_at->diffInMinutes(now()) <= 15)
                                    <div class="flex items-center gap-1 mr-2" onclick="event.preventDefault(); event.stopPropagation();">
                                        <a href="{{ route('report.edit', $report->id) }}" class="text-xs bg-white hover:bg-gray-100 text-gray-700 px-2.5 py-1.5 rounded-lg border border-gray-200 transition font-medium">Edit</a>
                                        <form action="{{ route('report.destroy', $report->id) }}" method="POST" onsubmit="return confirm('Hapus laporan ini?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs bg-white hover:bg-red-50 text-red-600 px-2.5 py-1.5 rounded-lg border border-gray-200 transition font-medium">Hapus</button>
                                        </form>
                                    </div>
                                @endif
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

            <div id="all-partners-scroll-container" class="flex-1 overflow-auto" onscroll="handleAllPartnersScroll()">
                <div id="all-partners-list" class="space-y-2">
                    <div class="text-sm text-gray-400">Memuat partner...</div>
                </div>
            </div>

            <div class="mt-3">
                <button type="button" onclick="closeAllPartnersModal()" class="w-full bg-gray-900 hover:bg-gray-700 text-white py-3 rounded-xl font-semibold text-sm transition">Tutup</button>
            </div>
        </div>
    </div>

    <div id="cat-modal" class="hidden fixed inset-0 bg-black/80 z-[100] flex flex-col justify-center items-center px-6 py-6 transition-all duration-300">
        <div class="bg-white rounded-3xl w-full max-w-sm mx-auto p-5 shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-2.5 h-2.5 bg-red-600 rounded-full animate-pulse"></div>
                <p class="text-red-700 text-xs font-bold uppercase tracking-widest">Laporan Aktif</p>
            </div>
            <h2 class="font-black text-2xl text-gray-900 mb-1 leading-tight tracking-tight">Apa yang terjadi?</h2>
            <p class="text-gray-500 text-sm mb-6">Pilih kategori. GPS terekam otomatis.</p>
            
            <form id="emergency-form" class="flex flex-col h-full" onsubmit="event.preventDefault();">
                @csrf
                <input type="hidden" name="latitude" id="f-lat">
                <input type="hidden" name="longitude" id="f-lng">
                <input type="hidden" name="category" id="fallback-category" value="ambulance" disabled>
                <input type="hidden" name="idempotency_key" value="{{ Str::uuid() }}">
                
                <div id="step-1">
                <div class="grid grid-cols-2 gap-3 mb-5">
                    @foreach([['Medis Darurat','🚑','ambulance'],['Bantuan Hukum','⚖️','legal'],['Psikososial','🧠','counselor'],['Pemadam / Rescue','🚒','pemadam']] as [$label,$icon,$val])
                    <label class="cursor-pointer" onclick="handleCategoryTap(this)">
                        <input type="radio" name="category" value="{{ $val }}" class="peer hidden" required>
                        <div class="peer-checked:bg-red-50 peer-checked:border-red-500 border-2 border-gray-100 rounded-2xl p-4 text-center hover:border-gray-300 transition-all duration-200 active:scale-95 shadow-sm">
                            <p class="text-3xl mb-2">{{ $icon }}</p>
                            <p class="text-sm font-black text-gray-900">{{ $label }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>
                
                <div class="flex items-center justify-between bg-gray-50 rounded-2xl px-5 py-4 mb-4">
                    <div>
                        <p class="text-sm font-bold text-gray-900">Mode Anonim</p>
                        <p class="text-[11px] text-gray-500 mt-0.5">Identitas tidak ditampilkan</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="f-anon" name="anonymous" value="1" @guest checked @endguest class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-checked:bg-red-600 rounded-full transition-all after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                    </label>
                </div>
                
                <div id="loc-info" class="flex items-center gap-2 mb-4 text-[11px] text-gray-400 font-medium">
                    <div class="w-2 h-2 bg-yellow-400 rounded-full animate-pulse"></div>
                    Mengambil lokasi GPS...
                </div>
                
                <div class="bg-red-50 rounded-2xl p-4 mb-4">
                    <p id="auto-submit-info" class="text-sm text-red-700 font-bold text-center">Terkirim otomatis: <span id="auto-submit-cd" class="text-lg font-black">30</span>s</p>
                </div>
                
                <button type="button" onclick="submitStep1()" id="btn-next-step" class="w-full bg-red-600 hover:bg-red-700 active:bg-red-800 text-white py-4 rounded-2xl font-black text-lg transition-all duration-200 shadow-[0_4px_14px_0_rgba(220,38,38,0.39)]">SELANJUTNYA</button>
                </div>

                <!-- STEP 2 -->
                <div id="step-2" class="hidden flex-col h-full">
                    <h3 class="font-bold text-lg mb-2 text-center text-gray-900 mt-2">Laporan Terkirim!</h3>
                    <p class="text-xs text-gray-500 mb-4 text-center">Tambahkan detail opsional. Membantu partner memahami situasi lebih baik.</p>

                    <textarea name="description" id="step2-desc" rows="3" placeholder="Deskripsi kejadian..." oninput="resetStep2Timer()" class="w-full border border-gray-200 bg-gray-50 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-red-500 focus:bg-white mb-3 resize-none"></textarea>
                    
                    <div class="border border-dashed border-gray-300 rounded-xl p-4 mb-3 text-center bg-gray-50">
                        <input type="file" id="step2-evidence" name="evidence[]" multiple class="text-xs w-full" accept="image/*,video/*,audio/*" onchange="resetStep2Timer()">
                        <p class="text-xs text-gray-500 mt-2">Bisa pilih beberapa file sekaligus.</p>
                    </div>

                    <div class="flex items-center justify-between bg-gray-50 rounded-xl px-4 py-3 mb-4 border border-gray-200">
                        <div>
                            <p class="text-sm font-bold text-gray-900">Tampilkan Bukti</p>
                            <p class="text-[10px] text-gray-500 mt-0.5">Izinkan semua orang melihat bukti ini</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="show_evidence" value="1" checked class="sr-only peer" id="step2-show-evidence">
                            <div class="w-9 h-5 bg-gray-200 peer-checked:bg-red-600 rounded-full transition-all after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-white"></div>
                        </label>
                    </div>

                    <div class="bg-red-50 rounded-xl p-3 mb-4">
                        <p class="text-xs text-red-700 font-bold text-center">Menutup otomatis dalam: <span id="step2-cd" class="text-base font-black">60</span>s</p>
                    </div>

                    <button type="button" onclick="submitStep2()" id="btn-final-submit" class="w-full bg-gray-900 hover:bg-black text-white py-4 rounded-xl font-bold transition-all text-lg mt-auto shadow-[0_4px_14px_0_rgba(17,24,39,0.39)]">KIRIM LAPORAN</button>
                </div>
                <button onclick="closeCatModal()" class="w-full mt-2 text-gray-500 hover:text-gray-900 font-bold text-sm py-3 transition-colors">Kembali</button>
            </form>
        </div>
    </div>

    <script>
    let cdInterval=null, lat=null, lng=null;
    let map = null;
    let userMarker = null;
    let partnerMarkers = [];
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

            // Reload markers if they were loaded before map initialized
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
        watchId = navigator.geolocation.watchPosition(
            p=>{
                lat=p.coords.latitude;
                lng=p.coords.longitude;
                document.getElementById('f-lat').value=lat;
                document.getElementById('f-lng').value=lng;
                document.getElementById('loc-info').innerHTML='<div class="w-2 h-2 bg-green-500 rounded-full"></div><span class="text-green-600">Lokasi: '+lat.toFixed(4)+', '+lng.toFixed(4)+'</span>';
                
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
                            loadNearbyPartners({
                                type: document.getElementById('map-search-type')?.value || '',
                                query: document.getElementById('map-search-query')?.value || ''
                            });
                        }).catch(console.error);
                    }
                @endif
            },
            ()=>{document.getElementById('loc-info').innerHTML='<div class="w-2 h-2 bg-red-400 rounded-full"></div><span class="text-red-500">GPS tidak tersedia.</span>';},
            { enableHighAccuracy: true, maximumAge: 10000, timeout: 10000 }
        );
    }



    let emergencyMarkers = [];

    function partnerMarkerVisual(type) {
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
            default: {
                bg: 'bg-gray-700',
                svg: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6M9 10h.01M15 10h.01"/></svg>'
            }
        };

        return visuals[key];
    }

    function renderMapMarkers(items, emergencies = []) {
        if (!map) return;
        
        partnerMarkers.forEach(m => map.removeLayer(m));
        partnerMarkers = [];

        emergencyMarkers.forEach(m => map.removeLayer(m));
        emergencyMarkers = [];
        
        const bounds = [];
        if (lat && lng) bounds.push([lat, lng]);

        items.forEach((x, i) => {
            const p = x.partner;
            const km = Number(x.distance_km) || 0;
            if (p.latitude && p.longitude) {
                bounds.push([p.latitude, p.longitude]);
                const visual = partnerMarkerVisual(p.partner_type);
                
                const partnerIcon = L.divIcon({
                    className: 'custom-partner-marker',
                    html: `<div class="w-7 h-7 rounded-full ${visual.bg} border-2 border-white shadow-md flex items-center justify-center text-white relative z-20">${visual.svg}</div>`,
                    iconSize: [28, 28],
                    iconAnchor: [14, 14]
                });

                const popupHtml = `
                    <div class="text-xs p-1">
                        <div class="font-bold">${String(p.partner_name).replace(/</g,'&lt;')}</div>
                        <div class="text-gray-500">${p.partner_type} • ${km.toFixed(2)} km</div>
                        <a href="/data-partner/${p.id}" class="text-blue-600 hover:underline mt-1 block">Lihat Detail &rarr;</a>
                    </div>
                `;

                const m = L.marker([p.latitude, p.longitude], {icon: partnerIcon})
                    .bindPopup(popupHtml)
                    .addTo(map);
                partnerMarkers.push(m);
            }
        });

        emergencies.forEach((e) => {
            if (e.latitude && e.longitude) {
                bounds.push([e.latitude, e.longitude]);

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
        
        if (bounds.length > 0 && !window.__mapUserPanned) {
            map.fitBounds(bounds, { padding: [30, 30], maxZoom: 15 });
        }
    }

    // Load partner gabungan (ambulans + LBH + psikolog + dll) + marker + search
    async function loadNearbyPartners({type = '', query = ''} = {}){
        const el = document.getElementById('nearby-partners');

        if(!el) return;

        try{
            el.innerHTML = '<div class="text-sm text-gray-400">Memuat partner...</div>';

            const params = new URLSearchParams();
            if(type) params.set('type', type);
            if(query) params.set('query', query);

            const res = await fetch(`/map-search?${params.toString()}`, { headers: { 'Accept':'application/json' } });
            if(!res.ok) throw new Error('HTTP '+res.status);

            const json = await res.json();
            const items = json.data || [];
            const emergencies = json.emergencies || [];

            if(items.length===0){
                el.innerHTML = '<div class="text-sm text-gray-400">Belum ada partner yang cocok.</div>';
                if (map) {
                    partnerMarkers.forEach(m => map.removeLayer(m));
                    partnerMarkers = [];
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

            // Di bawah map hanya tampilkan 4 partner terdekat (preview)
            const previewTop = items.slice(0,4);

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

    let allPartnersPage = 1;
    let allPartnersHasMore = false;
    let isFetchingAllPartners = false;

    function handleAllPartnersScroll() {
        const container = document.getElementById('all-partners-scroll-container');
        if(!container) return;
        
        // If scrolled to bottom (within 50px)
        if(container.scrollHeight - container.scrollTop - container.clientHeight < 50) {
            if(allPartnersHasMore && !isFetchingAllPartners) {
                fetchAllPartners(allPartnersPage + 1, true);
            }
        }
    }

    function renderAllPartners(items, append = false){
        const listEl = document.getElementById('all-partners-list');
        const subtitleEl = document.getElementById('all-partners-subtitle');
        if(!listEl) return;

        if(!append && (!items || items.length === 0)){
            listEl.innerHTML = '<div class="text-sm text-gray-400">Belum ada partner yang cocok.</div>';
            if(subtitleEl) subtitleEl.textContent = '0 hasil untuk filter & pencarian saat ini.';
            return;
        }

        if(subtitleEl && !append){
            subtitleEl.textContent = `Menampilkan partner terdekat. Scroll ke bawah untuk memuat lebih banyak.`;
        }

        const currentCount = append ? listEl.querySelectorAll('a').length : 0;

        const html = items.map((x, i)=>{
            const p = x.partner;
            const km = Number(x.distance_km) || 0;
            return `
                <a href="/data-partner/${p.id}" class="block bg-white border border-gray-100 rounded-xl p-3 hover:bg-gray-50 transition">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-bold text-gray-900 text-sm truncate">${p.partner_name}</p>
                            <p class="text-xs text-gray-500 mt-1">${p.partner_type} • ${km.toFixed(2)} km</p>
                        </div>
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

    async function fetchAllPartners(page = 1, append = false) {
        if(isFetchingAllPartners) return;
        isFetchingAllPartners = true;
        allPartnersPage = page;
        
        const listEl = document.getElementById('all-partners-list');
        const t = allTypeEl?.value || '';
        const q = allQueryEl?.value || '';

        if(page === 1) {
            if(listEl) listEl.innerHTML = '<div class="text-sm text-gray-400">Memuat partner...</div>';
        } else {
            if(listEl) {
                const loadingEl = document.createElement('div');
                loadingEl.id = 'all-partners-loading-indicator';
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

            const res = await fetch(`/map-search?${params.toString()}`, { headers: { 'Accept':'application/json' } });
            if(!res.ok) throw new Error('HTTP '+res.status);
            const json = await res.json();
            const items = json.data || [];
            allPartnersHasMore = json.has_more || false;
            
            document.getElementById('all-partners-loading-indicator')?.remove();
            
            renderAllPartners(items, append);
            
            if(page === 1) window.__lastNearbyItems = items;
        }catch(e){
            document.getElementById('all-partners-loading-indicator')?.remove();
            if(page === 1 && listEl) {
                listEl.innerHTML = `<div class="text-sm text-red-700 bg-red-50 border border-red-200 rounded-xl px-3 py-2">Gagal memuat partner: ${String(e && e.message ? e.message : e)}</div>`;
            }
        }finally{
            isFetchingAllPartners = false;
        }
    }

    // open modal sync input ke filter map yang aktif
    btnViewAll && btnViewAll.addEventListener('click', async ()=>{
        const t = mapTypeEl?.value || '';
        const q = mapQueryEl?.value || '';

        if(allTypeEl) allTypeEl.value = t;
        if(allQueryEl) allQueryEl.value = q;

        openAllPartnersModal();

        fetchAllPartners(1, false);
    });

    // modal search realtime (gunakan API yang sama)
    let allDebounceTimer = null;
    const allTriggerSearch = ()=>{
        if(allDebounceTimer) clearTimeout(allDebounceTimer);
        allDebounceTimer = setTimeout(async ()=>{
            fetchAllPartners(1, false);
            // refresh map juga agar marker & preview sesuai modal
            const t = allTypeEl?.value || '';
            const q = allQueryEl?.value || '';
            loadNearbyPartners({ type: t, query: q });
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
    let step2Interval = null;
    let isEmergencySubmitting = false;
    let currentReportId = null;
    let step2Cd = 60;

    function handleCategoryTap(el) {
        document.getElementById('fallback-category').disabled=true;
        const radio = el.querySelector('input[type="radio"]');
        if (radio) radio.checked = true;
    }

    function startPanic(){
        openCatModal();
    }

    async function submitStep1() {
        if (isEmergencySubmitting) return;
        isEmergencySubmitting = true;
        
        if (autoSubmitInterval) clearInterval(autoSubmitInterval);

        const form = document.getElementById('emergency-form');
        const submitBtn = document.getElementById('btn-next-step');
        if(submitBtn) {
            submitBtn.innerHTML = 'MEMPROSES...';
            submitBtn.classList.add('opacity-70', 'cursor-not-allowed');
            submitBtn.disabled = true;
        }

        const radios = form.querySelectorAll('input[name="category"]');
        let checked = false;
        radios.forEach(r => {
            if(r.checked) checked = true;
            r.required = false;
        });
        if (!checked) {
            document.getElementById('fallback-category').disabled = false;
                            document.getElementById('fallback-category').value = 'ambulance';
        }

        const formData = new FormData(form);
        try {
            const response = await fetch('/emergency', {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            
            if (data.ok && data.report_id) {
                currentReportId = data.report_id;
                // Move to step 2
                document.getElementById('step-1').classList.add('hidden');
                document.getElementById('step-2').classList.remove('hidden');
                document.getElementById('step-2').classList.add('flex');
                
                // Start step 2 countdown
                startStep2Timer();
            }
        } catch (e) {
            console.error(e);
            HTMLFormElement.prototype.submit.call(form); // fallback
        }
    }
    
    function startStep2Timer() {
        if (step2Interval) clearInterval(step2Interval);
        step2Cd = 60;
        document.getElementById('step2-cd').textContent = step2Cd;
        step2Interval = setInterval(() => {
            step2Cd--;
            document.getElementById('step2-cd').textContent = step2Cd;
            if (step2Cd <= 0) {
                clearInterval(step2Interval);
                submitStep2();
            }
        }, 1000);
    }
    
    function resetStep2Timer() {
        if (step2Interval) {
            step2Cd = 60;
            document.getElementById('step2-cd').textContent = step2Cd;
        }
    }

    async function submitStep2() {
        if (step2Interval) clearInterval(step2Interval);
        const submitBtn = document.getElementById('btn-final-submit');
        submitBtn.innerHTML = 'MENYIMPAN...';
        submitBtn.disabled = true;
        
        const desc = document.getElementById('step2-desc').value;
        const showEvidence = document.getElementById('step2-show-evidence').checked ? '1' : '0';
        const fileInput = document.getElementById('step2-evidence');
        
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('description', desc);
        formData.append('show_evidence', showEvidence);
        
        if (fileInput.files.length > 0) {
            for (let i = 0; i < fileInput.files.length; i++) {
                formData.append('evidence[]', fileInput.files[i]);
            }
        }
        
        try {
            await fetch(`/tracking/${currentReportId}/evidence`, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            });
        } catch (e) {
            console.error(e);
        }
        
        window.location.href = `/tracking/${currentReportId}`;
    }

    function openCatModal(){
        document.getElementById('cat-modal').classList.remove('hidden');
        document.body.style.overflow='hidden';

        // Reset state
        isEmergencySubmitting = false;
        document.getElementById('step-1').classList.remove('hidden');
        document.getElementById('step-2').classList.add('hidden');
        document.getElementById('step-2').classList.remove('flex');
        
        const submitBtn = document.getElementById('btn-next-step');
        if(submitBtn) {
            submitBtn.innerHTML = 'SELANJUTNYA';
            submitBtn.classList.remove('opacity-70', 'cursor-not-allowed');
            submitBtn.disabled = false;
        }

        let cd = 30;
        document.getElementById('auto-submit-info').classList.remove('hidden');
        document.getElementById('auto-submit-cd').textContent = cd;
        if(autoSubmitInterval) clearInterval(autoSubmitInterval);
        if(step2Interval) clearInterval(step2Interval);
        
        autoSubmitInterval = setInterval(() => {
            cd--;
            document.getElementById('auto-submit-cd').textContent = cd;
            if (cd <= 0) {
                clearInterval(autoSubmitInterval);
                submitStep1();
            }
        }, 1000);
    }
    function closeCatModal(){
        document.getElementById('cat-modal').classList.add('hidden');
        document.body.style.overflow='';
        if (autoSubmitInterval) clearInterval(autoSubmitInterval);
        if (step2Interval) clearInterval(step2Interval);
    }
    document.getElementById('cat-modal').addEventListener('click',function(e){if(e.target===this)closeCatModal();});
    </script>
</body>
</html>
