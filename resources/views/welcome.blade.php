<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Safora</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap');
        html { scroll-behavior: smooth; }
        * { font-family: 'Inter', sans-serif; }
        @keyframes fadeUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
        .fade-up { animation: fadeUp 0.6s ease forwards; }
        .d1{animation-delay:0s} .d2{animation-delay:.15s} .d3{animation-delay:.3s}
        @keyframes modalIn { from{opacity:0;transform:scale(.95)} to{opacity:1;transform:scale(1)} }
        .modal-in { animation: modalIn 0.25s ease forwards; }
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
                <a href="/tracking-search" class="text-gray-600 hover:text-gray-900 text-sm transition hidden md:block">Cek Laporan</a>
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
    <section id="hero-section" class="sm:pt-12 pt-11 min-h-screen flex items-center bg-[#faf9f7] relative overflow-hidden">

        <div class="max-w-xl md:max-w-5xl mx-auto px-5 w-full flex flex-col items-center text-center pt-10 pb-8">

            {{-- Headline --}}
            <h1 class="text-4xl md:text-6xl font-black text-gray-900 leading-tight mb-3 fade-up d1">
                Saat dunia diam,<br>
                <span class="text-red-700">Safora</span> jadi suaramu.
            </h1>

            <p class="text-gray-500 text-sm md:text-base leading-relaxed mb-8 max-w-sm md:max-w-lg fade-up d1">
                Kirim laporan, simpan bukti otomatis, terhubung ke LBH,<br class="hidden md:block">
                ambulans, atau psikolog — bahkan tanpa akun.
            </p>

            {{-- Emergency Button --}}
            <div class="relative flex items-center justify-center mb-5 fade-up d2">
                {{-- Halo rings --}}
                <div class="absolute w-64 h-64 md:w-80 md:h-80 bg-red-100 rounded-full animate-ping opacity-25" style="animation-duration: 2.5s;"></div>
                <div class="absolute w-52 h-52 md:w-64 md:h-64 bg-red-200 rounded-full animate-pulse opacity-20 pointer-events-none"></div>

                <button id="btn-darurat" onclick="openEmergencyModal()"
                   class="relative z-10 w-48 h-48 md:w-60 md:h-60 bg-red-700 hover:bg-red-800 text-white rounded-full flex flex-col items-center justify-center shadow-[0_20px_60px_rgba(185,28,28,0.4)] transition-all hover:scale-105 active:scale-95 cursor-pointer">
                    <svg class="w-12 h-12 md:w-14 md:h-14 text-red-100 mb-2 animate-bounce" fill="currentColor" viewBox="0 0 20 20" style="animation-duration: 2s;"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <span class="font-black text-lg md:text-xl tracking-widest text-red-100">DARURAT</span>
                </button>
            </div>

            {{-- Status indicator --}}
            <p class="flex items-center gap-1.5 text-gray-500 text-sm mb-1 fade-up d2">
                <span class="w-2 h-2 bg-green-400 rounded-full inline-block"></span>
                Anonim
            </p>

            {{-- Divider --}}
            <div class="w-full flex items-center gap-4 mb-6 fade-up d3">
                <div class="flex-1 h-px bg-gray-200"></div>
                <div class="flex-1 h-px bg-gray-200"></div>
            </div>

            {{-- Service Cards --}}
            <div class="w-full grid grid-cols-2 md:grid-cols-4 gap-3 fade-up md:hidden">
                
                {{-- Cara Kerja --}}
                <a href="#cara-kerja" class="group bg-white hover:bg-gray-50 border border-gray-200 hover:border-gray-300 rounded-2xl p-4 md:p-5 text-left transition-all">
                    <div class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center mb-1">
                        <svg class="w-6 h-6 md:w-6 md:h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="font-bold text-gray-900 text-sm mb-1">Cara kerja</p>
                    <p class="text-gray-500 text-xs leading-relaxed">Pelajari alur Safora</p>
                </a>

                {{-- Cek Laporan --}}
                <a href="/tracking-search" class="group bg-white hover:bg-gray-50 border border-gray-200 hover:border-gray-300 rounded-2xl p-4 md:p-5 text-left transition-all">
                    <div class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center mb-1">
                        <svg class="w-6 h-6 md:w-6 md:h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <p class="font-bold text-gray-900 text-sm mb-1">Cek laporan</p>
                    <p class="text-gray-500 text-xs leading-relaxed">Lacak status laporanmu</p>
                </a>

            </div>

        </div>
    </section>

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
                    <p class="text-gray-400 text-xs mt-1">Pilih kategori — laporan dikirim anonim secara otomatis.</p>
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
                    <p class="text-xs text-gray-400 mt-2">Laporan dikirim anonim — tidak perlu akun.</p>
                    <button onclick="cancelEmergency()" class="mt-4 text-sm text-gray-400 hover:text-gray-600 underline transition">Batalkan</button>
                </div>
            </div>

        </div>
    </div>

    {{-- CARA KERJA INTERAKTIF --}}
    <section id="cara-kerja" class="bg-[#fcfbf9] py-24 px-6 border-y border-gray-200">
        <div class="max-w-6xl mx-auto">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <p class="text-xs font-bold uppercase tracking-widest text-red-600 mb-3">ALUR PERLINDUNGAN</p>
                <h2 class="text-2xl md:text-3xl font-black text-gray-900 leading-tight">Bagaimana Safora Melindungi Anda?</h2>
                <p class="text-gray-500 text-sm mt-3">Pelajari alur perlindungan instan saat Anda mengaktifkan mode darurat.</p>
            </div>

            <div class="grid lg:grid-cols-12 gap-8 items-stretch">
                {{-- KIRI: Langkah-langkah interaktif --}}
                <div class="lg:col-span-5 flex flex-col gap-3 justify-center">
                    {{-- Step 1 --}}
                    <button type="button" onclick="switchWorkStep(1)" id="work-step-btn-1" class="work-step-btn text-left p-5 rounded-2xl border transition-all duration-300 bg-white border-red-600 shadow-md group">
                        <div class="flex items-start gap-4">
                            <span class="work-step-num font-mono text-2xl font-black text-red-700 transition">01</span>
                            <div>
                                <h3 class="font-bold text-gray-900 text-base mb-1 group-hover:text-red-700 transition">Lapor Cepat (GPS & Akun)</h3>
                                <p class="text-gray-500 text-xs leading-relaxed">Satu tombol langsung melacak posisi Anda. Bisa dikirim tanpa akun (anonim/rahasia) atau otomatis terhubung ke profil akun Anda.</p>
                            </div>
                        </div>
                    </button>

                    {{-- Step 2 --}}
                    <button type="button" onclick="switchWorkStep(2)" id="work-step-btn-2" class="work-step-btn text-left p-5 rounded-2xl border transition-all duration-300 bg-transparent border-transparent hover:bg-gray-100/50 group">
                        <div class="flex items-start gap-4">
                            <span class="work-step-num font-mono text-2xl font-black text-gray-400 group-hover:text-red-600 transition">02</span>
                            <div>
                                <h3 class="font-bold text-gray-900 text-base mb-1 group-hover:text-red-700 transition">Telepon Instansi Terdekat</h3>
                                <p class="text-gray-500 text-xs leading-relaxed">Aplikasi telepon di HP langsung terbuka dan otomatis terisi nomor layanan darurat terdekat yang sesuai (seperti Ambulans 119 atau Damkar).</p>
                            </div>
                        </div>
                    </button>

                    {{-- Step 3 --}}
                    <button type="button" onclick="switchWorkStep(3)" id="work-step-btn-3" class="work-step-btn text-left p-5 rounded-2xl border transition-all duration-300 bg-transparent border-transparent hover:bg-gray-100/50 group">
                        <div class="flex items-start gap-4">
                            <span class="work-step-num font-mono text-2xl font-black text-gray-400 group-hover:text-red-600 transition">03</span>
                            <div>
                                <h3 class="font-bold text-gray-900 text-base mb-1 group-hover:text-red-700 transition">Hubungi Warga & Keluarga</h3>
                                <p class="text-gray-500 text-xs leading-relaxed">Sistem otomatis mengirim pesan WhatsApp darurat ke warga sekitar (radius 10 km) dan keluarga terdekat untuk pertolongan pertama.</p>
                            </div>
                        </div>
                    </button>

                    {{-- Step 4 --}}
                    <button type="button" onclick="switchWorkStep(4)" id="work-step-btn-4" class="work-step-btn text-left p-5 rounded-2xl border transition-all duration-300 bg-transparent border-transparent hover:bg-gray-100/50 group">
                        <div class="flex items-start gap-4">
                            <span class="work-step-num font-mono text-2xl font-black text-gray-400 group-hover:text-red-600 transition">04</span>
                            <div>
                                <h3 class="font-bold text-gray-900 text-base mb-1 group-hover:text-red-700 transition">Hubungkan Petugas Resmi</h3>
                                <p class="text-gray-500 text-xs leading-relaxed">Laporan diteruskan ke dinas atau petugas penyelamat terdekat dalam radius 20 km agar segera ditangani secara resmi.</p>
                            </div>
                        </div>
                    </button>
                </div>

                {{-- KANAN: Visualisasi Panel Dinamis --}}
                <div class="lg:col-span-7 bg-white border border-gray-200 rounded-3xl p-6 sm:p-8 shadow-sm flex flex-col justify-between min-h-[360px] relative overflow-hidden">
                    <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-red-500/5 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="absolute -left-10 -top-10 w-48 h-48 bg-purple-500/5 rounded-full blur-3xl pointer-events-none"></div>

                    {{-- Content Step 1 --}}
                    <div id="work-pane-1" class="work-pane space-y-4">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                            <span class="text-xs font-bold text-red-700 px-2.5 py-1 bg-red-50 rounded-full">Kirim Lokasi</span>
                            <span class="text-xs text-gray-400">GPS Aktif</span>
                        </div>
                        <div class="space-y-3">
                            <p class="text-sm font-semibold text-gray-900">Mencatat Lokasi GPS & Akun Anda</p>
                            <p class="text-gray-500 text-xs leading-relaxed">Sistem mencatat lokasi GPS Anda dengan akurat. Jika belum masuk akun, laporan terkirim sebagai tamu rahasia (anonim). Jika sudah masuk akun, identitas Anda otomatis terhubung agar memudahkan petugas mengenali Anda.</p>
                            
                            {{-- Mockup UI --}}
                            <div class="bg-gray-50 rounded-xl p-4 border border-gray-150 space-y-2 mt-2">
                                <div class="flex items-center gap-2">
                                    <div class="w-2.5 h-2.5 bg-red-600 rounded-full animate-ping"></div>
                                    <span class="text-xs font-bold text-gray-800">Menghubungkan GPS...</span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-[11px] pt-1">
                                    <div class="bg-white border border-red-500 text-red-700 font-bold p-2.5 rounded-lg text-center shadow-sm">👤 Akun Terhubung<br><span class="text-[9px] text-gray-400 font-normal">Identitas Tercatat</span></div>
                                    <div class="bg-white border border-gray-200 text-gray-500 p-2.5 rounded-lg text-center opacity-65">🕶️ Tamu (Anonim)<br><span class="text-[9px] text-gray-400 font-normal">Identitas Rahasia</span></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Content Step 2 --}}
                    <div id="work-pane-2" class="work-pane space-y-4 hidden">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                            <span class="text-xs font-bold text-red-700 px-2.5 py-1 bg-red-50 rounded-full">Telepon Darurat</span>
                            <span class="text-xs text-gray-400">Panggilan Langsung</span>
                        </div>
                        <div class="space-y-3">
                            <p class="text-sm font-semibold text-gray-900">Layanan Telepon Siap Panggil</p>
                            <p class="text-gray-500 text-xs leading-relaxed">Sistem memilih nomor layanan darurat terbaik berdasarkan jenis kejadian yang Anda laporkan. Tombol panggil di HP Anda akan langsung terisi nomor tersebut secara otomatis.</p>
                            
                            {{-- Mockup UI --}}
                            <div class="bg-gray-900 text-white rounded-xl p-4 border border-gray-800 flex items-center justify-between mt-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 bg-green-500 rounded-full flex items-center justify-center animate-pulse">
                                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-400 uppercase tracking-wider">Memanggil Layanan</p>
                                        <p class="text-sm font-bold text-white">Layanan Ambulans 119</p>
                                    </div>
                                </div>
                                <span class="text-xs text-red-400 font-mono animate-pulse font-bold">Panggil...</span>
                            </div>
                        </div>
                    </div>

                    {{-- Content Step 3 --}}
                    <div id="work-pane-3" class="work-pane space-y-4 hidden">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                            <span class="text-xs font-bold text-red-700 px-2.5 py-1 bg-red-50 rounded-full">Mengabari Sekitar</span>
                            <span class="text-xs text-gray-400">Pesan WhatsApp</span>
                        </div>
                        <div class="space-y-3">
                            <p class="text-sm font-semibold text-gray-900">Bantuan Cepat dari Warga Terdekat & Keluarga</p>
                            <p class="text-gray-500 text-xs leading-relaxed">Sistem mengirimkan pesan bantuan darurat secara otomatis ke masyarakat terdekat dalam radius 10 km dan keluarga Anda melalui WhatsApp, agar pertolongan pertama bisa langsung datang.</p>
                            
                            {{-- Mockup UI --}}
                            <div class="bg-[#e5ddd5] rounded-xl p-3 border border-gray-300 space-y-2 text-xs mt-2 relative">
                                <div class="bg-[#dcf8c6] rounded-lg p-2.5 shadow-sm max-w-[85%] border border-gray-250/30">
                                    <p class="font-bold text-[10px] text-green-800 mb-0.5">🚨 Safora Emergency Alert</p>
                                    <p class="text-gray-800 text-[11px] leading-snug">*BUTUH BANTUAN!* Seseorang membutuhkan pertolongan darurat dekat lokasi Anda (radius &lt; 10km). Tolong bantu korban di lokasi berikut: https://safora.id/tracking/e439</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Content Step 4 --}}
                    <div id="work-pane-4" class="work-pane space-y-4 hidden">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                            <span class="text-xs font-bold text-red-700 px-2.5 py-1 bg-red-50 rounded-full">Penanganan Resmi</span>
                            <span class="text-xs text-gray-400">Petugas Terdekat &lt; 20 Km</span>
                        </div>
                        <div class="space-y-3">
                            <p class="text-sm font-semibold text-gray-900">Diteruskan Langsung ke Lembaga Penyelamat</p>
                            <p class="text-gray-500 text-xs leading-relaxed">Laporan diteruskan ke instansi penyelamat terdekat (Pemadam Kebakaran, Bantuan Hukum, PPPA, dll.) dalam radius maksimal 20 km. Anda dan keluarga dapat memantau status penanganan secara langsung.</p>
                            
                            {{-- Mockup UI --}}
                            <div class="bg-white rounded-xl p-3.5 border border-gray-200 shadow-sm space-y-2 mt-2">
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="text-gray-500">Lembaga Penolong:</span>
                                    <span class="font-bold text-gray-900">Pemadam Kebakaran & Rescue</span>
                                </div>
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="text-gray-500">Status Laporan:</span>
                                    <span class="font-bold text-green-700 bg-green-50 px-2 py-0.5 rounded">Petugas Menuju Lokasi (3.2 km)</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-red-600 h-full rounded-full w-2/3"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Footer/CTA --}}
                    <div class="border-t border-gray-100 pt-4 flex items-center justify-between text-xs text-gray-400 mt-4">
                        <span>Safora System Terintegrasi</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- MENGAPA --}}
    <section class="bg-white py-20 px-6">
        <div class="max-w-6xl mx-auto">
            <p class="text-xs font-semibold uppercase tracking-widest text-red-600 mb-4">MENGAPA Safora</p>
            <h2 class="text-2xl md:text-3xl font-black text-gray-900 mb-3">Tiga isu, satu platform.</h2>
            <div class="grid md:grid-cols-3 gap-6">
                <div class="border border-gray-200 rounded-2xl p-6 hover:border-orange-200 hover:bg-orange-50/30 transition group">
                    <div class="w-12 h-12 flex items-center justify-center mb-4">
                        <span class="text-2xl">🧭</span>
                    </div>
                    <h3 class="font-bold text-gray-900 text-lg mb-2">Kesulitan Menghubungi Instansi</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Safora langsung menyalurkan laporan Anda ke instansi darurat terdekat secara otomatis. *Masuk akun untuk menyimpan riwayat laporan permanen.*</p>
                </div>
                <div class="border border-gray-200 rounded-2xl p-6 hover:border-orange-200 hover:bg-orange-50/30 transition group">
                    <div class="w-12 h-12 flex items-center justify-center mb-4">
                        <span class="text-2xl">🛡️</span>
                    </div>
                    <h3 class="font-bold text-gray-900 text-lg mb-2">Kekhawatiran Keamanan Identitas</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Laporan terkirim secara rahasia (anonim) tanpa perlu daftar akun. *Masuk akun untuk membuka akses ke obrolan komunitas.*</p>
                </div>
                <div class="border border-gray-200 rounded-2xl p-6 hover:border-orange-200 hover:bg-orange-50/30 transition group">
                    <div class="w-12 h-12 flex items-center justify-center mb-4">
                        <span class="text-2xl">🚑</span>
                    </div>
                    <h3 class="font-bold text-gray-900 text-lg mb-2">Keterlambatan Penanganan Darurat</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Warga sekitar otomatis menerima pesan bantuan WhatsApp untuk menolong Anda. *Masuk akun untuk langsung mendaftarkan kontak keluarga.*</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Script untuk Tab Interaktif Cara Kerja --}}
    <script>
        function switchWorkStep(step) {
            // Remove active style from all buttons
            document.querySelectorAll('.work-step-btn').forEach(btn => {
                btn.classList.remove('bg-white', 'shadow-md', 'border-red-600');
                btn.classList.add('bg-transparent', 'border-transparent', 'hover:bg-gray-100/50');
            });
            document.querySelectorAll('.work-step-num').forEach(num => {
                num.classList.remove('text-red-700');
                num.classList.add('text-gray-400');
            });

            // Add active style to selected button
            const activeBtn = document.getElementById('work-step-btn-' + step);
            if (activeBtn) {
                activeBtn.classList.remove('bg-transparent', 'border-transparent', 'hover:bg-gray-100/50');
                activeBtn.classList.add('bg-white', 'shadow-md', 'border-red-600');
                
                const activeNum = activeBtn.querySelector('.work-step-num');
                if (activeNum) {
                    activeNum.classList.remove('text-gray-400');
                    activeNum.classList.add('text-red-700');
                }
            }

            // Hide all panels
            document.querySelectorAll('.work-pane').forEach(pane => {
                pane.classList.add('hidden');
            });

            // Show active panel
            const activePane = document.getElementById('work-pane-' + step);
            if (activePane) {
                activePane.classList.remove('hidden');
            }
        }
    </script>

    {{-- FITUR --}}
    <section class="bg-white py-20 px-6">
        <div class="max-w-6xl mx-auto">
            <p class="text-xs font-semibold uppercase tracking-widest text-red-600 mb-4">FITUR UTAMA</p>
            <h2 class="text-2xl md:text-3xl font-black text-gray-900 mb-3">Dirancang untuk keadaan darurat.</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="border border-gray-200 rounded-2xl p-5 hover:border-red-200 hover:bg-red-50/20 transition">
                    <div class="w-10 h-10 flex items-center justify-center mb-1">
                        <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1">Quick Emergency</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Panic button → countdown → kategori → kirim. GPS otomatis. WhatsApp alert ke kontak terpercaya & pengguna sekitar.</p>
                </div>
                <div class="border border-gray-200 rounded-2xl p-5 hover:border-red-200 hover:bg-red-50/20 transition">
                    <div class="w-10 h-10 flex items-center justify-center mb-1">
                        <svg class="w-6 h-6 text-gray-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1">Auto Evidence Locker</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Setiap bukti yang diunggah otomatis mendapat SHA-256 hash + timestamp + koordinat GPS.</p>
                </div>
                <div class="border border-gray-200 rounded-2xl p-5 hover:border-red-200 hover:bg-red-50/20 transition">
                    <div class="w-10 h-10 flex items-center justify-center mb-1">
                        <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1">Mode Anonim & Guest</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Identitas bersifat opsional. Hanya mitra penangan yang bisa mengakses data korban.</p>
                </div>
                <div class="border border-gray-200 rounded-2xl p-5 hover:border-red-200 hover:bg-red-50/20 transition">
                    <div class="w-10 h-10 flex items-center justify-center mb-1">
                        <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1">Saksi Komunitas</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Warga di sekitar kejadian bisa mengunggah bukti tambahan menggunakan kode laporan atau laporan di map dashboard.</p>
                </div>
                <div class="border border-gray-200 rounded-2xl p-5 hover:border-red-200 hover:bg-red-50/20 transition">
                    <div class="w-10 h-10 flex items-center justify-center mb-1">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1">Smart Routing Mitra</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Laporan darurat diarahkan otomatis ke mitra terdekat sesuai spesialisasi penanganan.</p>
                </div>
                <div class="border border-gray-200 rounded-2xl p-5 hover:border-red-200 hover:bg-red-50/20 transition">
                    <div class="w-10 h-10 flex items-center justify-center mb-1">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1">Live Tracking Laporan</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Halaman laporan tracking real-time yang dibagikan ke whatsapp kontak terpercaya untuk alert dan memantau status penanganan.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class=" py-20 px-6">
        <div class="max-w-3xl mx-auto text-center">
            <h2 class="text-2xl md:text-3xl font-black mb-4">Siap ketika kamu butuhkan.</h2>
            <p class="text-gray-400 mb-8">Tidak perlu login. Tidak perlu bayar.</p>
            <a href="#hero-section" class="inline-block bg-red-700 hover:bg-red-800 text-white px-8 py-4 rounded-xl font-bold text-lg transition">
                Kirim Laporan Sekarang →
            </a>
        </div>
    </section>

    {{-- FOOTER --}}
    <footer class="bg-white border-t border-gray-200 py-8 px-6">
        <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between">
            <div class="flex items-center gap-1">
                <span class="text-gray-600 text-md">©</span>
                <span id="year" class="text-gray-600 text-sm"></span>
                <span class="text-gray-600 text-sm">Nexi Team</span>
                <span class="text-gray-600 text-sm">— All rights reserved.</span>
            </div>
        </div>
    </footer>

    @if(isset($activeReport) && $activeReport)
    {{-- FLOATING ACTIVE REPORT BUBBLE FOR GUEST REPORTERS --}}
    <div id="floating-report-bubble" class="fixed bottom-6 right-6 z-50" style="animation-duration: 3s;">
        <a href="/tracking/{{ $activeReport->id }}" 
           title="Lacak Laporan Aktif"
           class="w-14 h-14 bg-[#0f172a] hover:bg-[#1e293b] text-white rounded-full shadow-[0_12px_40px_rgba(15,23,42,0.35)] transition-all duration-300 hover:scale-105 active:scale-95 group border border-slate-800 relative flex items-center justify-center">
            
            {{-- Pulsing Red Indicator Dot --}}
            <span class="absolute top-0 right-0 flex h-4 w-4">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-4 w-4 bg-red-600 border-2 border-white"></span>
            </span>

            {{-- SVG Report Document Icon --}}
            <svg class="w-6 h-6 text-white group-hover:rotate-12 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </a>
    </div>
    @endif

