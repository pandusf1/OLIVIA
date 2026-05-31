<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Safora — Suara & Perlindungan</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap');
        * { font-family: 'Inter', sans-serif; }
        @keyframes fadeUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
        .fade-up { animation: fadeUp 0.6s ease forwards; }
        .d1{animation-delay:0s} .d2{animation-delay:.15s} .d3{animation-delay:.3s}
        @keyframes modalIn { from{opacity:0;transform:scale(.95)} to{opacity:1;transform:scale(1)} }
        .modal-in { animation: modalIn 0.25s ease forwards; }
    </style>
</head>
<body class="bg-[#faf9f7] text-gray-900 antialiased">

    
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
                <?php if(auth()->guard()->check()): ?>
                <a href="<?php echo e(route('dashboard')); ?>" class="text-gray-600 hover:text-gray-900 text-sm transition">Dashboard</a>
                <?php else: ?>
                <a href="<?php echo e(route('login')); ?>" class="text-gray-600 hover:text-gray-900 text-sm transition">Masuk</a>
                <a href="<?php echo e(route('register')); ?>" class="bg-gray-900 hover:bg-gray-700 text-white text-sm px-4 py-2 rounded-lg font-semibold transition">Daftar</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    
    <section class="sm:pt-12 pt-11 min-h-screen flex items-center bg-[#faf9f7] relative overflow-hidden">

        <div class="max-w-xl md:max-w-5xl mx-auto px-5 w-full flex flex-col items-center text-center pt-10 pb-8">

            
            <h1 class="text-4xl md:text-6xl font-black text-gray-900 leading-tight mb-3 fade-up d1">
                Saat dunia diam,<br>
                <span class="text-red-700">Safora</span> jadi suaramu.
            </h1>

            <p class="text-gray-500 text-sm md:text-base leading-relaxed mb-8 max-w-sm md:max-w-lg fade-up d1">
                Kirim laporan, simpan bukti otomatis, terhubung ke LBH,<br class="hidden md:block">
                ambulans, atau psikolog — bahkan tanpa akun.
            </p>

            
            <div class="relative flex items-center justify-center mb-5 fade-up d2">
                
                <div class="absolute w-64 h-64 md:w-80 md:h-80 bg-red-100 rounded-full animate-ping opacity-25" style="animation-duration: 2.5s;"></div>
                <div class="absolute w-52 h-52 md:w-64 md:h-64 bg-red-200 rounded-full animate-pulse opacity-20 pointer-events-none"></div>

                <button id="btn-darurat" onclick="openEmergencyModal()"
                   class="relative z-10 w-48 h-48 md:w-60 md:h-60 bg-red-700 hover:bg-red-800 text-white rounded-full flex flex-col items-center justify-center shadow-[0_20px_60px_rgba(185,28,28,0.4)] transition-all hover:scale-105 active:scale-95 cursor-pointer">
                    <svg class="w-12 h-12 md:w-14 md:h-14 text-red-100 mb-2 animate-bounce" fill="currentColor" viewBox="0 0 20 20" style="animation-duration: 2s;"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <span class="font-black text-lg md:text-xl tracking-widest text-red-100">DARURAT</span>
                </button>
            </div>

            
            <p class="flex items-center gap-1.5 text-gray-500 text-sm mb-1 fade-up d2">
                <span class="w-2 h-2 bg-green-400 rounded-full inline-block"></span>
                Anonim
            </p>

            
            <div class="w-full flex items-center gap-4 mb-6 fade-up d3">
                <div class="flex-1 h-px bg-gray-200"></div>
                <div class="flex-1 h-px bg-gray-200"></div>
            </div>

            
            <div class="w-full grid grid-cols-2 md:grid-cols-4 gap-3 fade-up md:hidden">
                
                
                <a href="#cara-kerja" class="group bg-white hover:bg-gray-50 border border-gray-200 hover:border-gray-300 rounded-2xl p-4 md:p-5 text-left transition-all">
                    <div class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center mb-1">
                        <svg class="w-6 h-6 md:w-6 md:h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="font-bold text-gray-900 text-sm mb-1">Cara kerja</p>
                    <p class="text-gray-500 text-xs leading-relaxed">Pelajari alur Safora</p>
                </a>

                
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

    
    <div id="emergency-modal" class="fixed inset-0 z-[999] items-end sm:items-center justify-center bg-black/60 backdrop-blur-sm hidden">
        <div class="modal-in bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-md shadow-2xl overflow-hidden">

            
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
                    <?php $__currentLoopData = ['Kekerasan','Kesehatan','Pelecehan','Kecelakaan','Ancaman','Lainnya']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button onclick="selectCategory('<?php echo e($cat); ?>')"
                        class="category-btn flex flex-col items-start gap-1 bg-gray-50 hover:bg-red-50 border border-gray-200 hover:border-red-300 rounded-2xl px-4 py-4 text-left transition-all group">
                        <span class="font-bold text-gray-900 text-sm group-hover:text-red-700 transition"><?php echo e($cat); ?></span>
                        <span class="text-xs text-gray-400">
                            <?php $hints = ['Kekerasan'=>'Perlu perlindungan','Kesehatan'=>'Butuh bantuan medis','Pelecehan'=>'Butuh pendampingan','Kecelakaan'=>'Butuh respons cepat','Ancaman'=>'Merasa tidak aman','Lainnya'=>'Saya butuh bantuan']; ?>
                            <?php echo e($hints[$cat]); ?>

                        </span>
                    </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <div class="px-5 pb-5">
                    <p class="text-center text-xs text-gray-400">Jika nyawa terancam, hubungi langsung:
                        <a href="tel:112" class="text-red-700 font-bold">112</a> ·
                        <a href="tel:119" class="text-red-700 font-bold">119</a> ·
                        <a href="tel:110" class="text-red-700 font-bold">110</a>
                    </p>
                </div>
            </div>

            
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




    
    <section class="bg-white py-20 px-6">
        <div class="max-w-6xl mx-auto">
            <p class="text-xs font-semibold uppercase tracking-widest text-red-600 mb-4">MENGAPA Safora</p>
            <h2 class="text-4xl font-black text-gray-900 mb-3">Tiga isu, satu platform.</h2>
            <div class="grid md:grid-cols-3 gap-6">
                <div class="border border-gray-200 rounded-2xl p-6 hover:border-orange-200 hover:bg-orange-50/30 transition group">
                    <div class="w-12 h-12 flex items-center justify-center mb-4">
                        <span class="text-2xl">🧭</span>
                    </div>
                    <h3 class="font-bold text-gray-900 text-lg mb-2">Tidak tahu harus lapor ke mana</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Korban panik, tidak tahu harus menghubungi siapa. Safora routing otomatis ke lembaga yang paling sesuai.</p>
                </div>
                <div class="border border-gray-200 rounded-2xl p-6 hover:border-orange-200 hover:bg-orange-50/30 transition group">
                    <div class="w-12 h-12 flex items-center justify-center mb-4">
                        <span class="text-2xl">🛡️</span>
                    </div>
                    <h3 class="font-bold text-gray-900 text-lg mb-2">Pelecehan & kekerasan</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Mode anonim melindungi identitas. Hanya LBH terverifikasi yang akses. Psikolog tersedia via smart routing.</p>
                </div>
                <div class="border border-gray-200 rounded-2xl p-6 hover:border-orange-200 hover:bg-orange-50/30 transition group">
                    <div class="w-12 h-12 flex items-center justify-center mb-4">
                        <span class="text-2xl">🚑</span>
                    </div>
                    <h3 class="font-bold text-gray-900 text-lg mb-2">Darurat publik & kecelakaan</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Kecelakaan, butuh ambulans cepat, warga sekitar tidak tahu kontak darurat. Safora routing otomatis.</p>
                </div>
            </div>
        </div>
    </section>

    
    <section id="cara-kerja" class="bg-[#faf9f7] py-20 px-6">
        <div class="max-w-6xl mx-auto">
            <p class="text-xs font-semibold uppercase tracking-widest text-red-600 mb-4">CARA KERJA</p>
            <h2 class="text-4xl font-black text-gray-900 mb-3">Tiga langkah. Laporan terkirim.</h2>
            <div class="grid md:grid-cols-3 gap-6">
                <div class="bg-white border border-gray-200 rounded-2xl p-6 relative">
                    <div class="w-10 h-10 bg-red-700 rounded-xl flex items-center justify-center font-black text-white text-sm mb-5">1</div>
                    <h3 class="font-bold text-gray-900 text-lg mb-2">Tekan tombol DARURAT</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Satu klik. Countdown 5 detik dengan opsi batal. GPS & waktu otomatis terekam.</p>
                </div>
                <div class="bg-white border border-gray-200 rounded-2xl p-6 relative">
                    <div class="w-10 h-10 bg-red-700 rounded-xl flex items-center justify-center font-black text-white text-sm mb-5">2</div>
                    <h3 class="font-bold text-gray-900 text-lg mb-2">Pilih kategori kejadian</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Kekerasan, kesehatan, pelecehan, kecelakaan, ancaman, atau lainnya. Smart routing bekerja otomatis.</p>
                </div>
                <div class="bg-white border border-gray-200 rounded-2xl p-6 relative">
                    <div class="w-10 h-10 bg-red-700 rounded-xl flex items-center justify-center font-black text-white text-sm mb-5">3</div>
                    <h3 class="font-bold text-gray-900 text-lg mb-2">Laporan terkirim & terlindungi</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">MMitra yang tepat menerima laporan. Saksi sekitar dapat berkontribusi. Bukti digital tersimpan aman.</p>
                </div>
            </div>
        </div>
    </section>

    
    <section class="bg-white py-20 px-6">
        <div class="max-w-6xl mx-auto">
            <p class="text-xs font-semibold uppercase tracking-widest text-red-600 mb-4">FITUR UTAMA</p>
            <h2 class="text-4xl font-black text-gray-900 mb-3">Dirancang untuk keadaan darurat.</h2>
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

    
    <section class=" py-20 px-6">
        <div class="max-w-3xl mx-auto text-center">
            <h2 class="text-4xl font-black mb-4">Siap ketika kamu butuhkan.</h2>
            <p class="text-gray-400 mb-8">Tidak perlu login. Tidak perlu bayar. Fokus awal: Semarang & sekitarnya.</p>
            <a href="/emergency" class="inline-block bg-red-700 hover:bg-red-800 text-white px-8 py-4 rounded-xl font-bold text-lg transition">
                Kirim Laporan Sekarang →
            </a>
        </div>
    </section>

    
    <footer class="bg-white border-t border-gray-200 py-8 px-6">
        <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between">
            <div class="flex items-center gap-1">
                <span class="font-bold text-gray-900 text-md">©</span>
                <span id="year" class="font-bold text-gray-900 text-sm"></span>
                <span class="font-bold text-gray-900 text-sm">Nexi Team</span>
                <span class="text-gray-400 text-sm">— All rights reserved.</span>
            </div>
        </div>
    </footer>

    <?php if(isset($activeReport) && $activeReport): ?>
    
    <div id="floating-report-bubble" class="fixed bottom-6 right-6 z-50" style="animation-duration: 3s;">
        <a href="/tracking/<?php echo e($activeReport->id); ?>" 
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
    </div>
    <?php endif; ?>

