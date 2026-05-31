<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chat — {{ $report->category }} · Safora</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { display: flex; flex-direction: column; height: 100dvh; overflow: hidden; }
        #chat-wrapper { display: flex; flex-direction: column; flex: 1; overflow: hidden; }
        #chat-messages {
            flex: 1;
            overflow-y: auto;
            scroll-behavior: smooth;
            padding: 16px 4px;
            max-width: 42rem; /* max-w-2xl */
            width: 100%;
            margin: 0 auto;
        }
        .bubble-mine { background: #0f172a; color: #ffffff; border-radius: 16px; }
        .bubble-other { background: #ffffff; color: #0f172a; border-radius: 16px; border: 1px solid #e2e8f0; }
        .chat-msg { max-width: 75%; word-break: break-word; }
    </style>
</head>
<body class="bg-[#faf9f7] text-gray-900 antialiased">

    {{-- TOP NAV --}}
    <nav class="bg-white border-b border-gray-200 flex-shrink-0 z-50 relative">
        <div class="absolute left-6 top-1/2 -translate-y-1/2 z-10">
            <a href="/tracking/{{ $report->id }}{{ ($userLat && $userLng) ? '?lat=' . $userLat . '&lng=' . $userLng : '' }}" class="flex items-center gap-1.5 text-gray-500 hover:text-gray-900 transition text-sm font-semibold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                <span class="hidden sm:inline">Laporan</span>
            </a>
        </div>
        <div class="max-w-2xl mx-auto pl-24 pr-4 md:px-4 h-14 flex items-center justify-between gap-3">
            <div class="flex-1 min-w-0">
                <p class="font-bold text-gray-900 text-sm truncate">
                    {{ $report->category }}
                    <span class="text-gray-400 font-normal">· #{{ strtoupper(substr($report->id, 0, 8)) }}</span>
                </p>
                <p class="text-xs text-gray-400">{{ $report->created_at->format('d M Y, H:i') }}
                    @if($report->latitude && $report->longitude)
                        ·
                        <a href="https://maps.google.com/?q={{ $report->latitude }},{{ $report->longitude }}" target="_blank" class="text-red-600 hover:underline">📍 Lokasi</a>
                    @endif
                </p>
            </div>
        </div>
    </nav>

    <div id="chat-wrapper">
        {{-- MESSAGES CONTAINER --}}
        <div id="chat-messages">
            {{-- Rendered dynamically via Javascript updateUI() --}}
        </div>

        {{-- INPUT --}}
        <div class="bg-white border-t border-gray-200 px-4 py-3 flex-shrink-0">
            <div class="max-w-2xl mx-auto flex items-end gap-2">
                <div class="flex-1 bg-gray-50 rounded-2xl border border-gray-200 focus-within:border-gray-400 focus-within:bg-white transition-all">
                    <textarea id="chat-input" rows="1" placeholder="Tulis pesan..."
                        class="w-full resize-none bg-transparent px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:outline-none"
                        style="max-height: 120px; min-height: 44px;"
                        onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendMsg()}"></textarea>
                </div>
                <button onclick="sendMsg()" id="send-btn"
                    class="w-11 h-11 bg-red-700 hover:bg-red-800 text-white rounded-2xl flex items-center justify-center transition flex-shrink-0 disabled:opacity-50">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/>
                    </svg>
                </button>
            </div>
            <p class="text-center text-xs text-gray-400 mt-2">Mengirim sebagai <span class="font-semibold">{{ $currentName }}</span></p>
        </div>
    </div>

<script>
const csrf   = document.querySelector('meta[name="csrf-token"]').content;
const reportId = '{{ $report->id }}';
const userLat  = {{ $userLat !== null ? $userLat : 'null' }};
const userLng  = {{ $userLng !== null ? $userLng : 'null' }};

let serverMessages = [
    @foreach($messages as $msg)
    {
        id: '{{ $msg['id'] }}',
        message: {!! json_encode($msg['message']) !!},
        sender_name: {!! json_encode($msg['sender_name']) !!},
        time: '{{ $msg['time'] }}',
        is_mine: {{ $msg['is_mine'] ? 'true' : 'false' }},
        status: 'sent'
    },
    @endforeach
];

let pendingMessages = [];

function qs() {
    return (userLat && userLng) ? `?lat=${userLat}&lng=${userLng}` : '';
}

function escHtml(s) {
    return String(s ?? '')
        .replace(/&/g,'&amp;')
        .replace(/</g,'&lt;')
        .replace(/>/g,'&gt;')
        .replace(/"/g,'&quot;')
        .replace(/'/g, '&#039;');
}

function messageHtml(m) {
    const isPending = m.status === 'pending' || m.status === 'sending';
    const tickHtml = m.is_mine
        ? (isPending
            ? `<span class="text-[10px] text-slate-400 ml-1" title="Mengirim">✓</span>`
            : `<span class="text-[10px] text-sky-400 font-bold ml-1" title="Terkirim">✓✓</span>`)
        : '';

    return `
        <div class="flex ${m.is_mine ? 'justify-end' : 'justify-start'} mb-3">
            <div class="chat-msg">
                <p class="text-xs font-semibold text-gray-500 mb-1 ${m.is_mine ? 'text-right mr-2' : 'ml-2'}">${escHtml(m.sender_name)}</p>
                <div class="px-3.5 py-2 ${m.is_mine ? 'bubble-mine' : 'bubble-other'} shadow-sm flex flex-col gap-1 min-w-[80px]">
                    <p class="text-sm leading-relaxed whitespace-pre-wrap pr-2">${escHtml(m.message)}</p>
                    <div class="flex items-center justify-end gap-1 text-[10px] self-end mt-0.5 ${m.is_mine ? 'text-slate-300' : 'text-slate-400'}">
                        <span>${escHtml(m.time)}</span>
                        ${tickHtml}
                    </div>
                </div>
            </div>
        </div>
    `;
}

function scrollBottom() {
    const el = document.getElementById('chat-messages');
    if (el) el.scrollTop = el.scrollHeight;
}

function updateUI() {
    const container = document.getElementById('chat-messages');
    if (!container) return;

    const allMessages = [...serverMessages, ...pendingMessages];

    const wasScrolledToBottom = container.scrollHeight - container.clientHeight <= container.scrollTop + 60;

    if (allMessages.length === 0) {
        container.innerHTML = `
            <div class="flex flex-col items-center justify-center h-full py-16 text-center" id="empty-state">
                <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.338-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <p class="font-bold text-gray-700 mb-1">Belum ada pesan</p>
                <p class="text-gray-400 text-sm max-w-xs">Korban, warga sekitar, dan partner yang menangani bisa berkomunikasi di sini.</p>
            </div>
        `;
    } else {
        container.innerHTML = allMessages.map(messageHtml).join('');
    }

    if (wasScrolledToBottom || pendingMessages.length > 0) {
        scrollBottom();
    }
}

async function sendMsg() {
    const input = document.getElementById('chat-input');
    const msg   = input.value.trim();
    if (!msg) return;

    input.value = '';
    input.style.height = '';

    const tempTime = new Date();
    const hh = String(tempTime.getHours()).padStart(2, '0');
    const mm = String(tempTime.getMinutes()).padStart(2, '0');

    // Balon optimis (centang 1) langsung digambar di layar
    const pendingMsg = {
        id: 'temp-' + Date.now(),
        message: msg,
        sender_name: '{{ $currentName }}',
        time: `${hh}:${mm}`,
        is_mine: true,
        status: 'pending'
    };

    pendingMessages.push(pendingMsg);
    updateUI();

    // Jalankan background process untuk kirim ke server
    await processPendingMessages();
}

async function processPendingMessages() {
    if (pendingMessages.length === 0) return;

    for (let i = 0; i < pendingMessages.length; i++) {
        let msg = pendingMessages[i];
        if (msg.status === 'pending') {
            msg.status = 'sending';
            updateUI();

            try {
                const res = await fetch(`/chat/report/${reportId}/send${qs()}`, {
                    method : 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body   : JSON.stringify({ message: msg.message }),
                });

                if (res.ok) {
                    msg.status = 'sent';
                    const sentMsg = pendingMessages.splice(i, 1)[0];
                    i--;
                    serverMessages.push(sentMsg);
                    await doPoll();
                } else {
                    const d = await res.json().catch(() => ({}));
                    alert(d.message || 'Gagal mengirim pesan. Pastikan kamu berhak mengakses chat ini.');
                    pendingMessages.splice(i, 1);
                    i--;
                    updateUI();
                }
            } catch(e) {
                msg.status = 'pending'; // Biarkan nanti dicoba lagi oleh sinkronisasi otomatis
                console.error(e);
            }
        }
    }
    updateUI();
}

async function doPoll() {
    try {
        const res = await fetch(`/chat/report/${reportId}/poll${qs()}`, { headers: { 'Accept': 'application/json' } });
        if (!res.ok) return;

        const data = await res.json();
        const newServerMessages = data.messages || [];

        // Bandingkan isi serverMessages lama dengan yang baru agar tidak re-render berlebihan
        const oldIds = serverMessages.map(m => String(m.id)).join(',');
        const newIds = newServerMessages.map(m => String(m.id)).join(',');

        if (oldIds !== newIds) {
            serverMessages = newServerMessages;
            updateUI();
        }
    } catch(e) { /* ignore */ }
}

// Textarea auto-resize
document.getElementById('chat-input').addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
});

// Init
updateUI();
scrollBottom();
setInterval(doPoll, 2000);
</script>

</body>
</html>
