<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loker Bukti</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap');*{font-family:'Inter',sans-serif;} h1,.font-unbounded{font-family:'Space Grotesk',sans-serif!important;}</style>
</head>
<body class="bg-[#faf9f7] text-gray-900 antialiased min-h-screen">
    @php
        $backUrl = request()->headers->get('referer') ?: route('dashboard');
        $backLabel = 'Kembali';
        $showBrand = false;
    @endphp
    @include('partials.nav-auth')

    <div class="max-w-3xl mx-auto px-6 py-10">
        <div class="mb-8">
            <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-1">EVIDENCE LOCKER</p>
<h1 class="font-unbounded text-3xl font-bold text-gray-900">Bukti tersimpan aman.</h1>
            <p class="text-gray-400 text-sm mt-1">Semua bukti otomatis ber-timestamp, hash, dan GPS. Klik laporan untuk melihat & tambah bukti.</p>
        </div>

        <!-- Skeleton for Evidence list -->
        <div id="evidence-skeleton" class="space-y-4">
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden animate-pulse">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <div class="space-y-2 flex-1">
                        <div class="h-4 bg-gray-200 rounded w-1/4"></div>
                        <div class="h-3 bg-gray-200 rounded w-1/3"></div>
                    </div>
                    <div class="h-4 bg-gray-200 rounded w-20"></div>
                </div>
                <div class="p-5 flex items-center justify-between">
                    <div class="space-y-2 flex-1">
                        <div class="h-4 bg-gray-200 rounded w-1/3"></div>
                        <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                    </div>
                    <div class="h-4 bg-gray-200 rounded w-10"></div>
                </div>
            </div>
        </div>

        <!-- Real list container (hidden by default) -->
        <div id="evidence-list" class="space-y-4 hidden"></div>

        <!-- Empty state (hidden by default) -->
        <div id="evidence-empty" class="bg-white border border-gray-200 rounded-2xl p-12 text-center hidden">
            <p class="text-4xl mb-3">🔒</p>
            <p class="font-bold text-gray-900 mb-1">Belum ada laporan.</p>
            <p class="text-gray-400 text-sm mb-4">Buat laporan darurat dan upload bukti untuk mengisi locker ini.</p>
            <a href="/" class="inline-block bg-red-700 hover:bg-red-800 text-white px-5 py-2.5 rounded-xl font-semibold text-sm transition">Buat Laporan</a>
        </div>
    </div>

    <script>
        async function loadEvidences() {
            const skeleton = document.getElementById('evidence-skeleton');
            const list = document.getElementById('evidence-list');
            const empty = document.getElementById('evidence-empty');

            try {
                const res = await fetch('/evidence', { headers: { 'Accept': 'application/json' } });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const data = await res.json();
                const reports = data.reports || [];

                if (reports.length > 0) {
                    list.innerHTML = reports.map(r => {
                        const dateStr = new Date(r.created_at).toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' });
                        const evidencesHtml = (r.evidences || []).map(ev => {
                            const evUrl = ev.file_url.startsWith('data:') 
                                ? ev.file_url 
                                : `/evidences/view/${ev.file_url.split('/').pop()}`;
                            const hashShort = (ev.file_hash || '').substring(0, 32) + '...';
                            const evDateStr = new Date(ev.uploaded_at).toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' });
                            return `
                                <div class="flex items-center justify-between px-5 py-3">
                                    <div>
                                        <p class="text-sm text-gray-700">${escapeHtml(ev.file_type)}</p>
                                        <p class="text-xs text-gray-400 font-mono">${escapeHtml(hashShort)}</p>
                                        <p class="text-xs text-gray-400">${escapeHtml(evDateStr)}</p>
                                    </div>
                                    <a href="${evUrl}" target="_blank" class="text-red-700 hover:text-red-800 text-xs font-semibold transition">Buka &rarr;</a>
                                </div>
                            `;
                        }).join('');

                        return `
                            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                                    <div>
                                        <p class="font-bold text-gray-900">${escapeHtml(r.category)}</p>
                                        <p class="text-gray-400 text-xs">${escapeHtml(dateStr)} · ${(r.evidences || []).length} bukti</p>
                                    </div>
                                    <a href="/tracking/${r.id}" class="text-red-700 hover:text-red-800 text-xs font-semibold transition">Lihat Laporan &rarr;</a>
                                </div>
                                <div class="divide-y divide-gray-50">
                                    ${evidencesHtml}
                                </div>
                            </div>
                        `;
                    }).join('');

                    if (skeleton) skeleton.classList.add('hidden');
                    if (empty) empty.classList.add('hidden');
                    if (list) list.classList.remove('hidden');
                } else {
                    if (skeleton) skeleton.classList.add('hidden');
                    if (list) list.classList.add('hidden');
                    if (empty) empty.classList.remove('hidden');
                }
            } catch (e) {
                console.error('Failed to load evidences:', e);
                if (skeleton) skeleton.classList.add('hidden');
                if (empty) {
                    empty.innerHTML = `<p class="text-red-500 text-sm">Gagal memuat loker bukti: ${e.message}</p>`;
                    empty.classList.remove('hidden');
                }
            }
        }

        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
        }

        loadEvidences();
    </script>
</body>
</html>

