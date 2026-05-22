<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Safora - Tracking Darurat</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="min-h-screen bg-gray-50 text-gray-950 antialiased">
<?php
    $backUrl = request()->headers->get('referer') ?: (auth()->check() ? route('dashboard') : '/');
    $backLabel = 'Kembali';
    $showBrand = false;
?>
<?php echo $__env->make('partials.nav-auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<main class="mx-auto max-w-5xl px-4 pb-10 pt-5 sm:px-6">
    <section class="rounded-lg border border-red-100 bg-white p-5 shadow-sm">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-red-700">Laporan #<span data-field="short_id"><?php echo e($livePayload['report']['short_id']); ?></span></p>
                <h1 class="mt-2 text-2xl font-black sm:text-3xl" data-field="current_status"><?php echo e($livePayload['current_status']); ?></h1>
                <p class="mt-3 max-w-2xl text-base leading-7 text-gray-700" data-field="human_message"><?php echo e($livePayload['human_message']); ?></p>
            </div>
            <div class="rounded-full border border-red-200 bg-red-50 px-3 py-1 text-xs font-black uppercase text-red-700" data-field="urgency"><?php echo e($livePayload['report']['urgency_level']); ?></div>
        </div>

        <div class="mt-5 grid gap-3 sm:grid-cols-3">
            <div class="rounded-lg bg-gray-50 p-4">
                <p class="text-xs font-bold uppercase text-gray-500">Kategori</p>
                <p class="mt-1 font-black" data-field="category"><?php echo e($livePayload['report']['category']); ?></p>
            </div>
            <div class="rounded-lg bg-gray-50 p-4">
                <p class="text-xs font-bold uppercase text-gray-500">Estimasi Respons</p>
                <p class="mt-1 font-black" data-field="eta"><?php echo e($livePayload['eta']); ?></p>
            </div>
            <div class="rounded-lg bg-gray-50 p-4">
                <p class="text-xs font-bold uppercase text-gray-500">Lokasi</p>
                <a data-field="maps_url" href="<?php echo e($livePayload['report']['location']['maps_url'] ?? '#'); ?>" target="_blank" class="mt-1 inline-block font-black text-red-700 underline">
                    <?php echo e($livePayload['report']['location']['verified'] ? 'GPS diterima' : 'GPS belum tersedia'); ?>

                </a>
            </div>
        </div>
    </section>

    <section class="mt-4 rounded-lg border border-orange-200 bg-orange-50 p-4">
        <p class="text-sm font-black text-orange-950">Langkah berikutnya</p>
        <p class="mt-1 text-sm leading-6 text-orange-900" data-field="next_instruction"><?php echo e($livePayload['next_instruction']); ?></p>
        <p class="mt-2 text-xs leading-5 text-orange-800" data-field="escalation_message"><?php echo e($livePayload['escalation_message']); ?></p>
    </section>

    <div class="mt-5 grid gap-5 lg:grid-cols-[1.1fr_.9fr]">
        <div class="space-y-5">
            <section id="assigned-card" class="hidden rounded-lg border border-green-200 bg-green-50 p-5">
                <p class="text-xs font-bold uppercase tracking-widest text-green-700">Sedang Menangani</p>
                <h2 class="mt-2 text-xl font-black text-green-950" data-field="assigned_name"></h2>
                <p class="mt-1 text-sm text-green-800" data-field="assigned_detail"></p>
                <a id="chat-link" href="#" class="mt-4 inline-flex w-full justify-center rounded-lg bg-green-800 px-4 py-3 text-sm font-black text-white sm:w-auto">Buka Chat Krisis</a>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-5">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-black">Diteruskan ke Partner</h2>
                        <p class="text-sm text-gray-500">Status ini diperbarui otomatis setiap beberapa detik.</p>
                    </div>
                    <span id="live-dot" class="rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-700">Live</span>
                </div>
                <div id="routed-partners" class="space-y-3"></div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-5">
                <h2 class="text-lg font-black">Update Terbaru</h2>
                <div id="timeline" class="mt-4 space-y-4"></div>
            </section>
        </div>

        <aside class="space-y-5">
            <section class="rounded-lg border border-red-200 bg-white p-5">
                <h2 class="text-lg font-black text-red-800">Nomor Darurat</h2>
                <p class="mt-1 text-sm text-gray-600">Jika kondisi memburuk, hubungi bantuan resmi sekarang. Safora berjalan paralel.</p>
                <div id="hotlines" class="mt-4 grid gap-2"></div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-5">
                <h2 class="text-lg font-black">Pesan Partner</h2>
                <div id="latest-messages" class="mt-4 space-y-3"></div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-5">
                <h2 class="text-lg font-black">Bukti & Saksi</h2>
                <p class="mt-1 text-sm text-gray-600">Tambah bukti jika aman dilakukan. Saksi bisa memakai ID laporan ini.</p>
                <button type="button" onclick="document.getElementById('upload-area').classList.toggle('hidden')" class="mt-4 w-full rounded-lg border border-gray-300 px-4 py-3 text-sm font-black">Tambah Bukti</button>
                <div id="upload-area" class="hidden mt-4 rounded-lg border border-dashed border-gray-200 p-4">
                    <form action="/tracking/<?php echo e($report->id); ?>/evidence" method="POST" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <input type="file" name="evidence[]" multiple class="w-full text-sm">
                        <button class="mt-3 w-full rounded-lg bg-gray-950 px-4 py-3 text-sm font-black text-white">Upload</button>
                    </form>
                </div>
                <code class="mt-4 block break-all rounded-lg bg-gray-50 p-3 text-xs text-gray-600"><?php echo e($report->id); ?></code>
            </section>
        </aside>
    </div>
</main>

<script>
    const initialPayload = <?php echo json_encode($livePayload, 15, 512) ?>;
    const reportId = <?php echo json_encode($report->id, 15, 512) ?>;
    let lastPayload = null;

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function setText(selector, value) {
        const node = document.querySelector(selector);
        if (node) node.textContent = value || '-';
    }

    function statusLabel(status) {
        return {
            waiting: ['Menunggu', 'bg-gray-100 text-gray-700'],
            reviewing: ['Meninjau', 'bg-yellow-100 text-yellow-900'],
            unavailable: ['Tidak tersedia', 'bg-orange-100 text-orange-800'],
            accepted: ['Menerima', 'bg-green-100 text-green-800'],
        }[status] || ['Menunggu', 'bg-gray-100 text-gray-700'];
    }

    function render(payload) {
        lastPayload = payload;
        setText('[data-field="current_status"]', payload.current_status);
        setText('[data-field="human_message"]', payload.human_message);
        setText('[data-field="eta"]', payload.eta);
        setText('[data-field="next_instruction"]', payload.next_instruction);
        setText('[data-field="escalation_message"]', payload.escalation_message);
        setText('[data-field="urgency"]', payload.report.urgency_level);
        setText('[data-field="category"]', payload.report.category);

        const maps = document.querySelector('[data-field="maps_url"]');
        if (maps && payload.report.location.maps_url) {
            maps.href = payload.report.location.maps_url;
            maps.textContent = payload.report.location.verified ? 'GPS diterima' : 'Buka peta';
        }

        const assigned = document.getElementById('assigned-card');
        if (payload.assigned_partner) {
            assigned.classList.remove('hidden');
            setText('[data-field="assigned_name"]', payload.assigned_partner.name);
            setText('[data-field="assigned_detail"]', `${payload.assigned_partner.handler_name || 'Tim partner'} - ${payload.assigned_partner.specialization}`);
            const chatLink = document.getElementById('chat-link');
            chatLink.href = `/chat/messages/${payload.assigned_partner.id}?report_id=${reportId}`;
        } else {
            assigned.classList.add('hidden');
        }

        document.getElementById('routed-partners').innerHTML = (payload.routed_partners || []).map((partner) => {
            const [label, klass] = statusLabel(partner.status);
            return `
                <div class="rounded-lg border border-gray-200 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-black">${escapeHtml(partner.name)}</p>
                            <p class="mt-1 text-sm text-gray-500">${escapeHtml(partner.specialization)}${partner.city ? ' - ' + escapeHtml(partner.city) : ''}</p>
                            <p class="mt-1 text-xs text-gray-500">${escapeHtml(partner.distance || 'Jarak belum tersedia')} - estimasi ${escapeHtml(partner.estimated_response)}</p>
                        </div>
                        <span class="rounded-full px-3 py-1 text-xs font-bold ${klass}">${label}</span>
                    </div>
                </div>
            `;
        }).join('') || '<p class="rounded-lg bg-gray-50 p-4 text-sm text-gray-500">Kami sedang mencari partner yang relevan.</p>';

        document.getElementById('timeline').innerHTML = (payload.timeline || []).map((event) => `
            <div class="flex gap-3">
                <div class="mt-1 h-2.5 w-2.5 rounded-full bg-red-700"></div>
                <div>
                    <p class="text-sm font-bold text-gray-900">${escapeHtml(event.message)}</p>
                    <p class="mt-1 text-xs text-gray-400">${escapeHtml(event.time)}</p>
                </div>
            </div>
        `).join('');

        document.getElementById('hotlines').innerHTML = (payload.hotlines || []).map((hotline) => `
            <a href="tel:${escapeHtml(hotline.phone)}" class="flex items-center justify-between rounded-lg border border-red-100 bg-red-50 px-4 py-3">
                <span class="text-sm font-bold text-red-950">${escapeHtml(hotline.label)}</span>
                <span class="text-lg font-black text-red-800">${escapeHtml(hotline.phone)}</span>
            </a>
        `).join('');

        document.getElementById('latest-messages').innerHTML = (payload.latest_messages || []).map((message) => `
            <div class="rounded-lg bg-gray-50 p-3">
                <p class="text-sm text-gray-800">${escapeHtml(message.message)}</p>
                <p class="mt-1 text-xs text-gray-400">${escapeHtml(message.time)}</p>
            </div>
        `).join('') || '<p class="rounded-lg bg-gray-50 p-4 text-sm text-gray-500">Belum ada pesan partner. Tetap pantau halaman ini.</p>';
    }

    async function pollLive() {
        try {
            const response = await fetch(`/tracking/${reportId}/live`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (response.ok) {
                render(await response.json());
                document.getElementById('live-dot').textContent = 'Live';
            }
        } catch (error) {
            document.getElementById('live-dot').textContent = 'Mencoba ulang';
        }
    }

    render(initialPayload);
    setInterval(pollLive, 4000);
</script>
</body>
</html>
<?php /**PATH D:\CODING\olivia_final\resources\views/pages/tracking.blade.php ENDPATH**/ ?>