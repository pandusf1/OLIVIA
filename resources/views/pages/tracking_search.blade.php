<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Safora — Cek Laporan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap');*{font-family:'Inter',sans-serif;} h1,.font-unbounded{font-family:\'Unbounded\',sans-serif!important;}</style>
</head>
<body class="bg-[#faf9f7] text-gray-900 antialiased min-h-screen">
    @php
        $backUrl = request()->headers->get('referer') ?: (auth()->check() ? route('dashboard') : '/');
        $backLabel = 'Kembali';
        $showBrand = false;
    @endphp
    @include('partials.nav-auth')
    <div class="max-w-md mx-auto px-6 py-20">
        <div class="text-center mb-8">
            <p class="text-4xl mb-4">🔍</p>
<h1 class="font-unbounded text-2xl font-semibold text-gray-900 mb-2">Cek Status Laporan</h1>
            <p class="text-gray-400 text-sm">Masukkan ID laporan atau 8 karakter pertama kode unik Anda.</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl p-6">
            <form action="/tracking-search" method="GET">
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">ID Laporan / Kode Pendek</label>
                <input type="text" name="id" placeholder="Contoh: e439d57a atau #e439d57a..." class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition mb-4" required>
                <button class="w-full bg-red-700 hover:bg-red-800 text-white py-3 rounded-xl font-bold text-sm transition">Cari Laporan</button>
            </form>
        </div>
        <p class="text-center text-gray-400 text-xs mt-4">ID laporan Anda tersimpan di riwayat browser darurat, tautan tracking, atau terkirim ke WhatsApp kontak darurat.</p>
    </div>
</body>
</html>
