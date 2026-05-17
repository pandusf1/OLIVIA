<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Savora — Data Partner</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style> @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap');
        * { font-family: 'Space Grotesk', sans-serif; }
        h1 { font-family: 'Space Grotesk', sans-serif !important; }
        .font-unbounded { font-family: 'Space Grotesk', sans-serif !important; }
    </style>
</head>
<body class="bg-[#faf9f7] text-gray-900 antialiased min-h-screen">
    @include('partials.nav-auth')

    <div class="max-w-5xl mx-auto px-6 py-10">

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl mb-6 text-sm">
                ✓ {{ session('success') }}
            </div>
        @endif

        @php
            // dashboard-detail: brand (logo+teks) disembunyikan, tombol kembali akan mengarah ke page sebelumnya.
            $backUrl = $backUrl ?? request()->headers->get('referer');
            $backLabel = $backLabel ?? 'Kembali';
            $showBrand = false;
        @endphp

        {{-- Header: gambar partner di luar container, diikuti teks nama partner --}}
        <div class="mb-6">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl border border-gray-100 bg-gray-100 overflow-hidden flex-shrink-0">
                    @if(!empty($partner->image_url))
                        <a href="{{ $partner->image_url }}" target="_blank" rel="noopener noreferrer" class="block w-full h-full">
                            <img src="{{ $partner->image_url }}" alt="{{ $partner->partner_name }}" class="w-full h-full object-cover"/>
                        </a>
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-400">—</div>
                    @endif
                </div>


                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-1">INFORMASI DATA PARTNER</p>
<p class="text-gray-900 font-bold text-xl truncate">{{ $partner->partner_name }}</p>
                    <p class="text-gray-500 text-sm mt-1">{{ $partner->partner_type }}</p>
                </div>
            </div>
        </div>

        {{-- Wrapper: Map (kiri) + Informasi data partner (kanan) --}}
        <div class="bg-white border border-gray-200 rounded-2xl p-6 mb-4">
            @php
                $hasLatLng = filled($partner->latitude) && filled($partner->longitude);
            @endphp

            <div>
                <h2 class="font-semibold text-gray-900 mb-6">Map & Informasi Partner</h2>

                <div class="grid lg:grid-cols-12 gap-6 items-start">
                    {{-- Map (kiri) --}}
                    <div class="lg:col-span-5">

                        <div class="relative overflow-hidden rounded-xl bg-[#faf9f7] border border-gray-100 p-4">

                            <div class="absolute inset-0 pointer-events-none" style="
                            background:
                                radial-gradient(circle at 30% 25%, rgba(239,68,68,0.10) 0%, rgba(239,68,68,0.00) 55%),
                                radial-gradient(circle at 70% 65%, rgba(239,68,68,0.08) 0%, rgba(239,68,68,0.00) 50%),
                                repeating-linear-gradient(135deg, rgba(0,0,0,0.05) 0, rgba(0,0,0,0.05) 1px, transparent 1px, transparent 14px),
                                repeating-linear-gradient(45deg, rgba(0,0,0,0.03) 0, rgba(0,0,0,0.03) 1px, transparent 1px, transparent 18px);
                            filter: saturate(1.05) contrast(1.02);
                        ">
                        </div>

                        <div class="relative">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="w-2.5 h-2.5 rounded-full bg-red-700" title="Lokasi partner"></span>
                                <p class="text-xs font-semibold text-gray-600">Lokasi partner</p>
                            </div>

                            <div class="relative w-full h-64 rounded-xl border border-gray-100 bg-white/40 overflow-hidden">
                                <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2">
                                    <div class="w-3 h-3 rounded-full bg-red-700 shadow-md shadow-red-200"></div>
                                    <div class="w-7 h-7 rounded-full border-2 border-red-200/80 absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 animate-pulse" style="animation-duration:2.2s"></div>
                                </div>

                                @if($hasLatLng)
                                    <div class="absolute left-3 bottom-3 text-[11px] text-gray-600 bg-white/90 border border-gray-100 rounded-lg px-3 py-2">
                                        {{ $partner->latitude }}, {{ $partner->longitude }}
                                    </div>
                                @else
                                    <div class="absolute left-3 bottom-3 text-[11px] text-gray-500 bg-white/90 border border-gray-100 rounded-lg px-3 py-2">
                                        Koordinat tidak tersedia
                                    </div>
                                @endif
                            </div>

                            @if($hasLatLng)
                                <div class="mt-3">
                                    <a href="https://maps.google.com/?q={{ $partner->latitude }},{{ $partner->longitude }}" target="_blank" class="inline-flex items-center gap-2 text-sm font-semibold text-red-700 hover:text-red-800 transition underline">
                                        <span>📍</span>
                                        Lihat di Google Maps
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Informasi data partner (kanan) --}}
                <div class="lg:col-span-7">
                    <div class="flex flex-col gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Email</p>
                            <p class="text-gray-800 font-semibold mt-1">{{ $partner->email ? $partner->email : '-' }}</p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Kontak</p>
                            <p class="text-gray-800 font-semibold mt-1">{{ $partner->phone ? $partner->phone : '-' }}</p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Kota</p>
                            <p class="text-gray-800 font-semibold mt-1">{{ $partner->city ? $partner->city : '-' }}</p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Alamat</p>
                            <p class="text-gray-800 font-semibold mt-1">{{ $partner->address ? $partner->address : '-' }}</p>
                        </div>
                    </div>

                    <div class="mt-4 bg-[#faf9f7] border border-gray-100 rounded-xl p-4">
                        <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Catatan</p>
                        <p class="text-gray-500 text-sm mt-1">
                            Jam kerja atau informasi lainnya.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>


            @php
                $canShowPriceList = in_array($partner->partner_type, ['legal', 'counselor'], true);
            @endphp

            @if(!$canShowPriceList)
                <div class="text-sm text-gray-500 bg-[#faf9f7] border border-gray-100 rounded-xl p-4">
                </div>
            @else
        {{-- Section 3: pricelist (khusus legal & counselor) --}}
        <div class="bg-white border border-gray-200 rounded-2xl p-6">
            <h2 class="font-semibold text-gray-900 mb-4">Daftar Pricelist</h2>
            
                @if($priceLists->count() === 0)
                    <div class="text-sm text-gray-500 bg-[#faf9f7] border border-gray-100 rounded-xl p-4">
                        Belum ada price list untuk partner ini.
                    </div>
                @else
                
                    <div class="space-y-3">
                        @foreach($priceLists as $pl)
                            <div class="flex items-start justify-between gap-4 bg-[#faf9f7] border border-gray-100 rounded-xl p-4">
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $pl->service_name }}</p>
                                    @if($pl->duration)
                                        <p class="text-xs text-gray-500 mt-1">Durasi: {{ $pl->duration }}</p>
                                    @endif
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="font-black text-gray-900 font-semibold">Rp {{ number_format($pl->price, 0, ',', '.') }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ $pl->currency }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>

</body>
</html>


