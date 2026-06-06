<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700;800&display=swap');
        * { font-family: 'Space Grotesk', sans-serif; }
        .chat-layout { transition: max-width .28s ease; }
        .chat-panel { opacity: 0; transform: translateX(16px); pointer-events: none; transition: opacity .25s ease, transform .25s ease; }
        .chat-closed .chat-panel { display: none; }
        .chat-open .chat-panel { display: flex; opacity: 1; transform: translateX(0); pointer-events: auto; }
        @media (max-width: 1023px) {
            .chat-open .thread-list { display: none; }
            .chat-open .chat-panel { display: flex; }
            .chat-closed .chat-panel { display: none; }
        }
    </style>
</head>
<body class="bg-[#faf9f7] text-gray-900 min-h-screen">
<?php
    $showBrand = false;
    $backUrl = $backUrl ?? route('dashboard');
    $backLabel = $backLabel ?? 'Dashboard';
    $hasSelectedChat = filled($partnerId);
    $viewerType = $viewerType ?? 'user';
    $reportContext = $reportContext ?? null;
?>
<?php echo $__env->make('partials.nav-auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div id="chat-page"
     class="chat-layout <?php echo e($hasSelectedChat ? 'chat-open max-w-6xl' : 'chat-closed max-w-4xl'); ?> mx-auto w-full px-4 sm:px-6 py-6">
    <div id="chat-grid" class="<?php echo e($hasSelectedChat ? 'lg:grid lg:grid-cols-[30%_70%] lg:gap-4' : ''); ?> transition-all duration-300">
        <aside id="thread-list" class="thread-list">
            <!-- Skeleton for Thread List -->
            <div id="threads-skeleton" class="space-y-3">
                <div class="animate-pulse bg-white border border-gray-200 rounded-2xl p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gray-200"></div>
                    <div class="flex-1 space-y-2">
                        <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                        <div class="h-3 bg-gray-200 rounded w-2/3"></div>
                    </div>
                </div>
                <div class="animate-pulse bg-white border border-gray-200 rounded-2xl p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gray-200"></div>
                    <div class="flex-1 space-y-2">
                        <div class="h-4 bg-gray-200 rounded w-2/3"></div>
                        <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                    </div>
                </div>
            </div>

            <!-- Dynamic Thread List Container (hidden by default) -->
            <div id="threads-container" class="space-y-3 hidden"></div>

            <!-- Empty Threads State (hidden by default) -->
            <div id="threads-empty" class="text-center py-12 hidden">
                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 15a4 4 0 0 1-4 4H7l-4 4V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/></svg>
                </div>
                <p class="text-gray-400 text-sm font-medium">Belum ada chat yang terbuka</p>
                <p class="text-gray-300 text-xs mt-1">Pilih layanan mitra dan selesaikan pembayaran dulu.</p>
            </div>
        </aside>

        <main id="chat-panel" class="chat-panel flex flex-col w-full h-[calc(100vh-96px)] lg:h-[calc(100vh-132px)]">
            <section class="bg-white border border-gray-200 rounded-2xl flex flex-col overflow-hidden h-full">
                <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-3 justify-between">
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <div id="partner-avatar-wrap">
                            <?php if(!empty($partner->image_url ?? null)): ?>
                                <img id="partner-avatar" src="<?php echo e($partner->image_url); ?>" class="w-9 h-9 rounded-xl object-cover border border-gray-100" alt="">
                            <?php else: ?>
                                <div id="partner-avatar" class="w-9 h-9 rounded-xl bg-gray-100 flex items-center justify-center text-sm">💬</div>
                            <?php endif; ?>
                        </div>

                        <div class="flex-1 min-w-0">
                            <p id="partner-name" class="font-bold text-gray-900 text-sm truncate"><?php echo e($viewerType === 'partner' ? (($reportContext?->anonymous ?? false) ? 'Anonim' : ($reportContext?->user?->name ?? 'Pelapor')) : ($partner->partner_name ?? 'Pilih mitra')); ?></p>
                            <?php
                                $headerType = '';
                                if ($viewerType === 'partner') {
                                    $headerType = 'Pelapor laporan';
                                } else {
                                    $headerType = match($partner->partner_type ?? '') {
                                        'ambulance' => 'Medis Darurat',
                                        'legal' => 'Bantuan Hukum',
                                        'counselor' => 'Psikososial',
                                        'pemadam' => 'Pemadam / Rescue',
                                        default => $partner->partner_type ?? ''
                                    };
                                }
                            ?>
                            <p id="partner-type" class="text-xs text-gray-400"><?php echo e($headerType); ?></p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <a id="btn-tracking" href="<?php echo e($reportContext ? route('tracking.show', $reportContext->id) : '#'); ?>" class="<?php echo e($reportContext ? 'block' : 'hidden'); ?> text-xs font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-full transition border border-gray-200">
                            Konteks Laporan
                        </a>
                        <button type="button" id="btn-back-list" class="lg:hidden text-xs text-gray-400 hover:text-gray-600 transition border border-gray-200 px-3 py-1.5 rounded-full">
                            Kembali
                        </button>
                    </div>
                </div>

                <div id="msg-container" class="flex-1 overflow-y-auto space-y-3 px-4 py-4 bg-[#faf9f7]">
                    <!-- Messages loaded dynamically via JS -->
                </div>

                <form method="POST"
                      action="javascript:void(0);"
                      id="chat-send-form"
                      class="border-t border-gray-100 p-3 flex gap-2 items-end">
                    <?php echo csrf_field(); ?>
                    <textarea name="message" id="msg-input" rows="1" required
                        placeholder="Tulis pesan..."
                        oninput="this.style.height='auto';this.style.height=Math.min(this.scrollHeight,120)+'px'"
                        class="flex-1 bg-transparent border-0 outline-none text-sm resize-none py-2 px-1 placeholder-gray-400 leading-relaxed"></textarea>
                    <button type="submit"
                        class="w-10 h-10 bg-gray-900 hover:bg-black text-white rounded-xl flex items-center justify-center transition active:scale-95 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </button>
                </form>
            </section>
        </main>
    </div>
</div>

<script>
    let currentPartnerId = <?php echo json_encode($partnerId); ?>;
    let currentReportId = <?php echo json_encode($reportContext ? $reportContext->id : null); ?>;
    let currentUserId = null;
    const viewerType = <?php echo json_encode($viewerType); ?>;
    
    let pollTimer = null;
    let syncTimer = null;
    let serverMessages = [];
    let pendingMessages = []; // Messages waiting to be sent (offline queue)

    const page = document.getElementById('chat-page');
    const grid = document.getElementById('chat-grid');
    const panel = document.getElementById('chat-panel');
    const container = document.getElementById('msg-container');
    const form = document.getElementById('chat-send-form');
    const input = document.getElementById('msg-input');
    const partnerName = document.getElementById('partner-name');
    const partnerType = document.getElementById('partner-type');
    const avatarWrap = document.getElementById('partner-avatar-wrap');
    const btnTracking = document.getElementById('btn-tracking');

    // Load initial messages populated by PHP
    const initialMessages = [
        <?php $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        {
            id: '<?php echo e($m->id); ?>',
            sender_type: '<?php echo e($m->sender_type); ?>',
            message: <?php echo json_encode($m->message); ?>,
            time: '<?php echo e($m->created_at->format('H:i')); ?>'
        },
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    ];

    if (initialMessages && initialMessages.length > 0) {
        serverMessages = initialMessages;
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function emptyState() {
        return `
            <div class="flex flex-col items-center justify-center h-full py-16 text-center">
                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-3 text-xl">💬</div>
                <p class="font-semibold text-gray-600 text-sm">Mulai percakapan</p>
                <p class="text-gray-400 text-xs mt-1">Kirim pesan pertamamu.</p>
            </div>
        `;
    }

    function messageHtml(message) {
        const isMe = message.sender_type === viewerType;
        const isPending = message.status === 'pending' || message.status === 'sending';

        const tickHtml = isMe
            ? (isPending
                ? `<span class="text-[10px] text-gray-400 ml-2 tracking-[-2px]" title="Mengirim">✓</span>`
                : `<span class="text-[10px] text-gray-400 ml-2 tracking-[-2px]" title="Terkirim">✓✓</span>`)
            : '';

        return `
            <div class="flex ${isMe ? 'justify-end' : 'justify-start'}">
                <div class="max-w-[78%] rounded-2xl px-4 py-3 ${isMe ? 'bg-gray-900 text-white' : 'bg-white border border-gray-200 text-gray-900'}">
                    <p class="text-sm break-words leading-relaxed whitespace-pre-wrap">${escapeHtml(message.message)}</p>
                    <div class="flex items-center justify-end gap-2 mt-1.5">
                        <p class="text-[11px] ${isMe ? 'text-gray-300' : 'text-gray-400'}">${escapeHtml(message.time)}</p>
                        ${tickHtml}
                    </div>
                </div>
            </div>
        `;
    }

    function openLayout() {
        page.classList.remove('chat-closed', 'max-w-4xl');
        page.classList.add('chat-open', 'max-w-6xl');
        grid.classList.add('lg:grid', 'lg:grid-cols-[30%_70%]', 'lg:gap-4');
    }

    function closeMobileChat() {
        page.classList.remove('chat-open');
        page.classList.add('chat-closed');
    }

    function setActiveLink(partnerId) {
        document.querySelectorAll('[data-chat-link]').forEach((link) => {
            link.classList.toggle('border-gray-900', link.dataset.partnerId === partnerId);
            link.classList.toggle('border-gray-200', link.dataset.partnerId !== partnerId);
        });
    }

    function setPartnerHeader(link) {
        partnerName.textContent = link.dataset.partnerName || 'Mitra';
        partnerType.textContent = link.dataset.partnerType || '';

        if (link.dataset.partnerImage) {
            avatarWrap.innerHTML = `<img id="partner-avatar" src="${escapeHtml(link.dataset.partnerImage)}" class="w-9 h-9 rounded-xl object-cover border border-gray-100" alt="">`;
        } else {
            avatarWrap.innerHTML = `<div id="partner-avatar" class="w-9 h-9 rounded-xl bg-gray-100 flex items-center justify-center text-sm">💬</div>`;
        }

        if (currentReportId) {
            btnTracking.href = `/tracking/${currentReportId}`;
            btnTracking.classList.remove('hidden');
            btnTracking.classList.add('block');
        } else {
            btnTracking.classList.add('hidden');
            btnTracking.classList.remove('block');
        }
    }

    function updateUI() {
        // Render server messages first, then attach pending messages safely.
        const allMessages = [...serverMessages, ...pendingMessages];

        const wasScrolledToBottom = container.scrollHeight - container.clientHeight <= container.scrollTop + 50;

        if (allMessages.length === 0) {
            container.innerHTML = emptyState();
        } else {
            container.innerHTML = allMessages.map(messageHtml).join('');
        }

        if (wasScrolledToBottom || pendingMessages.length > 0) {
            container.scrollTop = container.scrollHeight;
        }
    }

    async function processPendingMessages() {
        if (!currentPartnerId || pendingMessages.length === 0) return;
        
        for (let i = 0; i < pendingMessages.length; i++) {
            let msg = pendingMessages[i];
            if (msg.status === 'pending') {
                msg.status = 'sending';
                updateUI(); // ensure single tick is rendered
                
                try {
                    const params = new URLSearchParams();
                    if (currentReportId) params.append('report_id', currentReportId);
                    if (currentUserId) params.append('user_id', currentUserId);
                    
                    const suffix = params.toString() ? `?${params.toString()}` : '';
                    
                    const csrf = document.querySelector('input[name="_token"]')?.value;
                    const response = await fetch(`/chat/send/${currentPartnerId}${suffix}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ message: msg.message }),
                    });

                    if (response.ok) {
                        msg.status = 'sent';
                        const sentMsg = pendingMessages.splice(i, 1)[0];
                        i--; // adjust index since we removed an item
                        serverMessages.push(sentMsg); // Add locally instantly to prevent UI flicker
                        fetchMessages(); // refresh from server asynchronously
                    } else {
                        msg.status = 'pending'; // revert to pending to retry later
                    }
                } catch (e) {
                    msg.status = 'pending'; // network error, keep as pending to retry later
                }
            }
        }
        updateUI(); // apply final state so single ticks persist if offline
    }

    async function fetchMessages() {
        if (!currentPartnerId) return;

        try {
            const params = new URLSearchParams();
            if (currentReportId) params.append('report_id', currentReportId);
            if (currentUserId) params.append('user_id', currentUserId);
            
            const suffix = params.toString() ? `?${params.toString()}` : '';
            const response = await fetch(`/chat/messages/${currentPartnerId}${suffix}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (response.ok) {
                const data = await response.json();
                const newServerMessages = data.messages || [];
                
                if (JSON.stringify(serverMessages) !== JSON.stringify(newServerMessages)) {
                    serverMessages = newServerMessages;
                    updateUI();
                }
            }
        } catch (e) {
            console.error('Failed to fetch messages', e);
            // Silently ignore to keep optimistic UI intact during offline mode
        }
    }

    function startPolling() {
        if (pollTimer) clearInterval(pollTimer);
        if (syncTimer) clearInterval(syncTimer);
        
        if (!currentPartnerId) return;

        fetchMessages();
        pollTimer = setInterval(fetchMessages, 2000);
        syncTimer = setInterval(processPendingMessages, 3000);
    }

    // loadThreads dynamically renders the sidebar list
    async function loadThreads() {
        const skeleton = document.getElementById('threads-skeleton');
        const container = document.getElementById('threads-container');
        const empty = document.getElementById('threads-empty');

        try {
            const res = await fetch('/chat/threads', { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();
            const threads = data.threads || [];

            if (threads.length > 0) {
                container.innerHTML = threads.map(t => {
                    const active = String(currentPartnerId) === String(t.partner_id);
                    const imageHtml = t.partner_image 
                        ? `<img src="${t.partner_image}" class="w-10 h-10 rounded-xl object-cover border border-gray-100" alt="">`
                        : `<div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center text-sm">💬</div>`;
                    
                    return `
                        <a href="${t.threadHref}"
                           data-chat-link
                           data-partner-id="${t.partner_id}"
                           data-report-id="${t.report_id || ''}"
                           data-user-id="${t.user_id}"
                           data-partner-name="${escapeHtml(t.threadName)}"
                           data-partner-type="${escapeHtml(t.threadType)}"
                           data-partner-image="${t.partner_image}"
                           class="block bg-white border ${active ? 'border-gray-900' : 'border-gray-200'} rounded-2xl p-4 hover:bg-gray-50 transition">
                            <div class="flex items-center gap-3">
                                ${imageHtml}
                                <div class="min-w-0 flex-1">
                                    <p class="font-bold text-gray-900 text-sm truncate">${escapeHtml(t.threadName)}</p>
                                    <p class="text-xs text-gray-400 mt-1 truncate">
                                        ${escapeHtml(t.threadType)} • ${escapeHtml(t.last_message_time)}
                                    </p>
                                </div>
                            </div>
                        </a>
                    `;
                }).join('');

                // Re-bind click event listeners to dynamic links
                container.querySelectorAll('[data-chat-link]').forEach((link) => {
                    link.addEventListener('click', (event) => {
                        event.preventDefault();

                        currentPartnerId = link.dataset.partnerId;
                        currentReportId = link.dataset.reportId || null;
                        currentUserId = link.dataset.userId || null;
                        serverMessages = [];
                        pendingMessages = [];
                        
                        setPartnerHeader(link);
                        setActiveLink(currentPartnerId);
                        openLayout();
                        history.pushState({ partnerId: currentPartnerId }, '', link.href);
                        startPolling();
                    });
                });

                if (skeleton) skeleton.classList.add('hidden');
                if (empty) empty.classList.add('hidden');
                container.classList.remove('hidden');

                // Initialize chat layout if partnerId is selected in the URL on page load
                if (currentPartnerId) {
                    const activeLink = container.querySelector(`[data-chat-link][data-partner-id="${currentPartnerId}"]`);
                    if (activeLink) {
                        currentUserId = activeLink.dataset.userId || null;
                        setPartnerHeader(activeLink);
                        openLayout();
                        setActiveLink(currentPartnerId);
                        updateUI();
                        startPolling();
                    }
                }
            } else {
                if (skeleton) skeleton.classList.add('hidden');
                container.classList.add('hidden');
                if (empty) empty.classList.remove('hidden');
            }
        } catch (e) {
            console.error('Failed to load threads:', e);
            if (skeleton) skeleton.classList.add('hidden');
            if (empty) {
                empty.innerHTML = `<p class="text-red-500 text-sm">Gagal memuat obrolan: ${e.message}</p>`;
                empty.classList.remove('hidden');
            }
        }
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    document.getElementById('btn-back-list')?.addEventListener('click', () => {
        closeMobileChat();
        history.pushState({}, '', '<?php echo e(route('chat.threads')); ?>');
    });

    input?.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
        }
    });

    form?.addEventListener('submit', (event) => {
        event.preventDefault();

        const message = input.value.trim();
        if (!currentPartnerId || !message) return;

        input.value = '';
        input.style.height = 'auto';

        const tempTime = new Date();
        const hh = String(tempTime.getHours()).padStart(2, '0');
        const mm = String(tempTime.getMinutes()).padStart(2, '0');
        
        const pendingMsg = {
            id: Date.now(),
            sender_type: viewerType,
            message: message,
            time: `${hh}:${mm}`,
            status: 'pending'
        };

        pendingMessages.push(pendingMsg);
        updateUI();
        processPendingMessages(); // try sending immediately
    });

    window.addEventListener('popstate', () => window.location.reload());

    // Initialize layout on page load
    loadThreads();
    if (!currentPartnerId) {
        updateUI(); // show empty state
    }
</script>
</body>
</html>
<?php /**PATH D:\CODING\olivia_final\resources\views/pages/user/chat.blade.php ENDPATH**/ ?>