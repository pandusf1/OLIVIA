
    let cdInterval=null, lat=null, lng=null;

    function openAllPartnersModal(){
        const m = document.getElementById('all-partners-modal');
        if(!m) return;
        document.body.style.overflow = 'hidden';
        m.classList.remove('hidden');
    }

    function closeAllPartnersModal(){
        const m = document.getElementById('all-partners-modal');
        if(!m) return;
        m.classList.add('hidden');
        document.body.style.overflow = '';
    }

    if(navigator.geolocation){
        navigator.geolocation.getCurrentPosition(
            p=>{lat=p.coords.latitude;lng=p.coords.longitude;document.getElementById('f-lat').value=lat;document.getElementById('f-lng').value=lng;document.getElementById('loc-info').innerHTML='<div class="w-2 h-2 bg-green-500 rounded-full"></div><span class="text-green-600">Lokasi: '+lat.toFixed(4)+', '+lng.toFixed(4)+'</span>';},
            ()=>{document.getElementById('loc-info').innerHTML='<div class="w-2 h-2 bg-red-400 rounded-full"></div><span class="text-red-500">GPS tidak tersedia.</span>';}
        );
    }



    // Load partner gabungan (ambulans + LBH + psikolog + dll) + marker + search
    async function loadNearbyPartners({type = '', query = ''} = {}){
        const el = document.getElementById('nearby-partners');
        const markersEl = document.getElementById('nearby-markers');

        if(!el) return;

        try{
            el.innerHTML = '<div class="text-sm text-gray-400">Memuat partner...</div>';
            if(markersEl) markersEl.innerHTML = '';

            const params = new URLSearchParams();
            if(type) params.set('type', type);
            if(query) params.set('query', query);

            const res = await fetch(`/map-search?${params.toString()}`, { headers: { 'Accept':'application/json' } });
            if(!res.ok) throw new Error('HTTP '+res.status);

            const json = await res.json();
            const items = json.data || [];

            if(items.length===0){
                el.innerHTML = '<div class="text-sm text-gray-400">Belum ada partner yang cocok.</div>';
                return;
            }

            // Cache full result untuk dipakai fitur “Lihat Semua”
            window.__lastNearbyItems = items;

            // Di bawah map hanya tampilkan 4 partner terdekat (preview)
            const previewTop = items.slice(0,4);
            // Namun titik di map tetap menampilkan semua hasil sesuai filter
            const mapAll = items;

            // Visual marker layout: tanpa konversi koordinat ke piksel real map.
            if(markersEl){
                const maxKm = Math.max(...mapAll.map(x => Number(x.distance_km) || 0), 1);

                mapAll.forEach((x, i)=>{


                    const p = x.partner;
                    const km = Number(x.distance_km) || 0;
                    const t = Math.min(km / maxKm, 1);
                    const rPct = 18 + t * 44;
                    const angle = (i * 73 + (p.partner_name?.length || 0) * 11) * Math.PI / 180;

                    const cx = 50;
                    const cy = 50;
                    const xPct = cx + Math.cos(angle) * rPct;
                    const yPct = cy + Math.sin(angle) * rPct;

                    const marker = document.createElement('a');
                    marker.href = `/data-partner/${p.id}`;
                    marker.className = 'absolute -translate-x-1/2 -translate-y-1/2 group';
                    marker.style.zIndex = '0';

                    marker.style.left = `${xPct}%`;
                    marker.style.top = `${yPct}%`;

                    marker.innerHTML = `
                        <div class="w-2.5 h-2.5 rounded-full bg-red-300 border border-red-200 shadow-sm"></div>
                        <div class="absolute -top-2 left-1/2 -translate-x-1/2">
                            <div class="hidden group-hover:block relative z-[999]">
                                <div class="text-[11px] bg-white/95 border border-gray-100 rounded-lg px-2 py-2 shadow text-gray-700 whitespace-nowrap max-w-[240px] relative z-[1000]">

                                    <div class="flex items-start gap-2">
                                        ${p.image_url ? `
                                            <img src="${p.image_url}" class="w-12 h-12 object-cover rounded border border-gray-100 shrink-0" alt="${String(p.partner_name).replace(/</g,'<').replace(/>/g,'>')}">
                                        ` : `
                                            <div class="w-12 h-12 bg-gray-100 rounded border border-gray-100 shrink-0"></div>
                                        `}
                                        <div class="min-w-0">
                                            <div class="font-semibold leading-tight">${String(p.partner_name).replace(/</g,'<').replace(/>/g,'>')}</div>
                                            <div class="text-gray-500 leading-tight">${p.partner_type} • ${Number(km).toFixed(2)} km</div>
                                            <div class="text-gray-600 leading-tight mt-1">${p.phone ? `📞 ${p.phone}` : '-'}</div>
                                            <div class="text-gray-500 leading-tight mt-1">${p.address ? String(p.address).replace(/</g,'<').replace(/>/g,'>') : '-'}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                    marker.style.zIndex = '0';

                    markersEl.appendChild(marker);


                });
            }

            el.innerHTML = previewTop.map((x,i)=>{
                const p = x.partner;
                return `
                    <a href="/data-partner/${p.id}" class="block bg-white border border-gray-100 rounded-xl p-3 hover:bg-gray-50 transition">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-bold text-gray-900 text-sm truncate">${p.partner_name}</p>
                                <p class="text-xs text-gray-500 mt-1">${p.partner_type} • ${Number(x.distance_km).toFixed(2)} km</p>
                            </div>
                            <span class="text-xs font-semibold px-2 py-1 rounded-full bg-red-50 text-red-700 shrink-0">${i+1}</span>
                        </div>
                    </a>
                `;
            }).join('');


        }catch(e){
            const el = document.getElementById('nearby-partners');
            if(el){
                el.innerHTML = `<div class="text-sm text-red-700 bg-red-50 border border-red-200 rounded-xl px-3 py-2">Gagal memuat partner: ${String(e && e.message ? e.message : e)}</div>`;
            }
        }
    }


    // Filter map (type + query) -> refresh marker & list
    const mapTypeEl = document.getElementById('map-search-type');
    const mapQueryEl = document.getElementById('map-search-query');

    function triggerMapSearch(){
        const t = mapTypeEl?.value || '';
        const q = mapQueryEl?.value || '';
        loadNearbyPartners({ type: t, query: q });

        // jika modal sedang terbuka, ikut refresh juga
        const allModal = document.getElementById('all-partners-modal');
        if(allModal && !allModal.classList.contains('hidden')){
            const items = window.__lastNearbyItems || [];
            renderAllPartners(items);
        }
    }


    if(mapTypeEl){
        mapTypeEl.addEventListener('change', ()=>triggerMapSearch());
    }
    if(mapQueryEl){
        let mapDebounceTimer = null;
        mapQueryEl.addEventListener('input', ()=>{
            if(mapDebounceTimer) clearTimeout(mapDebounceTimer);
            mapDebounceTimer = setTimeout(()=>triggerMapSearch(), 250);
        });
    }


    // initial load (semua)
    loadNearbyPartners();

    // Lihat Semua
    const btnViewAll = document.getElementById('btn-view-all-partners');
    const allTypeEl = document.getElementById('all-partners-type');
    const allQueryEl = document.getElementById('all-partners-query');

    function renderAllPartners(items){
        const listEl = document.getElementById('all-partners-list');
        const subtitleEl = document.getElementById('all-partners-subtitle');
        if(!listEl) return;

        if(!items || items.length === 0){
            listEl.innerHTML = '<div class="text-sm text-gray-400">Belum ada partner yang cocok.</div>';
            if(subtitleEl) subtitleEl.textContent = '0 hasil untuk filter & pencarian saat ini.';
            return;
        }

        if(subtitleEl){
            subtitleEl.textContent = `Menampilkan ${items.length} partner. (Preview map tetap 4 di bawah, tapi marker menampilkan semua.)`;
        }

        listEl.innerHTML = items.map((x, i)=>{
            const p = x.partner;
            const km = Number(x.distance_km) || 0;
            return `
                <a href="/data-partner/${p.id}" class="block bg-white border border-gray-100 rounded-xl p-3 hover:bg-gray-50 transition">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-bold text-gray-900 text-sm truncate">${p.partner_name}</p>
                            <p class="text-xs text-gray-500 mt-1">${p.partner_type} • ${km.toFixed(2)} km</p>
                        </div>
                        <span class="text-xs font-semibold px-2 py-1 rounded-full bg-red-50 text-red-700 shrink-0">${i+1}</span>
                    </div>
                </a>
            `;
        }).join('');
    }

    // open modal sync input ke filter map yang aktif
    btnViewAll && btnViewAll.addEventListener('click', async ()=>{
        const t = mapTypeEl?.value || '';
        const q = mapQueryEl?.value || '';

        if(allTypeEl) allTypeEl.value = t;
        if(allQueryEl) allQueryEl.value = q;

        openAllPartnersModal();

        const items = window.__lastNearbyItems || [];
        renderAllPartners(items);
    });

    // modal search realtime (gunakan API yang sama)
    let allDebounceTimer = null;
    const allTriggerSearch = ()=>{
        if(allDebounceTimer) clearTimeout(allDebounceTimer);
        allDebounceTimer = setTimeout(async ()=>{
            const t = allTypeEl?.value || '';
            const q = allQueryEl?.value || '';
            const el = document.getElementById('all-partners-list');
            if(el) el.innerHTML = '<div class="text-sm text-gray-400">Memuat partner...</div>';

            try{
                const params = new URLSearchParams();
                if(t) params.set('type', t);
                if(q) params.set('query', q);

                const res = await fetch(`/map-search?${params.toString()}`, { headers: { 'Accept':'application/json' } });
                if(!res.ok) throw new Error('HTTP '+res.status);
                const json = await res.json();
                const items = json.data || [];
                window.__lastNearbyItems = items; // biar konsisten dengan map
                renderAllPartners(items);

                // refresh map juga agar marker & preview sesuai modal
                loadNearbyPartners({ type: t, query: q });
            }catch(e){
                if(el) el.innerHTML = `<div class="text-sm text-red-700 bg-red-50 border border-red-200 rounded-xl px-3 py-2">Gagal memuat partner: ${String(e && e.message ? e.message : e)}</div>`;
            }
        }, 250);
    };

    if(allTypeEl) allTypeEl.addEventListener('change', ()=>allTriggerSearch());
    if(allQueryEl) allQueryEl.addEventListener('input', ()=>allTriggerSearch());

    // close on backdrop click
    const allModal = document.getElementById('all-partners-modal');
    allModal && allModal.addEventListener('click', function(e){
        if(e.target === this) closeAllPartnersModal();
    });


    // Reload lokasi user -> simpan ke backend -> reload partner
    const reloadBtn = document.getElementById('btn-reload-location');
    if(reloadBtn){
        reloadBtn.addEventListener('click', async () => {
            reloadBtn.disabled = true;
            // Jangan ubah isi tombol (ikon) pakai innerText, karena icon hilang.
            reloadBtn.dataset.originalLabel = reloadBtn.dataset.originalLabel || 'Reload';
            reloadBtn.classList.add('opacity-70');

            // Saat loading: ikon disembunyikan, hanya tampilkan teks.
            const imgEl = reloadBtn.querySelector('svg');
            if(imgEl){ imgEl.style.display = 'none'; }

            let loadingSpan = document.getElementById('reload-loading-span');
            if(!loadingSpan){
                loadingSpan = document.createElement('span');
                loadingSpan.id = 'reload-loading-span';
                loadingSpan.className = 'text-xs font-semibold';
                loadingSpan.textContent = 'Memuat...';
                reloadBtn.appendChild(loadingSpan);
            }
            reloadBtn.setAttribute('data-loading','1');
            reloadBtn.setAttribute('title','Memuat...');

            try{
                if(!navigator.geolocation){
                    throw new Error('Geolocation tidak didukung');
                }

                const pos = await new Promise((resolve, reject) => {
                    navigator.geolocation.getCurrentPosition(resolve, reject, {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    });
                });

                const latitude = pos.coords.latitude;
                const longitude = pos.coords.longitude;

                // update hidden inputs emergency (opsional)
                const latEl = document.getElementById('f-lat');
                const lngEl = document.getElementById('f-lng');
                if(latEl) latEl.value = latitude;
                if(lngEl) lngEl.value = longitude;

                console.log('attempt reload location', { latitude, longitude });

                // Pastikan request benar-benar terkirim, dan jangan silent fallback bila reload lokasi gagal.
                // (Jika lokasi belum tersimpan, fallback loadNearbyPartners akan menampilkan error dari API map-search)
                const res = await fetch('/user-location/reload', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ latitude, longitude })
                });

                // Debug: pastikan response benar-benar ada ok=true
                let reloadJson = null;
                try {
                    reloadJson = await res.json();
                } catch(e) {
                    reloadJson = { parse_error: String(e) };
                }
                console.log('user-location/reload response', reloadJson);

                if(!res.ok) {
                    throw new Error('HTTP ' + res.status + ' body=' + JSON.stringify(reloadJson));
                }

                // Setelah lokasi tersimpan di backend, langsung reload partner & marker.
                // Menghapus quickCheck `/map-search` supaya 1 klik tidak melakukan request tambahan.





                // reload partner list/map
                await loadNearbyPartners({
                    type: mapTypeEl?.value || '',
                    query: mapQueryEl?.value || ''
                });

                // refresh emergency markers juga
                if (window.__loadEmergencyMarkers) {
                    await window.__loadEmergencyMarkers();
                }
            }catch(e){
                const msg = (e && e.message) ? e.message : String(e);
                console.error('Reload lokasi gagal:', msg);

                // tampilkan error agar tidak silent fallback
                const el = document.getElementById('nearby-partners');
                if(el){
                    el.innerHTML = `<div class="text-sm text-red-700 bg-red-50 border border-red-200 rounded-xl px-3 py-2">Gagal menyimpan lokasi: ${msg}</div>`;
                }

                // fallback tetap mencoba reload partner (tapi UI error sudah ditampilkan)
                await loadNearbyPartners({
                    type: mapTypeEl?.value || '',
                    query: mapQueryEl?.value || ''
                });

                // refresh emergency markers juga
                if (window.__loadEmergencyMarkers) {
                    await window.__loadEmergencyMarkers();
                }
            }finally{ 
                reloadBtn.disabled = false;
                reloadBtn.classList.remove('opacity-70');
                reloadBtn.removeAttribute('data-loading');
                reloadBtn.setAttribute('title','Reload lokasi');

                // Hilangkan teks loading setelah selesai.
                const loadingSpan = document.getElementById('reload-loading-span');
                if(loadingSpan){ loadingSpan.remove(); }

                // Tampilkan kembali ikon.
                const imgEl = reloadBtn.querySelector('svg');
                if(imgEl){ imgEl.style.display = ''; }


            }
        });
    }








    let autoSubmitInterval = null;
    let isEmergencySubmitting = false;

    function handleCategoryTap(el) {
        document.getElementById('fallback-category').disabled=true;
        const radio = el.querySelector('input[type="radio"]');
        if (radio) radio.checked = true;
    }

    function startPanic(){
        openCatModal();
    }

    function submitEmergencyForm() {
        if (isEmergencySubmitting) return;
        isEmergencySubmitting = true;
        const form = document.getElementById('emergency-form');
        const submitBtn = document.getElementById('btn-submit-emergency');
        if(submitBtn) {
            submitBtn.innerHTML = 'MENGIRIM...';
            submitBtn.classList.add('opacity-70', 'cursor-not-allowed');
            submitBtn.disabled = true;
        }

        const radios = form.querySelectorAll('input[name="category"]');
        let checked = false;
        radios.forEach(r => {
            if(r.checked) checked = true;
            r.required = false; // Remove required to avoid HTML5 validation blocking
        });
        if (!checked) {
            // Jika user tidak memilih kategori, kirim sebagai unknown_emergency
            document.getElementById('fallback-category').disabled = false;
            document.getElementById('fallback-category').value = 'unknown_emergency';
        }

        HTMLFormElement.prototype.submit.call(form);
    }

    document.getElementById('emergency-form').addEventListener('submit', function(e) {
        e.preventDefault();
        if(autoSubmitInterval) clearInterval(autoSubmitInterval);
        submitEmergencyForm();
    });

    function openCatModal(){
        document.getElementById('cat-modal').classList.remove('hidden');
        document.body.style.overflow='hidden';

        let cd = 10;
        document.getElementById('auto-submit-info').classList.remove('hidden');
        document.getElementById('auto-submit-cd').textContent = cd;
        autoSubmitInterval = setInterval(() => {
            cd--;
            document.getElementById('auto-submit-cd').textContent = cd;
            if (cd <= 0) {
                clearInterval(autoSubmitInterval);
                submitEmergencyForm();
            }
        }, 1000);
    }
    function closeCatModal(){
        document.getElementById('cat-modal').classList.add('hidden');
        document.body.style.overflow='';
        if (autoSubmitInterval) clearInterval(autoSubmitInterval);
    }
    document.getElementById('cat-modal').addEventListener('click',function(e){if(e.target===this)closeCatModal();});
    