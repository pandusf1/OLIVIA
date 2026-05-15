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
                    <div class="relative">
                        <div class="w-3.5 h-3.5 rounded-full border-2 border-white shadow-md shadow-red-200 ${c.dot} ${c.ring}"></div>
                        <div class="absolute -top-2 left-1/2 -translate-x-1/2 hidden group-hover:block">
                            <div class="text-[11px] bg-white/95 border border-gray-100 rounded-lg px-2 py-1 shadow text-gray-700 whitespace-nowrap">
                                <div class="font-semibold">${m.category}</div>
                                <div class="text-gray-500">${m.status}</div>
                                <div class="text-gray-400">${Number(m.distance_km).toFixed(2)} km</div>
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