</body>
</html>

<script>
  document.getElementById("year").textContent = new Date().getFullYear();
</script>

<script>
// ─── Emergency Modal Logic ───────────────────────────────────────────────────

const csrf = document.querySelector('meta[name="csrf-token"]').content;
const isLoggedIn = @json(auth()->check());
window.hasActiveReport = @json($activeReport !== null);
let locationPayload = { latitude: null, longitude: null };
let locationPromise = Promise.resolve();
let isSubmitting = false;
let selectedCategory = null;

function openEmergencyModal() {
    if (!isLoggedIn && window.hasActiveReport) {
        alert("Tidak bisa membuat laporan baru. Mohon login untuk membuat lebih dari 1 laporan.");
        return;
    }
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

    // Reset all category buttons to inactive
    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.classList.remove('border-red-600', 'bg-red-50', 'text-red-700');
        btn.classList.add('bg-gray-50', 'border-gray-200');
        const nameSpan = btn.querySelector('.category-name');
        if (nameSpan) {
            nameSpan.classList.remove('text-red-700');
            nameSpan.classList.add('text-gray-900');
        }
    });

    // Mark current category button as active
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

    // Switch to step 2
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

    // Reset all category buttons to inactive
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
        latitude: locationPayload.latitude,
        longitude: locationPayload.longitude,
        anonymous: 1,
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

        // Save report ID directly to localStorage before redirecting to support transient sessions/cookies on mobile
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
            // Pass phone number to tracking page to avoid timing/redirect race conditions on mobile/deployed sites
            try {
                const urlObj = new URL(redirectUrl, window.location.origin);
                urlObj.searchParams.set('call', data.call_phone);
                redirectUrl = urlObj.toString();
            } catch (e) {
                redirectUrl += (redirectUrl.indexOf('?') !== -1 ? '&' : '?') + 'call=' + encodeURIComponent(data.call_phone);
            }
        }
        window.location.href = redirectUrl;
    } catch (e) {
        document.getElementById('modal-status').textContent = e.message;
        isSubmitting = false;
        alert(e.message);
        closeEmergencyModal();
    }
}

