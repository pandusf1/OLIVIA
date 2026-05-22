<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Safora — Pembayaran</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
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
<?php
    $showBrand = false;
    $backUrl = $backUrl ?? request()->headers->get('referer');
    $backLabel = $backLabel ?? 'Kembali';
?>
<?php echo $__env->make('partials.nav-auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="max-w-md mx-auto px-4 py-8">

    
    <div id="step-detail" class="fade-in">

        
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl bg-gray-100 border border-gray-200 overflow-hidden flex-shrink-0">
                <?php if(!empty($priceList->partner->image_url)): ?>
                    <img src="<?php echo e($priceList->partner->image_url); ?>" class="w-full h-full object-cover"/>
                <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center text-gray-400 text-sm">🏢</div>
                <?php endif; ?>
            </div>
            <div>
                <p class="font-semibold text-gray-900 text-sm"><?php echo e($priceList->partner->partner_name ?? 'Partner'); ?></p>
                <p class="text-xs text-gray-400"><?php echo e($priceList->partner->partner_type ?? ''); ?></p>
            </div>
        </div>

        
        <div class="bg-white border border-gray-200 rounded-2xl p-5 mb-4">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-3">Ringkasan Pesanan</p>

            <div class="flex justify-between items-start gap-4 py-3 border-b border-gray-100">
                <div>
                    <p class="font-semibold text-gray-900"><?php echo e($priceList->service_name); ?></p>
                    <?php if($priceList->duration): ?>
                        <p class="text-xs text-gray-500 mt-0.5">⏱ <?php echo e($priceList->duration); ?></p>
                    <?php endif; ?>
                </div>
                <p class="font-black text-gray-900 shrink-0">Rp <?php echo e(number_format($priceList->price, 0, ',', '.')); ?></p>
            </div>

            <div class="flex justify-between items-center py-3">
                <p class="text-sm text-gray-500">Total</p>
                <p class="font-black text-xl text-gray-900">Rp <?php echo e(number_format($priceList->price, 0, ',', '.')); ?></p>
            </div>
        </div>

        
        <div class="bg-white border border-gray-200 rounded-2xl p-5 mb-5">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-3">Metode Pembayaran</p>

            <div class="space-y-2">
                <label class="flex items-center gap-3 p-3 bg-gray-50 border-2 border-gray-900 rounded-xl cursor-pointer">
                    <input type="radio" name="pay_method" value="transfer" checked class="accent-gray-900">
                    <div>
                        <p class="font-semibold text-gray-900 text-sm">Transfer Bank</p>
                        <p class="text-xs text-gray-400">BCA / Mandiri / BNI</p>
                    </div>
                </label>
                <label class="flex items-center gap-3 p-3 bg-[#faf9f7] border border-gray-100 rounded-xl cursor-pointer opacity-60">
                    <input type="radio" name="pay_method" value="ewallet" disabled class="accent-gray-900">
                    <div>
                        <p class="font-semibold text-gray-700 text-sm">E-Wallet</p>
                        <p class="text-xs text-gray-400">GoPay / OVO / DANA</p>
                    </div>
                </label>
            </div>
        </div>

        
        <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 mb-5">
            <p class="text-xs text-amber-700">⚠️ <strong>Demo:</strong> Ini adalah simulasi pembayaran. Tidak ada transaksi nyata yang terjadi.</p>
        </div>

        
        <button onclick="startPayment()" id="btn-pay"
            class="w-full bg-gray-900 hover:bg-black text-white font-bold py-2 rounded-2xl transition active:scale-[0.98] text-base">
            Bayar Sekarang
        </button>

    </div>

    
    <div id="step-processing" class="hidden flex flex-col items-center justify-center py-20 text-center fade-in">
        <div class="w-16 h-16 rounded-2xl border-4 border-gray-100 border-t-gray-900 spinner mb-6"></div>
        <p class="font-bold text-gray-900 text-lg mb-1">Memproses Pembayaran</p>
        <p class="text-gray-400 text-sm">Mohon tunggu sebentar...</p>
    </div>

    
    <div id="step-success" class="hidden flex flex-col items-center py-16 text-center fade-in">
        <div class="w-20 h-20 rounded-full bg-green-100 flex items-center justify-center mb-5 check-pop">
            <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <p class="font-black text-2xl text-gray-900 mb-2">Pembayaran Berhasil!</p>
        <p class="text-gray-500 text-sm mb-1"><?php echo e($priceList->service_name); ?></p>
        <p class="text-gray-400 text-xs mb-8">Kamu sekarang terhubung dengan partner.</p>

        <div class="w-full bg-green-50 border border-green-200 rounded-2xl px-5 py-4 mb-6 text-left">
            <div class="flex justify-between items-center text-sm mb-2">
                <span class="text-gray-500">Layanan</span>
                <span class="font-semibold text-gray-900"><?php echo e($priceList->service_name); ?></span>
            </div>
            <div class="flex justify-between items-center text-sm mb-2">
                <span class="text-gray-500">Partner</span>
                <span class="font-semibold text-gray-900"><?php echo e($priceList->partner->partner_name ?? '-'); ?></span>
            </div>
            <div class="flex justify-between items-center text-sm">
                <span class="text-gray-500">Total</span>
                <span class="font-black text-gray-900">Rp <?php echo e(number_format($priceList->price, 0, ',', '.')); ?></span>
            </div>
        </div>

        <a id="btn-to-chat" href="<?php echo e(route('chat.start', ['partnerId' => $priceList->partner_id])); ?>"
           class="w-full block bg-gray-900 hover:bg-black text-white font-bold py-2 rounded-2xl transition mb-3 text-base">
            Lanjut ke Chat →
        </a>
        <a href="<?php echo e(route('dashboard')); ?>" class="text-sm text-gray-400 hover:text-gray-600 transition">
            Kembali ke Dashboard
        </a>
    </div>

</div>

<script>
function startPayment() {
    document.getElementById('step-detail').classList.add('hidden');
    document.getElementById('step-processing').classList.remove('hidden');

    // Simulate payment processing (1.5s)
    setTimeout(function() {
        // Actually submit to backend
        fetch("<?php echo e(route('pembayaran.pay')); ?>", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
            },
            body: JSON.stringify({ price_list_id: <?php echo e($priceList->id); ?> })
        }).then(function(response) {
            if (!response.ok) throw new Error('Pembayaran gagal diproses.');
            document.getElementById('step-processing').classList.add('hidden');
            document.getElementById('step-success').classList.remove('hidden');
        }).catch(function() {
            document.getElementById('step-processing').classList.add('hidden');
            document.getElementById('step-detail').classList.remove('hidden');
            alert('Pembayaran belum bisa diproses. Coba lagi sebentar.');
        });
    }, 1800);
}
</script>
</body>
</html>
<?php /**PATH D:\CODING\olivia_final\resources\views\pages\user\pembayaran_mock.blade.php ENDPATH**/ ?>