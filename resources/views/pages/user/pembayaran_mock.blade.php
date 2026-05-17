<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Savora — Pembayaran</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#faf9f7] text-gray-900 min-h-screen">
@php
    $showBrand = false;
    $backUrl = $backUrl ?? request()->headers->get('referer');
    $backLabel = $backLabel ?? 'Kembali';
@endphp
@include('partials.nav-auth')

    <div class="max-w-3xl mx-auto px-6 py-10">

        <div class="mb-6">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-1">PEMBAYARAN BOHONGAN</p>
            <h1 class="text-3xl font-black">Pilih Paket & Lanjut Chat</h1>
            <p class="text-gray-400 text-sm mt-1">Partner akan ditampilkan sesuai yang kamu pilih.</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl p-5">
            <div class="space-y-3">
                @forelse($priceLists as $pl)
                    <form method="POST" action="{{ route('pembayaran.pay') }}">
                        @csrf
                        <input type="hidden" name="price_list_id" value="{{ $pl->id }}">
                        <button type="submit" class="w-full text-left bg-[#faf9f7] hover:bg-gray-50 border border-gray-100 rounded-xl p-4 transition">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="font-bold text-gray-900">{{ $pl->service_name }}</p>
                                    @if($pl->duration)
                                        <p class="text-xs text-gray-500 mt-1">Durasi: {{ $pl->duration }}</p>
                                    @endif
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="font-black text-gray-900">Rp {{ number_format($pl->price, 0, ',', '.') }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ $pl->currency }}</p>
                                </div>
                            </div>
                        </button>
                    </form>
                @empty
                    <div class="text-center py-10">
                        <p class="text-gray-400 text-sm">Belum ada price list untuk partner ini.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="mt-5 text-xs text-gray-400">
            *Demo pembayaran. Tidak ada transaksi nyata.
        </div>
    </div>
</body>
</html>


