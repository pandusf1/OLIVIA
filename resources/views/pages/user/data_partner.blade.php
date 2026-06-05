<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Mitra</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
    <style> @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap');
        * { font-family: 'Space Grotesk', sans-serif; }
        h1 { font-family: 'Space Grotesk', sans-serif !important; }
        .font-unbounded { font-family: 'Space Grotesk', sans-serif !important; }
        @keyframes scaleIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        .animate-scale-in {
            animation: scaleIn 0.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>
</head>
<body class="bg-[#faf9f7] text-gray-900 antialiased min-h-screen">
    @include('partials.nav-auth')

    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-6 sm:py-10">



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


                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-1">INFORMASI DATA MITRA</p>
                    <p class="text-gray-900 font-bold text-lg sm:text-xl md:text-2xl break-words leading-tight">{{ $partner->partner_name }}</p>
                    @php
                        $typeLabel = match($partner->partner_type) {
                            'ambulance' => 'Medis Darurat',
                            'legal' => 'Bantuan Hukum',
                            'counselor' => 'Psikososial',
                            'pemadam' => 'Pemadam / Rescue',
                            default => 'Mitra Krisis'
                        };
                    @endphp
                    <p class="text-gray-500 text-sm mt-1">{{ $typeLabel }}</p>
                </div>
            </div>
        </div>

        {{-- Wrapper: Map (kiri) + Informasi data partner (kanan) --}}
        <div class="bg-white border border-gray-200 rounded-2xl p-4 sm:p-6 mb-4">
            @php
                $hasLatLng = filled($partner->latitude) && filled($partner->longitude);
            @endphp

            <div>
                <h2 class="font-semibold text-gray-900 mb-6">Map & Informasi Mitra</h2>

                <div class="grid md:grid-cols-12 gap-6 items-start">
                    {{-- Map (kiri) --}}
                    <div class="md:col-span-5 w-full">

                        <div class="relative overflow-hidden rounded-xl bg-[#faf9f7] border border-gray-100 p-3 sm:p-4">

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
                                <span class="w-2.5 h-2.5 rounded-full bg-red-700" title="Lokasi mitra"></span>
                                <p class="text-xs font-semibold text-gray-600">Lokasi mitra</p>
                            </div>

                            <div id="partner-location-map" class="relative w-full h-48 sm:h-56 md:h-64 rounded-xl border border-gray-100 bg-white/40 overflow-hidden z-0">

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
                                    <a href="https://www.openstreetmap.org/?mlat={{ $partner->latitude }}&mlon={{ $partner->longitude }}#map=16/{{ $partner->latitude }}/{{ $partner->longitude }}" target="_blank" class="inline-flex items-center gap-2 text-sm font-semibold text-red-700 hover:text-red-800 transition underline">
                                        <span>📍</span>
                                        Lihat di OpenStreetMap
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Informasi data partner (kanan) --}}
                <div class="md:col-span-7 w-full">
                    <div class="flex flex-col divide-y divide-gray-100">
                        <div class="py-3 first:pt-0 last:pb-0">
                            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Email</p>
                            <p class="text-gray-800 font-semibold mt-1 break-all text-sm sm:text-base">{{ $partner->email ? $partner->email : '-' }}</p>
                        </div>

                        <div class="py-3 first:pt-0 last:pb-0">
                            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Kontak</p>
                            <p class="text-gray-800 font-semibold mt-1 break-all text-sm sm:text-base">{{ $partner->phone ? $partner->phone : '-' }}</p>
                        </div>

                        <div class="py-3 first:pt-0 last:pb-0">
                            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Kota</p>
                            <p class="text-gray-800 font-semibold mt-1 break-words text-sm sm:text-base">{{ $partner->city ? $partner->city : '-' }}</p>
                        </div>

                        <div class="py-3 first:pt-0 last:pb-0">
                            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Alamat</p>
                            <p class="text-gray-800 font-semibold mt-1 break-words text-sm sm:text-base leading-relaxed whitespace-pre-line">{{ $partner->address ? $partner->address : '-' }}</p>
                        </div>
                    </div>

                    <div class="mt-4 bg-[#faf9f7] border border-gray-100 rounded-xl p-3 sm:p-4">
                        <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Catatan</p>
                        <p class="text-gray-500 text-xs sm:text-sm mt-1 leading-relaxed">
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
        <div class="bg-white border border-gray-200 rounded-2xl p-4 sm:p-6">
            <h2 class="font-semibold text-gray-900 mb-4 text-base sm:text-lg">Daftar Pricelist</h2>

            {{-- Multi select state --}}
            <div id="priceListSelectedState" class="hidden">0</div>

                @if($priceLists->count() === 0)
                    <div class="text-sm text-gray-500 bg-[#faf9f7] border border-gray-100 rounded-xl p-3 sm:p-4">
                        Belum ada daftar layanan untuk mitra ini.
                    </div>
                @else

                    <div class="space-y-3">
                        @foreach($priceLists as $pl)
                            <div
                                data-price-list-id="{{ $pl->id }}"
                                data-selected="0"
                                class="price-list-card flex items-start justify-between gap-3 sm:gap-4 bg-[#faf9f7] border border-gray-100 rounded-xl p-3 sm:p-4 cursor-pointer transition hover:border-gray-300"
                            >
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-gray-900 text-sm sm:text-base break-words leading-tight">{{ $pl->service_name }}</p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="font-black text-gray-900 text-sm sm:text-base">Rp {{ number_format($pl->price, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- CTA Lanjut --}}
                    <div id="selectedPriceListCtaWrap" class="mt-6 hidden">
                        <button
                            id="selectedPriceListCta"
                            type="button"
                            class="w-full bg-gray-900 hover:bg-black text-white font-bold py-3 px-4 rounded-xl sm:rounded-2xl transition active:scale-[0.98] text-sm sm:text-base shadow-sm"
                        >
                            Lanjut
                        </button>
                    </div>

                @endif
            @endif
        </div>

        {{-- Modal daftar layanan terpilih --}}
        <div id="servicesModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40"></div>
            <div class="relative w-full max-w-md bg-white border border-gray-200 rounded-2xl shadow-xl overflow-hidden flex flex-col max-h-[85vh] animate-scale-in">
                <div class="p-4 sm:p-5 border-b border-gray-100 flex-shrink-0">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Layanan Terpilih</p>
                            <h3 class="font-black text-base sm:text-lg text-gray-900">Ringkasan Pricelist</h3>
                        </div>
                        <button
                            type="button"
                            id="modalCloseX"
                            class="text-gray-400 hover:text-gray-600 transition text-sm font-bold px-2 py-1 rounded-full"
                            aria-label="Tutup"
                        >
                            ✕
                        </button>
                    </div>
                </div>

                <div class="p-4 sm:p-5 overflow-y-auto flex-1">
                    <div id="selectedServicesList" class="space-y-3">
                        {{-- populated by JS --}}
                    </div>

                    <div id="selectedServicesEmpty" class="hidden text-sm text-gray-500 bg-[#faf9f7] border border-gray-100 rounded-xl p-3 sm:p-4 mt-1">
                        Tidak ada layanan dipilih.
                    </div>
                </div>

                <div class="p-4 sm:p-5 border-t border-gray-100 flex gap-3 flex-shrink-0">
                    <button
                        type="button"
                        id="modalBackBtn"
                        class="flex-1 bg-white hover:bg-gray-50 border border-gray-200 text-gray-900 font-bold py-2.5 rounded-xl sm:rounded-2xl transition text-sm sm:text-base"
                    >
                        Kembali
                    </button>
                    <button
                        type="button"
                        id="modalContinueBtn"
                        class="flex-1 bg-gray-900 hover:bg-black text-white font-bold py-2.5 rounded-xl sm:rounded-2xl transition active:scale-[0.98] text-sm sm:text-base shadow-sm"
                    >
                        Lanjut
                    </button>
                </div>
            </div>
        </div>

