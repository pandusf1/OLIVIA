<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SuraRa — Psikolog & Pengacara Terdekat</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-[#faf9f7] text-gray-900 min-h-screen">
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
                    <div class="text-sm text-gray-400">Memuat data...</div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl p-6">
                <h2 class="font-bold text-gray-900 text-lg">Psikolog (Konselor)</h2>
                <div id="counselor-list" class="mt-4 space-y-2">
                    <div class="text-sm text-gray-400">Memuat data...</div>
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
                legalEl.innerHTML = '<div class="text-sm text-gray-400">Memuat data...</div>';
                counselorEl.innerHTML = '<div class="text-sm text-gray-400">Memuat data...</div>';

                const res = await fetch('/psikolog-pengacara-nearby', { headers: { 'Accept':'application/json' } });
                if(!res.ok) throw new Error('HTTP '+res.status);
                const json = await res.json();

                const legal = (json.legal || []).slice(0,5);
                const counselor = (json.counselor || []).slice(0,5);

                const render = (items) => {
                    if(items.length === 0){
                        return '<div class="text-sm text-gray-400">Belum ada partner terdekat.</div>';
                    }

                    return items.map((x,i)=>{
                        const p = x.partner;
                        const km = Number(x.distance_km) || 0;
                        const no = i+1;

                        return `
                            <a href="/pembayaran/partner/${p.id}" class="block bg-[#faf9f7] hover:bg-gray-50 border border-gray-100 rounded-xl p-3 transition">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-bold text-gray-900 text-sm truncate">${p.partner_name}</p>
                                        <p class="text-xs text-gray-500 mt-1">${p.partner_type} • ${km.toFixed(2)} km</p>
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

