<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Psikolog & Pengacara</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#faf9f7] text-gray-900 min-h-screen">
    @php
        $showBrand = false;
        $backUrl = $backUrl ?? request()->headers->get('referer');
        $backLabel = $backLabel ?? 'Kembali';
    @endphp
    @include('partials.nav-auth')


    <div class="max-w-5xl mx-auto px-6 py-10">
        <div class="mb-6">
            <p class="text-xs font-semibold uppercase tracking-widest text-red-600 mb-2">PSIKOLOG & PENGACARA TERDEKAT</p>
            <h1 class="text-3xl font-black">Temukan bantuan terdekat</h1>
            <p class="text-gray-400 text-sm mt-1">Urut berdasarkan jarak dari lokasi kamu (demo).</p>
        </div>

        <div class="grid lg:grid-cols-2 gap-6">
            <div class="bg-white border border-gray-200 rounded-2xl p-6">
                <h2 class="font-bold text-gray-900 text-lg">Pengacara (Legal)</h2>
                <div id="legal-list" class="mt-4 space-y-2">
                    <div class="animate-pulse space-y-2">
                        <div class="flex items-center justify-between p-3 bg-[#faf9f7] border border-gray-100 rounded-xl">
                            <div class="flex-1 space-y-2">
                                <div class="h-4 bg-gray-200 rounded w-2/3"></div>
                                <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                            </div>
                            <div class="w-6 h-6 bg-gray-200 rounded-full"></div>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-[#faf9f7] border border-gray-100 rounded-xl">
                            <div class="flex-1 space-y-2">
                                <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                                <div class="h-3 bg-gray-200 rounded w-1/3"></div>
                            </div>
                            <div class="w-6 h-6 bg-gray-200 rounded-full"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl p-6">
                <h2 class="font-bold text-gray-900 text-lg">Psikolog (Konselor)</h2>
                <div id="counselor-list" class="mt-4 space-y-2">
                    <div class="animate-pulse space-y-2">
                        <div class="flex items-center justify-between p-3 bg-[#faf9f7] border border-gray-100 rounded-xl">
                            <div class="flex-1 space-y-2">
                                <div class="h-4 bg-gray-200 rounded w-2/3"></div>
                                <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                            </div>
                            <div class="w-6 h-6 bg-gray-200 rounded-full"></div>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-[#faf9f7] border border-gray-100 rounded-xl">
                            <div class="flex-1 space-y-2">
                                <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                                <div class="h-3 bg-gray-200 rounded w-1/3"></div>
                            </div>
                            <div class="w-6 h-6 bg-gray-200 rounded-full"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5 text-xs text-gray-400">
            *Demo: hanya menampilkan daftar card + link ke pembayaran mock → chat.
        </div>
    </div>

    <script>
        async function loadLegalCounselor(){
            const legalEl = document.getElementById('legal-list');
            const counselorEl = document.getElementById('counselor-list');
            if(!legalEl || !counselorEl) return;

            try{
                const skeletonHtml = `
                    <div class="animate-pulse space-y-2">
                        <div class="flex items-center justify-between p-3 bg-[#faf9f7] border border-gray-100 rounded-xl">
                            <div class="flex-1 space-y-2">
                                <div class="h-4 bg-gray-200 rounded w-2/3"></div>
                                <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                            </div>
                            <div class="w-6 h-6 bg-gray-200 rounded-full"></div>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-[#faf9f7] border border-gray-100 rounded-xl">
                            <div class="flex-1 space-y-2">
                                <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                                <div class="h-3 bg-gray-200 rounded w-1/3"></div>
                            </div>
                            <div class="w-6 h-6 bg-gray-200 rounded-full"></div>
                        </div>
                    </div>
                `;
                legalEl.innerHTML = skeletonHtml;
                counselorEl.innerHTML = skeletonHtml;

                const res = await fetch('/psikolog-pengacara-nearby', { headers: { 'Accept':'application/json' } });
                if(!res.ok) throw new Error('HTTP '+res.status);
                const json = await res.json();

                const legal = (json.legal || []).slice(0,5);
                const counselor = (json.counselor || []).slice(0,5);

                const getMitraTypeLabel = (type) => {
                    return {
                        ambulance: 'Medis Darurat',
                        legal: 'Bantuan Hukum',
                        counselor: 'Psikososial',
                        pemadam: 'Pemadam / Rescue'
                    }[type] || type;
                };

                const render = (items) => {
                    if(items.length === 0){
                        return '<div class="text-sm text-gray-400">Belum ada mitra terdekat.</div>';
                    }

                    return items.map((x,i)=>{
                        const p = x.partner;
                        const km = Number(x.distance_km) || 0;
                        const no = i+1;

                        return `
                            <a href="/data-partner/${p.id}" class="block bg-[#faf9f7] hover:bg-gray-50 border border-gray-100 rounded-xl p-3 transition">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-bold text-gray-900 text-sm truncate">${p.partner_name}</p>
                                        <p class="text-xs text-gray-500 mt-1">${getMitraTypeLabel(p.partner_type)} • ${km.toFixed(2)} km</p>
                                    </div>
                                    <span class="text-xs font-semibold px-2 py-1 rounded-full bg-red-50 text-red-700 shrink-0">${no}</span>
                                </div>
                            </a>
                        `;
                    }).join('');
                };

                legalEl.innerHTML = render(legal);
                counselorEl.innerHTML = render(counselor);

            }catch(e){
                legalEl.innerHTML = '<div class="text-sm text-gray-400">Gagal memuat data.</div>';
                counselorEl.innerHTML = '<div class="text-sm text-gray-400">Gagal memuat data.</div>';
            }
        }

        loadLegalCounselor();
    </script>
</body>
</html>


