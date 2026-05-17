<script>
(async function(){
    const markersEl = document.getElementById('nearby-markers');
    if(!markersEl) return;

    async function loadEmergencyMarkers(){
        try{
            markersEl.innerHTML = '';
            const res = await fetch('/dashboard/emergency-markers', { headers: { 'Accept':'application/json' } });
            const json = await res.json();
            if(!res.ok) return;

            const markers = json.data || [];
            if(markers.length === 0) return;

            const statusColor = (status) => {
                switch(status){
                    case 'Submitted': return {dot:'bg-red-500', ring:'ring-red-100'};
                    case 'Routed': return {dot:'bg-red-600', ring:'ring-red-200'};
                    case 'Viewed': return {dot:'bg-yellow-500', ring:'ring-yellow-100'};
                    case 'In Progress': return {dot:'bg-orange-500', ring:'ring-orange-100'};
                    case 'Resolved': return {dot:'bg-green-500', ring:'ring-green-100'};
                    default: return {dot:'bg-red-400', ring:'ring-red-100'};
                }
            };

            // Visual layout: marker semu (tanpa konversi koordinat real)
            const maxKm = Math.max(...markers.map(x => Number(x.distance_km) || 0), 1);

            markers.forEach((m,i)=>{
                const t = Math.min((Number(m.distance_km)||0) / maxKm, 1);
                const rPct = 10 + t * 42;
                const angle = (i * 71 + (m.category ? m.category.length : 0) * 7) * Math.PI / 180;
                const cx = 50;
                const cy = 50;
                const xPct = cx + Math.cos(angle) * rPct;
                const yPct = cy + Math.sin(angle) * rPct;

                const c = statusColor(m.status);

                const a = document.createElement('a');
                a.href = `/tracking/${m.id}`;
                a.className = 'absolute -translate-x-1/2 -translate-y-1/2 group';
                a.style.left = `${xPct}%`;
                a.style.top = `${yPct}%`;

                a.innerHTML = `
                    <div class="relative cursor-pointer">
                        <div class="w-4 h-4 rounded-full border-2 border-white shadow-lg animate-pulse ${c.dot} ${c.ring}"></div>
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2 hidden group-hover:block z-[9999]">
                            <div class="text-[11px] bg-red-900 border border-red-700 rounded-lg px-3 py-2 shadow-2xl text-white whitespace-nowrap">
                                <div class="font-bold text-red-100 flex items-center gap-1.5 mb-1"><span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span> DARURAT</div>
                                <div class="font-semibold text-sm">${m.victim_name || 'Anonim'}</div>
                                <div class="text-red-200 mt-0.5">${m.category}</div>
                                <div class="text-red-300/80 mt-1">${Number(m.distance_km).toFixed(2)} km dari kamu</div>
                            </div>
                        </div>
                    </div>
                `;

                markersEl.appendChild(a);
            });
        }catch(e){
            // ignore
        }
    }

    // first load
    await loadEmergencyMarkers();

    // expose for reload button
    window.__loadEmergencyMarkers = loadEmergencyMarkers;
})();
</script>

