<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Safora — Suara & Perlindungan</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap');
        * { font-family: 'Inter', sans-serif; }
        @keyframes fadeUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
        .fade-up { animation: fadeUp 0.6s ease forwards; }
        .d1{animation-delay:0s} .d2{animation-delay:.15s} .d3{animation-delay:.3s}
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
                <a href="/witness" class="text-gray-600 hover:text-gray-900 text-sm transition hidden md:block">Saksi</a>
                <a href="/emergency" class="text-gray-600 hover:text-gray-900 text-sm transition hidden md:block">Darurat</a>
                <?php if(auth()->guard()->check()): ?>
                <a href="<?php echo e(route('dashboard')); ?>" class="text-gray-600 hover:text-gray-900 text-sm transition">Dashboard</a>
                <?php else: ?>
                <a href="<?php echo e(route('login')); ?>" class="text-gray-600 hover:text-gray-900 text-sm transition">Masuk</a>
                <a href="<?php echo e(route('register')); ?>" class="bg-gray-900 hover:bg-gray-700 text-white text-sm px-4 py-2 rounded-lg font-semibold transition">Daftar</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    
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
                <div class="flex flex-col sm:flex-row gap-3 fade-up d3">
                    <a href="/emergency" class="bg-red-700 hover:bg-red-800 text-white px-6 py-3 rounded-xl font-bold text-sm flex items-center gap-2 transition justify-center">
                        Kirim Laporan Darurat →
                    </a>
                    <a href="#cara-kerja" class="bg-white border border-gray-200 hover:border-gray-300 text-gray-700 px-6 py-3 rounded-xl font-semibold text-sm transition text-center">
                        Cara Kerja
                    </a>
                </div>
                <p class="text-gray-400 text-xs mt-4 fade-up d3">Safora adalah penghubung & pelindung bukti digital — bukan pengganti layanan darurat resmi pemerintah.</p>
            </div>
            <div class="hidden md:block">
                <div class="bg-gray-900 rounded-2xl overflow-hidden aspect-video flex items-end p-6 relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-gray-800 to-gray-950"></div>
                    <div class="relative z-10">
                        <p class="text-gray-400 text-xs uppercase tracking-widest mb-2">AUTO EVIDENCE LOCKER</p>
                        <p class="text-white font-bold text-xl">Bukti tetap aman, meski HP rusak.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    
    <section class="bg-white py-20 px-6">
        <div class="max-w-6xl mx-auto">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-4">MENGAPA Safora</p>
            <h2 class="text-4xl font-black text-gray-900 mb-12">Tiga isu, satu platform.</h2>
            <div class="grid md:grid-cols-3 gap-6">
                <?php $__currentLoopData = [
                    ['⚖️','Salah tangkap & penyalahgunaan wewenang','Korban takut, bukti hilang, keluarga bingung. Safora mengamankan bukti & menghubungkan ke LBH terverifikasi.'],
                    ['🛡️','Pelecehan & kekerasan','Mode anonim melindungi identitas. Hanya LBH terverifikasi yang akses. Psikolog tersedia via smart routing.'],
                    ['🚑','Darurat publik','Kecelakaan, butuh ambulans cepat, warga sekitar tidak tahu kontak darurat. Safora routing otomatis.'],
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$icon, $title, $desc]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="border border-gray-200 rounded-2xl p-6 hover:border-gray-300 transition">
                    <p class="text-2xl mb-4"><?php echo e($icon); ?></p>
                    <h3 class="font-bold text-gray-900 text-lg mb-2"><?php echo e($title); ?></h3>
                    <p class="text-gray-500 text-sm leading-relaxed"><?php echo e($desc); ?></p>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </section>

    
    <section id="cara-kerja" class="bg-[#faf9f7] py-20 px-6">
        <div class="max-w-6xl mx-auto">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-4">CARA KERJA</p>
            <h2 class="text-4xl font-black text-gray-900 mb-12">Tiga langkah. Selesai.</h2>
            <div class="grid md:grid-cols-3 gap-6">
                <?php $__currentLoopData = [
                    ['1','Tekan tombol darurat','Satu klik. Countdown 5 detik dengan opsi batal. GPS & waktu otomatis terekam.'],
                    ['2','Pilih kategori kejadian','Salah tangkap, pelecehan, kekerasan, atau kecelakaan. Smart routing bekerja otomatis.'],
                    ['3','Laporan terkirim & terlindungi','Mitra yang tepat menerima laporan. Trusted contact mendapat alert WhatsApp. Bukti tersimpan aman.'],
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$num, $title, $desc]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-white border border-gray-200 rounded-2xl p-6">
                    <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center font-black text-gray-700 text-sm mb-4"><?php echo e($num); ?></div>
                    <h3 class="font-bold text-gray-900 text-lg mb-2"><?php echo e($title); ?></h3>
                    <p class="text-gray-500 text-sm leading-relaxed"><?php echo e($desc); ?></p>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </section>

    
    <section class="bg-white py-20 px-6">
        <div class="max-w-6xl mx-auto">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-4">FITUR UTAMA</p>
            <h2 class="text-4xl font-black text-gray-900 mb-12">Dirancang untuk keadaan darurat.</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php $__currentLoopData = [
                    ['🚨','Quick Emergency','Panic button → countdown 5 detik → kategori → kirim. GPS otomatis. WhatsApp alert ke trusted contact.'],
                    ['🔒','Auto Evidence Locker','SHA-256 hash, timestamp, GPS pada setiap file. Bukti aman meski HP korban rusak atau disita.'],
                    ['👁️','Anonymous Mode','Identitas opsional. Hanya mitra terverifikasi (LBH) yang bisa akses data korban.'],
                    ['👥','Community Witness','Saksi sekitar upload bukti via kode laporan. Tidak publik, masuk ke laporan terkait.'],
                    ['🧭','Smart Routing','Laporan diarahkan otomatis: Salah tangkap→LBH, Kecelakaan→Ambulans, KDRT→Shelter+Psikolog.'],
                    ['🔢','Stealth Mode','Sembunyikan layar satu klik, redirect ke tampilan kalkulator. Aman jika situasi berbahaya.'],
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$icon, $title, $desc]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="border border-gray-200 rounded-2xl p-5 hover:border-gray-300 transition">
                    <p class="text-xl mb-3"><?php echo e($icon); ?></p>
                    <h3 class="font-bold text-gray-900 mb-1"><?php echo e($title); ?></h3>
                    <p class="text-gray-500 text-sm leading-relaxed"><?php echo e($desc); ?></p>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </section>

    
    <section class="bg-gray-900 py-20 px-6">
        <div class="max-w-3xl mx-auto text-center">
            <h2 class="text-4xl font-black text-white mb-4">Siap ketika kamu butuhkan.</h2>
            <p class="text-gray-400 mb-8">Tidak perlu login. Tidak perlu bayar. Fokus awal: Semarang & sekitarnya.</p>
            <a href="/emergency" class="inline-block bg-red-700 hover:bg-red-800 text-white px-8 py-4 rounded-xl font-bold text-lg transition">
                Kirim Laporan Sekarang →
            </a>
        </div>
    </section>

    
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
                <?php if(auth()->guard()->guest()): ?><a href="<?php echo e(route('login')); ?>" class="hover:text-gray-900 transition">Masuk</a><?php endif; ?>
            </div>
        </div>
    </footer>

</body>
</html>

<?php /**PATH D:\CODING\olivia_final\resources\views\welcome.blade.php ENDPATH**/ ?>