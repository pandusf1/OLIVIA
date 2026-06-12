<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700;800&display=swap');
        * { font-family: 'Space Grotesk', sans-serif; }
        @keyframes fade-in { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
        .fade-in { animation: fade-in 0.3s ease both; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .spinner { animation: spin 0.8s linear infinite; }
        @keyframes check-pop { 0% { transform: scale(0); } 70% { transform: scale(1.15); } 100% { transform: scale(1); } }
        .check-pop { animation: check-pop 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) both; }
    </style>
</head>
<body class="bg-[#faf9f7] text-gray-900 min-h-screen">
@php
    $showBrand = false;
    $backUrl = $backUrl ?? request()->headers->get('referer');
    $backLabel = $backLabel ?? 'Kembali';

    $hasBank = filled($mitra->bank_name) && filled($mitra->nomor_rekening);
    $hasEwallet = filled($mitra->ewallet_name) && filled($mitra->nomor_ewallet);
    $hasNeither = !$hasBank && !$hasEwallet;
@endphp
@include('partials.nav-auth')

<div class="max-w-md mx-auto px-4 py-8">

    {{-- STEP 1: DETAIL LAYANAN --}}
    <div id="step-detail" class="fade-in">

        {{-- Mitra info mini --}}
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl bg-gray-100 border border-gray-200 overflow-hidden flex-shrink-0">
                @if(!empty($mitra->image_url))
                    <img src="{{ $mitra->image_url }}" class="w-full h-full object-cover"/>
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-400 text-sm">🏢</div>
                @endif
            </div>
            <div>
                <p class="font-semibold text-gray-900 text-sm">{{ $mitra->mitra_name ?? 'Mitra' }}</p>
                @php
                    $typeLabel = match($mitra->mitra_type ?? '') {
                        'ambulance' => 'Medis Darurat',
                        'legal' => 'Bantuan Hukum',
                        'counselor' => 'Psikososial',
                        'pemadam' => 'Pemadam / Rescue',
                        default => 'Mitra Krisis'
                    };
                @endphp
                <p class="text-xs text-gray-400">{{ $typeLabel }}</p>
            </div>
        </div>

        {{-- Ringkasan layanan --}}
        <div class="bg-white border border-gray-200 rounded-2xl p-5 mb-4">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-3">Ringkasan Pesanan</p>

            <div class="divide-y divide-gray-100 max-h-48 overflow-y-auto pr-1">
                @foreach($priceLists as $pl)
                    <div class="flex justify-between items-start gap-4 py-3 first:pt-0">
                        <div>
                            <p class="font-semibold text-gray-950 text-sm leading-snug">{{ $pl->service_name }}</p>
                            @if($pl->duration && (str_contains(strtolower($pl->duration), 'sesi') || str_contains(strtolower($pl->duration), 'session')))
                                <p class="text-xs text-gray-400 mt-0.5">⏱ {{ $pl->duration }}</p>
                            @endif
                        </div>
                        <p class="font-black text-gray-900 text-sm shrink-0">Rp {{ number_format($pl->price, 0, ',', '.') }}</p>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-between items-center py-3 border-t border-gray-100 mt-2">
                <p class="text-sm text-gray-500">Total</p>
                <p class="font-black text-xl text-gray-900">Rp {{ number_format($totalPrice, 0, ',', '.') }}</p>
            </div>

            {{-- Detail Instruksi Pembayaran Dinamis --}}
            <div class="mt-2 pt-3 border-t border-dashed border-gray-100 flex flex-col gap-1 text-sm bg-gray-50/50 p-3 rounded-xl">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Instruksi Pembayaran</p>
                <div id="payment-instruction-detail" class="text-xs text-gray-700 font-medium leading-relaxed">
                    <!-- diisi secara dinamis -->
                </div>
            </div>
        </div>

        {{-- Metode Pembayaran --}}
        <div class="bg-white border border-gray-200 rounded-2xl p-5 mb-5">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-3">Metode Pembayaran</p>

            <div class="space-y-2">
                @if($hasBank)
                    <label class="flex items-center gap-3 p-3 bg-gray-50 border-2 border-gray-900 rounded-xl cursor-pointer payment-option-label" data-type="bank">
                        <input type="radio" name="pay_method" value="bank" checked class="accent-gray-900 payment-radio">
                        <div>
                            <p class="font-semibold text-gray-900 text-sm">Transfer Bank ({{ $mitra->bank_name }})</p>
                            <p class="text-xs text-gray-400">No. Rek: {{ $mitra->nomor_rekening }}</p>
                        </div>
                    </label>
                @else
                    <label class="flex items-center gap-3 p-3 bg-gray-100 border border-gray-200 rounded-xl cursor-not-allowed opacity-50">
                        <input type="radio" name="pay_method" value="bank" disabled class="accent-gray-900">
                        <div>
                            <p class="font-semibold text-gray-400 text-sm">Transfer Bank</p>
                            <p class="text-xs text-gray-300">Tidak tersedia untuk mitra ini</p>
                        </div>
                    </label>
                @endif

                @if($hasEwallet)
                    <label class="flex items-center gap-3 p-3 bg-[#faf9f7] border border-gray-150 rounded-xl cursor-pointer payment-option-label" data-type="ewallet">
                        <input type="radio" name="pay_method" value="ewallet" {{ !$hasBank ? 'checked' : '' }} class="accent-gray-900 payment-radio">
                        <div>
                            <p class="font-semibold text-gray-900 text-sm">E-Wallet ({{ $mitra->ewallet_name }})</p>
                            <p class="text-xs text-gray-400">No. HP: {{ $mitra->nomor_ewallet }}</p>
                        </div>
                    </label>
                @else
                    <label class="flex items-center gap-3 p-3 bg-gray-100 border border-gray-200 rounded-xl cursor-not-allowed opacity-50">
                        <input type="radio" name="pay_method" value="ewallet" disabled class="accent-gray-900">
                        <div>
                            <p class="font-semibold text-gray-400 text-sm">E-Wallet</p>
                            <p class="text-xs text-gray-300">Tidak tersedia untuk mitra ini</p>
                        </div>
                    </label>
                @endif

                @if($hasNeither)
                    <label class="flex items-center gap-3 p-3 bg-gray-50 border-2 border-gray-900 rounded-xl cursor-pointer payment-option-label" data-type="negotiation">
                        <input type="radio" name="pay_method" value="negotiation" checked class="accent-gray-900 payment-radio">
                        <div>
                            <p class="font-semibold text-gray-900 text-sm">Negosiasi / Bayar Nanti</p>
                            <p class="text-xs text-gray-450">Konfirmasi biaya setelah diskusi lewat chat</p>
                        </div>
                    </label>
                @endif
            </div>
        </div>

        {{-- Disclaimer --}}
        <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 mb-5">
            <p class="text-xs text-amber-700">⚠️ <strong>Demo:</strong> Ini adalah simulasi pembayaran. Tidak ada transaksi nyata yang terjadi.</p>
        </div>

        {{-- CTA --}}
        <button onclick="startPayment()" id="btn-pay"
            class="w-full bg-gray-900 hover:bg-black text-white font-bold py-3 rounded-2xl transition active:scale-[0.98] text-base shadow-sm">
            Bayar Sekarang
        </button>

    </div>

    {{-- STEP 2: PROCESSING --}}
    <div id="step-processing" class="hidden flex flex-col items-center justify-center py-20 text-center fade-in">
        <div class="w-16 h-16 rounded-2xl border-4 border-gray-100 border-t-gray-900 spinner mb-6"></div>
        <p class="font-bold text-gray-900 text-lg mb-1">Memproses Pembayaran</p>
        <p class="text-gray-400 text-sm">Mohon tunggu sebentar...</p>
    </div>

    {{-- STEP 3: SUKSES --}}
    <div id="step-success" class="hidden flex flex-col items-center py-16 text-center fade-in">
        <div class="w-20 h-20 rounded-full bg-green-100 flex items-center justify-center mb-5 check-pop">
            <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <p class="font-black text-2xl text-gray-900 mb-2">Transaksi Diproses!</p>
        <p class="text-gray-500 text-sm mb-1" id="success-payment-method"></p>
        <p class="text-gray-400 text-xs mb-8">Kamu sekarang terhubung dengan mitra untuk melanjutkan penanganan.</p>

        <div class="w-full bg-green-50 border border-green-200 rounded-2xl px-5 py-4 mb-6 text-left">
            <div class="flex justify-between items-start text-sm mb-3 border-b border-green-100/50 pb-2">
                <span class="text-green-700 font-medium shrink-0">Layanan</span>
                <div class="text-right font-bold text-green-950 text-xs sm:text-sm">
                    @foreach($priceLists as $pl)
                        <p class="mt-0.5 first:mt-0">{{ $pl->service_name }}</p>
                    @endforeach
                </div>
            </div>
            <div class="flex justify-between items-center text-sm mb-3">
                <span class="text-green-700 font-medium">Mitra</span>
                <span class="font-bold text-green-950 text-xs sm:text-sm">{{ $mitra->mitra_name ?? '-' }}</span>
            </div>
            <div class="flex justify-between items-center text-sm pt-2 border-t border-green-100/50">
                <span class="text-green-700 font-semibold">Total</span>
                <span class="font-black text-green-950 text-base">Rp {{ number_format($totalPrice, 0, ',', '.') }}</span>
            </div>
        </div>

        <a id="btn-to-chat" href="{{ route('chat.start', ['mitraId' => $mitra->id]) }}"
           class="w-full block bg-gray-900 hover:bg-black text-white font-bold py-3 rounded-2xl transition mb-3 text-base shadow-sm">
            Lanjut ke Chat →
        </a>
        <a href="{{ route('dashboard') }}" class="text-sm text-gray-400 hover:text-gray-600 transition">
            Kembali ke Dashboard
        </a>
    </div>

</div>

<script>
(function() {
    const mitraBank = @json($mitra->bank_name);
    const mitraRek = @json($mitra->nomor_rekening);
    const mitraEwallet = @json($mitra->ewallet_name);
    const mitraEwalletNum = @json($mitra->nomor_ewallet);

    const detailEl = document.getElementById('payment-instruction-detail');
    const radios = document.querySelectorAll('.payment-radio');

    function updateInstruction() {
        const checkedRadio = document.querySelector('.payment-radio:checked');
        if (!checkedRadio) return;

        const type = checkedRadio.value;
        let html = '';

        if (type === 'bank') {
            html = `Silakan transfer ke rekening <strong>Bank ${escapeHtml(mitraBank)}</strong>:<br>
                    <span class="text-sm font-bold tracking-wider text-gray-950 block mt-1">${escapeHtml(mitraRek)}</span>
                    <span class="text-gray-400 mt-1 block">a/n ${escapeHtml(@json($mitra->mitra_name))}</span>`;
        } else if (type === 'ewallet') {
            html = `Silakan transfer ke e-wallet <strong>${escapeHtml(mitraEwallet)}</strong>:<br>
                    <span class="text-sm font-bold tracking-wider text-gray-950 block mt-1">${escapeHtml(mitraEwalletNum)}</span>
                    <span class="text-gray-400 mt-1 block">a/n ${escapeHtml(@json($mitra->mitra_name))}</span>`;
        } else if (type === 'negotiation') {
            html = `<strong>Negosiasi / Bayar Nanti</strong>:<br>
                    <span class="text-gray-500 mt-1 block leading-relaxed">Pembayaran belum ditransfer. Selesaikan penawaran dan tata cara pembayaran langsung setelah terhubung di obrolan chat.</span>`;
        }

        detailEl.innerHTML = html;

        // Penyesuaian visual border untuk input radio
        document.querySelectorAll('.payment-option-label').forEach(lbl => {
            const isChecked = lbl.querySelector('.payment-radio').checked;
            lbl.classList.toggle('border-gray-900', isChecked);
            lbl.classList.toggle('bg-gray-50', isChecked);
            lbl.classList.toggle('border-gray-150', !isChecked);
            lbl.classList.toggle('bg-[#faf9f7]', !isChecked);
        });
    }

    function escapeHtml(str) {
        if(!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    radios.forEach(r => r.addEventListener('change', updateInstruction));
    updateInstruction();

    window.startPayment = function() {
        document.getElementById('step-detail').classList.add('hidden');
        document.getElementById('step-processing').classList.remove('hidden');

        const activeRadio = document.querySelector('.payment-radio:checked');
        const payMethod = activeRadio ? activeRadio.value : 'bank';

        let methodText = 'Pembayaran Berhasil!';
        if (payMethod === 'negotiation') {
            methodText = 'Negosiasi Terkirim!';
        } else if (payMethod === 'bank') {
            methodText = 'Pembayaran via Bank Berhasil!';
        } else {
            methodText = 'Pembayaran via E-Wallet Berhasil!';
        }
        document.getElementById('success-payment-method').textContent = methodText;

        setTimeout(function() {
            fetch("{{ route('pembayaran.pay') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    price_list_ids: @json($priceListIds),
                    payment_method: payMethod
                })
            }).then(function(response) {
                if (!response.ok) throw new Error('Pembayaran gagal.');
                document.getElementById('step-processing').classList.add('hidden');
                document.getElementById('step-success').classList.remove('hidden');
            }).catch(function(err) {
                document.getElementById('step-processing').classList.add('hidden');
                document.getElementById('step-detail').classList.remove('hidden');
                alert('Gagal memproses transaksi simulasi ini. Silakan coba kembali.');
            });
        }, 1600);
    }
})();
</script>
</body>
</html>