<script>
@if($hasLatLng)
(function () {
    const lat = @json((float) $partner->latitude);
    const lng = @json((float) $partner->longitude);
    const mapEl = document.getElementById('partner-location-map');
    if (!mapEl || !window.L) return;

    const map = L.map(mapEl, { scrollWheelZoom: false }).setView([lat, lng], 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
    }).addTo(map);

    const partnerIcon = L.divIcon({
        className: 'custom-partner-location-marker',
        html: '<div class="w-4 h-4 rounded-full bg-red-700 border-2 border-white shadow-md shadow-red-200"></div>',
        iconSize: [16, 16],
        iconAnchor: [8, 8]
    });

    L.marker([lat, lng], { icon: partnerIcon }).addTo(map);
})();
@endif

(function () {
    const cards = Array.from(document.querySelectorAll('.price-list-card'));
    const ctaWrap = document.getElementById('selectedPriceListCtaWrap');
    const ctaBtn = document.getElementById('selectedPriceListCta');

    const modal = document.getElementById('servicesModal');
    const modalCloseX = document.getElementById('modalCloseX');
    const modalBackBtn = document.getElementById('modalBackBtn');
    const modalContinueBtn = document.getElementById('modalContinueBtn');

    const selectedServicesList = document.getElementById('selectedServicesList');
    const selectedServicesEmpty = document.getElementById('selectedServicesEmpty');

    // selected ids
    const selected = new Set();

    function setCardSelected(card, isSelected) {
        if (isSelected) {
            card.dataset.selected = '1';
            card.classList.add('bg-green-50', 'border-green-200');
            card.classList.remove('bg-[#faf9f7]', 'border-gray-100');
        } else {
            card.dataset.selected = '0';
            card.classList.remove('bg-green-50', 'border-green-200');
            card.classList.add('bg-[#faf9f7]', 'border-gray-100');
        }
    }

    function refreshCta() {
        const count = selected.size;
        ctaWrap.classList.toggle('hidden', count === 0);
    }

    function getSelectedServicesData() {
        const items = [];
        cards.forEach((card) => {
            const id = String(card.dataset.priceListId);
            if (!selected.has(id)) return;

            const titleEl = card.querySelector('p.font-semibold.text-gray-900');
            const durationEl = card.querySelector('p.text-xs.text-gray-500');
            const priceEl = card.querySelector('p.font-black.text-gray-900');
            const currencyEl = card.querySelector('p.text-xs.text-gray-400');

            items.push({
                id,
                serviceName: titleEl ? titleEl.textContent.trim() : '',
                durationText: durationEl ? durationEl.textContent.trim() : '',
                priceText: priceEl ? priceEl.textContent.trim() : '',
                currencyText: currencyEl ? currencyEl.textContent.trim() : '',
            });
        });
        return items;
    }

    function renderModalList() {
        const items = getSelectedServicesData();
        selectedServicesList.innerHTML = '';

        if (items.length === 0) {
            selectedServicesEmpty.classList.remove('hidden');
            return;
        }
        selectedServicesEmpty.classList.add('hidden');

        items.forEach((item) => {
            const row = document.createElement('div');
            row.className = 'flex items-start justify-between gap-3 bg-[#faf9f7] border border-gray-100 rounded-xl p-3 sm:p-4';

            row.innerHTML = `
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-gray-900 text-sm sm:text-base break-words leading-tight">${escapeHtml(item.serviceName)}</p>
                    ${item.durationText && !item.durationText.toLowerCase().includes('menit') ? `<p class="text-xs text-gray-500 mt-1">${escapeHtml(item.durationText)}</p>` : ''}
                    <p class="text-xs text-gray-400 mt-1">${escapeHtml(item.currencyText)}</p>
                </div>
                <div class="text-right shrink-0">
                    <p class="font-black text-gray-900 text-sm sm:text-base">${escapeHtml(item.priceText)}</p>
                    <button
                        type="button"
                        class="mt-2 inline-flex bg-white border border-gray-200 hover:bg-gray-50 text-gray-900 text-xs font-bold px-2.5 py-1.5 rounded-lg transition"
                        data-remove-id="${escapeHtml(item.id)}"
                    >
                        Hapus
                    </button>
                </div>
            `;

            selectedServicesList.appendChild(row);
        });

        selectedServicesList.querySelectorAll('[data-remove-id]').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                const removeId = String(e.currentTarget.dataset.removeId);
                selected.delete(removeId);

                const card = cards.find(c => String(c.dataset.priceListId) === removeId);
                if (card) setCardSelected(card, false);

                refreshCta();
                renderModalList();
            });
        });
    }

    function openModal() {
        renderModalList();
        modal.classList.remove('hidden');
    }

    function closeModal() {
        modal.classList.add('hidden');
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '<')
            .replace(/>/g, '>')
            .replace(/"/g, '"')
            .replace(/'/g, '&#039;');
    }

    cards.forEach((card) => {
        card.addEventListener('click', () => {
            const id = String(card.dataset.priceListId);
            const isSelected = selected.has(id);

            if (isSelected) {
                selected.delete(id);
                setCardSelected(card, false);
            } else {
                selected.add(id);
                setCardSelected(card, true);
            }
            refreshCta();
        });
    });

    if (ctaBtn) {
        ctaBtn.addEventListener('click', () => {
            openModal();
        });
    }

    modalCloseX && modalCloseX.addEventListener('click', closeModal);
    modalBackBtn && modalBackBtn.addEventListener('click', closeModal);

    modalContinueBtn && modalContinueBtn.addEventListener('click', () => {
        const firstId = Array.from(selected)[0];
        if (!firstId) return;
        const url = `{{ url('/pembayaran') }}/${firstId}`;
        window.location.href = url;
    });
})();
</script>


</body>
</html>


