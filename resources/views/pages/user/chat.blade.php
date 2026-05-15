<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SuraRa — Chat</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-[#faf9f7] text-gray-900 min-h-screen">
@php
    $showBrand = false;
    $backUrl = $backUrl ?? request()->headers->get('referer');
    $backLabel = $backLabel ?? 'Kembali';
@endphp
@include('partials.nav-auth')

    <div class="max-w-4xl mx-auto px-6 py-10">

        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-1">CHAT</p>
                <h1 class="text-3xl font-black">Komunikasi dengan partner</h1>
                <p class="text-gray-400 text-sm mt-1">Kamu bisa mengirim pesan kapan saja.</p>
            </div>
            <a href="{{ route('chat.threads') }}" class="text-xs font-semibold text-gray-700 hover:text-gray-900 bg-white border border-gray-200 px-3 py-2 rounded-xl transition">
                Riwayat Chat
            </a>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl">
            <div class="p-4 border-b border-gray-100">
                <p class="text-sm font-semibold text-gray-900">Partner ID: {{ $partnerId }}</p>
            </div>

            <div class="p-4 space-y-3" style="max-height: 60vh; overflow:auto;">
                @forelse($messages as $m)
                    @php
                        $isMe = $m->sender_type === 'user';
                    @endphp
                    <div class="flex {{ $isMe ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[75%] rounded-2xl px-4 py-3 {{ $isMe ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-900' }}">
                            <p class="text-sm break-words">{{ $m->message }}</p>
                            <p class="text-[11px] opacity-70 mt-2">{{ $m->created_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-10">
                        <p class="text-gray-400 text-sm">Belum ada pesan. Mulai chat sekarang.</p>
                    </div>
                @endforelse
            </div>

            <div class="p-4 border-t border-gray-100">
                <form method="POST" action="{{ route('chat.send', ['partnerId' => $partnerId]) }}" class="flex gap-2">
                    @csrf
                    <textarea name="message" rows="2" required placeholder="Tulis pesan..." class="flex-1 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-gray-400 resize-none"></textarea>
                    <button class="bg-gray-900 hover:bg-black text-white px-5 py-3 rounded-xl font-bold transition">Kirim</button>
                </form>
            </div>
        </div>

    </div>
</body>
</html>