</body>
</html>

<script>
  document.getElementById("year").textContent = new Date().getFullYear();
</script>

<script>
// ─── Emergency Modal Logic ───────────────────────────────────────────────────

const csrf = document.querySelector('meta[name="csrf-token"]').content;
const isLoggedIn = <?php echo json_encode(auth()->check(), 15, 512) ?>;
window.hasActiveReport = <?php echo json_encode($activeReport !== null, 15, 512) ?>;
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

function selectCategory(cat) {
    selectedCategory = cat;
    requestLocation();

    // Switch to step 2
    document.getElementById('step-category').classList.add('hidden');
    document.getElementById('step-sending').classList.remove('hidden');
    document.getElementById('modal-category-label').textContent = cat;
    document.getElementById('modal-status').textContent = 'Mengambil lokasi GPS...';

    submitEmergency();
}

function requestLocation() {
    if (!navigator.geolocation) return;
    locationPromise = new Promise((resolve) => {
        navigator.geolocation.getCurrentPosition(
            (pos) => { locationPayload = { latitude: pos.coords.latitude, longitude: pos.coords.longitude }; resolve(); },
            () => { locationPayload = { latitude: null, longitude: null }; resolve(); },
            { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
        );
    });
}

function cancelEmergency() {
    isSubmitting = false;
    selectedCategory = null;
    locationPayload = { latitude: null, longitude: null };
    document.getElementById('step-sending').classList.add('hidden');
    document.getElementById('step-category').classList.remove('hidden');
}

async function submitEmergency() {
    if (isSubmitting) return;
    isSubmitting = true;

    document.getElementById('modal-status').textContent = 'Mengirim laporan ke partner terdekat...';

    await Promise.race([
        locationPromise,
        new Promise(r => setTimeout(r, 2000))
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
        window.location.href = data.tracking_url;
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
<?php /**PATH D:\CODING\olivia_final\resources\views/welcome.blade.php ENDPATH**/ ?>