<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Mitra</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap');
        * { font-family: 'Space Grotesk', sans-serif; }
        h1 { font-family: 'Space Grotesk', sans-serif !important; }
        .font-unbounded { font-family: 'Space Grotesk', sans-serif !important; }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
</head>
<body class="bg-gray-50 min-h-screen text-gray-950 antialiased">

<main class="max-w-4xl mx-auto px-4 py-10 sm:px-6">
    <div class="mb-8">
        <a href="{{ route('admin.mitras') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900 transition flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar Mitra
        </a>
        <h1 class="text-3xl font-black mt-3 tracking-tight">Tambah Mitra Baru</h1>
        <p class="text-sm text-gray-500 mt-1">Daftarkan institusi atau responder krisis baru ke dalam sistem Safora.</p>
    </div>

    <form method="POST" action="{{ route('admin.mitras.store') }}" class="grid gap-6 md:grid-cols-2">
        @csrf

        {{-- Kolom Kiri: Data Profil & Login --}}
        <div class="space-y-6">
            <section class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                <h2 class="text-lg font-black mb-4 text-gray-900">Profil Mitra</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-gray-500">Nama Mitra / Instansi *</label>
                        <input type="text" name="mitra_name" required value="{{ old('mitra_name') }}" placeholder="Contoh: LBH Semarang Utama"
                               class="w-full mt-1.5 border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition">
                    </div>

                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-gray-500">Kategori Mitra *</label>
                        <select name="mitra_type" required
                                class="w-full mt-1.5 border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition bg-white">
                            <option value="ambulance">Medis Darurat (Ambulans)</option>
                            <option value="legal">Bantuan Hukum (LBH/Pengacara)</option>
                            <option value="counselor">Psikososial (Psikolog)</option>
                            <option value="pemadam">Pemadam / Rescue (Damkar)</option>
                            <option value="pppa">Layanan PPPA (Perlindungan Anak & Perempuan)</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-gray-500">Kota / Wilayah *</label>
                        <input type="text" name="city" required value="{{ old('city') }}" placeholder="Semarang"
                               class="w-full mt-1.5 border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition">
                    </div>

                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-gray-500">No. Telepon / WhatsApp</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" placeholder="Contoh: 628512345678"
                               class="w-full mt-1.5 border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition">
                    </div>
                </div>
            </section>

            <section class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                <h2 class="text-lg font-black mb-4 text-gray-900">Akun Pengguna</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-gray-500">Nama Lengkap Admin Akun *</label>
                        <input type="text" name="name" required value="{{ old('name') }}" placeholder="Nama petugas / admin"
                               class="w-full mt-1.5 border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition">
                    </div>

                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-gray-500">Email Login *</label>
                        <input type="email" name="email" required value="{{ old('email') }}" placeholder="admin@instansi.id"
                               class="w-full mt-1.5 border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition">
                    </div>

                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-gray-500">Password Login *</label>
                        <input type="password" name="password" required placeholder="Minimal 6 karakter"
                               class="w-full mt-1.5 border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition">
                    </div>
                </div>
            </section>
        </div>

        {{-- Kolom Kanan: Peta Lokasi & Alamat --}}
        <div class="space-y-6">
            <section class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm flex flex-col h-full">
                <h2 class="text-lg font-black mb-4 text-gray-900">Lokasi Fisik</h2>
                
                <div class="space-y-4 flex-1">
                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-gray-500">Alamat Lengkap Kantor *</label>
                        <textarea name="address" required rows="3" placeholder="Jalan Raya Veteran No. 10, Lempongsari..."
                                  class="w-full mt-1.5 border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition resize-none">{{ old('address') }}</textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-bold uppercase tracking-wider text-gray-500 font-mono">Latitude *</label>
                            <input type="text" name="latitude" id="lat-input" required readonly
                                   class="w-full mt-1.5 border border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm font-mono focus:outline-none">
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase tracking-wider text-gray-500 font-mono">Longitude *</label>
                            <input type="text" name="longitude" id="lng-input" required readonly
                                   class="w-full mt-1.5 border border-gray-100 bg-gray-50 rounded-xl px-4 py-2.5 text-sm font-mono focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-gray-500 block mb-1.5">Tentukan Lokasi di Peta</label>
                        <div id="admin-map" class="h-72 w-full rounded-xl border border-gray-200 z-0"></div>
                        <span class="text-[11px] text-gray-400 block mt-1.5 leading-relaxed">Geser pin merah atau klik pada area Semarang di peta untuk memposisikan titik koordinat kantor mitra secara presisi.</span>
                    </div>
                </div>
            </section>
        </div>

        {{-- Form Submit Action --}}
        <div class="md:col-span-2 flex justify-end gap-3 mt-2">
            <a href="{{ route('admin.mitras') }}" class="px-6 py-3.5 border border-gray-200 hover:bg-gray-100 transition rounded-xl font-bold text-sm text-gray-700">Batal</a>
            <button type="submit" class="px-8 py-3.5 bg-gray-900 hover:bg-black text-white transition rounded-xl font-bold text-sm shadow-md">Simpan Mitra</button>
        </div>
    </form>
</main>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Default center Semarang Kota
        const defaultLat = -6.966667;
        const defaultLng = 110.416664;

        const latInput = document.getElementById('lat-input');
        const lngInput = document.getElementById('lng-input');

        // Set default values in inputs
        latInput.value = defaultLat.toFixed(6);
        lngInput.value = defaultLng.toFixed(6);

        const map = L.map('admin-map', { zoomControl: true }).setView([defaultLat, defaultLng], 13);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const marker = L.marker([defaultLat, defaultLng], {
            draggable: true
        }).addTo(map);

        function updateInputs(lat, lng) {
            latInput.value = lat.toFixed(6);
            lngInput.value = lng.toFixed(6);
        }

        marker.on('dragend', function (e) {
            const position = marker.getLatLng();
            updateInputs(position.lat, position.lng);
        });

        map.on('click', function (e) {
            marker.setLatLng(e.latlng);
            updateInputs(e.latlng.lat, e.latlng.lng);
        });
    });
</script>
</body>
</html>
