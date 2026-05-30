<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Safora — Suara & Perlindungan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap');
        * { font-family: 'Inter', sans-serif; }
        @keyframes fadeUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
        .fade-up { animation: fadeUp 0.6s ease forwards; }
        .d1{animation-delay:0s} .d2{animation-delay:.15s} .d3{animation-delay:.3s}
    </style>
</head>
<body class="bg-[#faf9f7] text-gray-900 antialiased">

    {{-- NAV --}}
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white border-b border-gray-200">
        <div class="max-w-6xl mx-auto px-6 h-14 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-red-700 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v3a1 1 0 102 0V7z" clip-rule="evenodd"/></svg>
                </div>
                <div>
                    <span class="font-bold text-gray-900 text-sm">Safora</span>
                    <span class="text-gray-400 text-xs ml-1 hidden sm:inline">SUARA & PERLINDUNGAN</span>
                </div>
            </div>
            <div class="flex items-center gap-6">
                <a href="#cara-kerja" class="text-gray-600 hover:text-gray-900 text-sm transition hidden md:block">Cara Kerja</a>
                <a href="/witness" class="text-gray-600 hover:text-gray-900 text-sm transition hidden md:block">Saksi</a>
                <a href="/tracking-search" class="text-gray-600 hover:text-gray-900 text-sm transition hidden md:block">Cek Laporan</a>
                <a href="/emergency" class="text-gray-600 hover:text-gray-900 text-sm transition hidden md:block">Darurat</a>
                @auth
                <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900 text-sm transition">Dashboard</a>
                @else
                <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900 text-sm transition">Masuk</a>
                <a href="{{ route('register') }}" class="bg-gray-900 hover:bg-gray-700 text-white text-sm px-4 py-2 rounded-lg font-semibold transition">Daftar</a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- HERO --}}
    <section class="pt-14 min-h-screen flex items-center">
        <div class="max-w-6xl mx-auto px-6 w-full grid md:grid-cols-2 gap-12 items-center py-20">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-4 fade-up d1">PLATFORM UNTUK INDONESIA · TAHAP AWAL: SEMARANG</p>
                <h1 class="font-unbounded text-5xl md:text-6xl font-black text-gray-900 leading-tight mb-6 fade-up d2">
                    Saat dunia diam,<br>
                    <span class="text-red-700">Safora</span> jadi suaramu.
                </h1>
                <p class="text-gray-500 text-lg leading-relaxed mb-8 fade-up d3">
                    Platform respons darurat sosial & hukum berbasis web. Kirim laporan, simpan bukti otomatis, dan terhubung ke LBH, ambulans, atau psikolog terverifikasi — bahkan tanpa akun.
                </p>
                <div class="flex flex-col sm:flex-row gap-3 fade-up d3 mb-2">
                    <a href="/emergency" class="bg-red-700 hover:bg-red-800 text-white px-6 py-3 rounded-xl font-bold text-sm flex items-center gap-2 transition justify-center">
                        Kirim Laporan Darurat →
                    </a>
                    <a href="#cara-kerja" class="bg-white border border-gray-200 hover:border-gray-300 text-gray-700 px-6 py-3 rounded-xl font-semibold text-sm transition text-center">
                        Cara Kerja
                    </a>
                </div>
                <p class="text-gray-400 text-xs mt-2 fade-up d3">Safora adalah penghubung & pelindung bukti digital — bukan pengganti layanan darurat resmi pemerintah.</p>
            </div>
            
            <div class="w-full fade-up d3">
                <div class="flex flex-col items-center justify-center p-6 bg-white border border-gray-200/80 rounded-3xl shadow-xl shadow-gray-200/50 relative overflow-hidden">
                    {{-- Glowing backgrounds --}}
                    <div class="absolute -top-24 -left-24 w-48 h-48 bg-red-100 rounded-full blur-3xl opacity-50"></div>
                    <div class="absolute -bottom-24 -right-24 w-48 h-48 bg-red-50 rounded-full blur-3xl opacity-50"></div>

                    <div class="relative z-10 text-center flex flex-col items-center py-6 w-full">
                        <div class="relative mb-8">
                            {{-- Pulsing halos --}}
                            <div class="absolute inset-0 bg-red-100 rounded-full scale-[1.3] opacity-60 animate-ping" style="animation-duration: 2.5s;"></div>
                            <div class="absolute inset-0 bg-red-200 rounded-full scale-[1.15] opacity-80 animate-pulse pointer-events-none"></div>
                            
                            <a href="/emergency" class="relative z-10 w-40 h-40 sm:w-48 sm:h-48 bg-red-700 hover:bg-red-800 text-white rounded-full flex flex-col items-center justify-center shadow-[0_10px_40px_rgba(185,28,28,0.35)] transition-all hover:scale-105 active:scale-95 group">
                                <svg class="w-12 h-12 text-red-100 mb-1.5 animate-bounce" fill="currentColor" viewBox="0 0 20 20" style="animation-duration: 2s;"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                <span class="font-bold text-sm tracking-widest text-red-100">DARURAT</span>
                                <span class="text-[9px] font-semibold text-red-200 uppercase tracking-widest mt-0.5">Anonim & Guest</span>
                            </a>
                        </div>
                        
                        <h3 class="font-bold text-gray-900 text-base mb-1">Respons Cepat Tanpa Akun</h3>
                        <p class="text-gray-500 text-xs max-w-xs leading-relaxed px-4">
                            Satu klik untuk mengaktifkan pelacak GPS, mengamankan bukti darurat, dan terhubung ke mitra — <strong>100% rahasia & tanpa login</strong>.
                        </p>
                        
                        <div class="mt-5 flex flex-wrap justify-center gap-2 text-[10px] font-semibold text-red-700 bg-red-50/70 px-4 py-2.5 rounded-2xl border border-red-100 max-w-xs">
                            <span class="flex items-center gap-1">🔒 Enkripsi Bukti</span>
                            <span class="text-red-300">•</span>
                            <span class="flex items-center gap-1">📍 Pelacakan GPS</span>
                            <span class="text-red-300">•</span>
                            <span class="flex items-center gap-1">👤 Anonimitas</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 3 ISU --}}
    <section class="bg-white py-20 px-6">
        <div class="max-w-6xl mx-auto">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-4">MENGAPA Safora</p>
            <h2 class="text-4xl font-black text-gray-900 mb-12">Tiga isu, satu platform.</h2>
            <div class="grid md:grid-cols-3 gap-6">
                @foreach([
                    ['⚖️','Salah tangkap & penyalahgunaan wewenang','Korban takut, bukti hilang, keluarga bingung. Safora mengamankan bukti & menghubungkan ke LBH terverifikasi.'],
                    ['🛡️','Pelecehan & kekerasan','Mode anonim melindungi identitas. Hanya LBH terverifikasi yang akses. Psikolog tersedia via smart routing.'],
                    ['🚑','Darurat publik','Kecelakaan, butuh ambulans cepat, warga sekitar tidak tahu kontak darurat. Safora routing otomatis.'],
                ] as [$icon, $title, $desc])
                <div class="border border-gray-200 rounded-2xl p-6 hover:border-gray-300 transition">
                    <p class="text-2xl mb-4">{{ $icon }}</p>
                    <h3 class="font-bold text-gray-900 text-lg mb-2">{{ $title }}</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">{{ $desc }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CARA KERJA --}}
    <section id="cara-kerja" class="bg-[#faf9f7] py-20 px-6">
        <div class="max-w-6xl mx-auto">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-4">CARA KERJA</p>
            <h2 class="text-4xl font-black text-gray-900 mb-12">Tiga langkah. Selesai.</h2>
            <div class="grid md:grid-cols-3 gap-6">
                @foreach([
                    ['1','Tekan tombol darurat','Satu klik. Countdown 10 detik dengan opsi batal. GPS & waktu otomatis terekam.'],
                    ['2','Pilih kategori kejadian','Kekerasan, kesehatan, pelecehan, kecelakaan, ancaman, atau lainnya. Smart routing bekerja otomatis.'],
                    ['3','Laporan terkirim & terlindungi','Mitra yang tepat menerima laporan. Saksi sekitar dapat berkontribusi. Bukti digital tersimpan aman.'],
                ] as [$num, $title, $desc])
                <div class="bg-white border border-gray-200 rounded-2xl p-6">
                    <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center font-black text-gray-700 text-sm mb-4">{{ $num }}</div>
                    <h3 class="font-bold text-gray-900 text-lg mb-2">{{ $title }}</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">{{ $desc }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- FITUR --}}
    <section class="bg-white py-20 px-6">
        <div class="max-w-6xl mx-auto">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-4">FITUR UTAMA</p>
            <h2 class="text-4xl font-black text-gray-900 mb-12">Dirancang untuk keadaan darurat.</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach([
                    ['🚨','Quick Emergency','Panic button → countdown → kategori → kirim. GPS otomatis. WhatsApp alert ke kontak terpercaya & pengguna sekitar.'],
                    ['🔒','Auto Evidence Locker','SHA-256 hash, timestamp, GPS pada setiap bukti. Aman meski HP korban disita atau rusak.'],
                    ['👁️','Anonymous Mode','Identitas bersifat opsional. Hanya mitra penangan terverifikasi yang bisa mengakses data korban.'],
                    ['👥','Community Witness','Saksi di sekitar kejadian bisa mengunggah bukti tambahan lewat kode laporan unik.'],
                    ['🧭','Smart Routing','Laporan darurat diarahkan otomatis ke mitra terdekat sesuai spesialisasi penanganan krisis.'],
                    ['🔢','Stealth Mode','Sembunyikan layar sekali klik ke tampilan kalkulator jika situasi di sekitar membahayakan.'],
                ] as [$icon, $title, $desc])
                <div class="border border-gray-200 rounded-2xl p-5 hover:border-gray-300 transition">
                    <p class="text-xl mb-3">{{ $icon }}</p>
                    <h3 class="font-bold text-gray-900 mb-1">{{ $title }}</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">{{ $desc }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="bg-gray-900 py-20 px-6">
        <div class="max-w-3xl mx-auto text-center">
            <h2 class="text-4xl font-black text-white mb-4">Siap ketika kamu butuhkan.</h2>
            <p class="text-gray-400 mb-8">Tidak perlu login. Tidak perlu bayar. Fokus awal: Semarang & sekitarnya.</p>
            <a href="/emergency" class="inline-block bg-red-700 hover:bg-red-800 text-white px-8 py-4 rounded-xl font-bold text-lg transition">
                Kirim Laporan Sekarang →
            </a>
        </div>
    </section>

    {{-- FOOTER --}}
    <footer class="bg-white border-t border-gray-200 py-8 px-6">
        <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 bg-red-700 rounded flex items-center justify-center">
                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944z" clip-rule="evenodd"/></svg>
                </div>
                <span class="font-bold text-gray-900 text-sm">Safora</span>
                <span class="text-gray-400 text-sm">— Suara & Perlindungan Rakyat</span>
            </div>
            <div class="flex gap-6 text-sm text-gray-500">
                <a href="/witness" class="hover:text-gray-900 transition">Jadi Saksi</a>
                <a href="/tracking-search" class="hover:text-gray-900 transition">Cek Laporan</a>
                @guest<a href="{{ route('login') }}" class="hover:text-gray-900 transition">Masuk</a>@endguest
            </div>
        </div>
    </footer>

</body>
</html>