// Sinkronisasi otomatis laporan aktif via localStorage di landing page (self-healing pasca login/logout)
document.addEventListener('DOMContentLoaded', () => {
    let storedReports = JSON.parse(localStorage.getItem('safora_guest_reports') || '[]');
    if (storedReports.length > 0) {
        fetch('/tracking/active-check?ids=' + encodeURIComponent(storedReports.join(',')), {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.active_report_id) {
                window.hasActiveReport = true;
                // Gambar ulang gelembung jika belum ada di DOM (misal session kosong habis logout)
                if (!document.getElementById('floating-report-bubble')) {
                    const bubble = document.createElement('div');
                    bubble.id = 'floating-report-bubble';
                    bubble.className = 'fixed bottom-6 right-6 z-50';
                    bubble.innerHTML = `
                        <a href="/tracking/${data.active_report_id}" 
                           title="Lacak Laporan Aktif"
                           class="w-14 h-14 bg-[#0f172a] hover:bg-[#1e293b] text-white rounded-full shadow-[0_12px_40px_rgba(15,23,42,0.35)] transition-all duration-300 hover:scale-105 active:scale-95 group border border-slate-800 relative flex items-center justify-center">
                            
                            <span class="absolute top-0 right-0 flex h-4 w-4">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-4 w-4 bg-red-600 border-2 border-white"></span>
                            </span>

                            <svg class="w-6 h-6 text-white group-hover:rotate-12 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </a>
                    `;
                    document.body.appendChild(bubble);
                }
            } else {
                window.hasActiveReport = false;
                // Laporan sudah resolved / tidak aktif, bersihkan localStorage & hapus bubble
                localStorage.removeItem('safora_guest_reports');
                const existingBubble = document.getElementById('floating-report-bubble');
                if (existingBubble) {
                    existingBubble.remove();
                }
            }
        })
        .catch(err => console.error('Active check failed:', err));
    }
});
</script>
