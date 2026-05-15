<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SuraRa — Riwayat Chat</title>
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

        <div class="mb-6">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-1">RIWAYAT CHAT</p>
            <h1 class="text-3xl font-black">Percakapan dengan partner</h1>
            <p class="text-gray-400 text-sm mt-1">Semua riwayat chat kamu tersimpan.</p>
        </div>

        @if($threads->count() > 0)
            <div class="space-y-3">
                @foreach($threads as $t)
                    <a href="{{ route('chat.start', ['partnerId' => $t->partner_id]) }}" class="block bg-white border border-gray-200 rounded-2xl p-4 hover:bg-gray-50 transition">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-bold text-gray-900 truncate">{{ $t->partner?->partner_name ?? 'Partner' }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ $t->partner?->partner_type ?? '' }} • {{ $t->last_message_at?->format('d M Y, H:i') ?? 'Belum ada pesan' }}</p>
                            </div>
                            <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 15a4 4 0 0 1-4 4H7l-4 4V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/></svg>
                </div>
                <p class="text-gray-400 text-sm font-medium">Belum ada riwayat chat</p>
                <p class="text-gray-300 text-xs mt-1">Mulai chat melalui partner terdekat.</p>
            </div>
        @endif
    </div>
</body>
</html>

