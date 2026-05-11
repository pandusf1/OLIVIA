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

            {{-- ===== LEFT: Emergency + Laporan ===== --}}
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
                        class="panic-pulse w-full bg-red-700 hover:bg-red-800 text-white py-5 rounded-xl font-bold text-base tracking-wide transition flex items-center justify-center gap-3 relative z-10">
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
    if(navigator.geolocation){
        navigator.geolocation.getCurrentPosition(
            p=>{lat=p.coords.latitude;lng=p.coords.longitude;document.getElementById('f-lat').value=lat;document.getElementById('f-lng').value=lng;document.getElementById('loc-info').innerHTML='<div class="w-2 h-2 bg-green-500 rounded-full"></div><span class="text-green-600">Lokasi: '+lat.toFixed(4)+', '+lng.toFixed(4)+'</span>';},
            ()=>{document.getElementById('loc-info').innerHTML='<div class="w-2 h-2 bg-red-400 rounded-full"></div><span class="text-red-500">GPS tidak tersedia.</span>';}
        );
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
