<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Safora - Darurat</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="min-h-screen bg-red-950 text-white antialiased">
<?php
    $backUrl = request()->headers->get('referer') ?: (auth()->check() ? route('dashboard') : '/');
    $backLabel = 'Kembali';
    $showBrand = false;
?>
<?php echo $__env->make('partials.nav-auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<main class="mx-auto flex min-h-[calc(100vh-72px)] max-w-lg flex-col px-4 pb-6 pt-5">
    <section id="phase-start" class="flex flex-1 flex-col justify-between">
        <div>
            <p class="rounded-full bg-white/10 px-4 py-2 text-center text-xs font-bold uppercase tracking-widest text-red-100">Mode Darurat Safora</p>
            <h1 class="mt-8 text-4xl font-black leading-tight">Tarik napas. Tekan satu tombol.</h1>
            <p class="mt-4 text-lg leading-8 text-red-100">Kami akan mengambil lokasi, mengirim laporan, menghubungi partner, dan memberi tautan tracking. Anda tidak perlu mengetik.</p>
        </div>

        <div class="mt-10">
            <button type="button" id="panic-button" class="w-full rounded-2xl bg-white px-6 py-8 text-2xl font-black text-red-800 shadow-xl shadow-black/20 active:scale-[0.99]">
                SAYA DALAM BAHAYA
            </button>
            <button type="button" id="show-description" class="mt-4 w-full rounded-xl border border-white/20 px-4 py-4 text-sm font-bold text-red-50">
                Tambah deskripsi singkat opsional
            </button>
        </div>

        <section class="mt-6 rounded-xl border border-white/10 bg-white/10 p-4">
            <p class="text-sm font-black">Jika nyawa terancam, hubungi sekarang</p>
            <div class="mt-3 grid grid-cols-3 gap-2">
                <a href="tel:112" class="rounded-lg bg-white px-3 py-3 text-center font-black text-red-800">112</a>
                <a href="tel:119" class="rounded-lg bg-white px-3 py-3 text-center font-black text-red-800">119</a>
                <a href="tel:110" class="rounded-lg bg-white px-3 py-3 text-center font-black text-red-800">110</a>
            </div>
        </section>
    </section>

    <section id="phase-category" class="hidden">
        <div class="mb-5 rounded-xl border border-white/10 bg-white/10 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-red-100">Pilih cepat</p>
                    <h1 class="mt-1 text-2xl font-black">Apa yang paling dekat?</h1>
                </div>
                <div class="h-16 w-16 rounded-full bg-white text-center text-3xl font-black leading-[4rem] text-red-800" id="countdown">7</div>
            </div>
            <p class="mt-3 text-sm leading-6 text-red-100">Jika Anda tidak memilih, Safora otomatis memakai kategori Lainnya.</p>
            <div class="mt-3 h-2 overflow-hidden rounded-full bg-white/10">
                <div id="countdown-bar" class="h-2 rounded-full bg-white" style="width: 100%"></div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <?php $__currentLoopData = [
                ['Kekerasan', 'Perlu perlindungan'],
                ['Kesehatan', 'Butuh bantuan medis'],
                ['Pelecehan', 'Butuh pendampingan'],
                ['Kecelakaan', 'Butuh respons cepat'],
                ['Ancaman', 'Merasa tidak aman'],
                ['Lainnya', 'Saya butuh bantuan'],
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$category, $hint]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <button type="button" data-category="<?php echo e($category); ?>" class="category-button min-h-32 rounded-2xl bg-white p-4 text-left text-red-950 shadow-lg active:scale-[0.99]">
                    <span class="block text-xl font-black"><?php echo e($category); ?></span>
                    <span class="mt-2 block text-sm font-semibold text-gray-500"><?php echo e($hint); ?></span>
                </button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <textarea id="description" rows="2" placeholder="Opsional: tulis 1 kalimat jika aman dilakukan" class="mt-4 hidden w-full resize-none rounded-xl border-0 bg-white px-4 py-4 text-base text-gray-950 placeholder-gray-400"></textarea>
    </section>

    <section id="phase-sending" class="hidden flex flex-1 flex-col justify-center text-center">
        <div class="mx-auto h-16 w-16 animate-pulse rounded-full bg-white"></div>
        <h1 class="mt-6 text-3xl font-black">Mengirim bantuan...</h1>
        <p id="send-status" class="mt-4 text-base leading-7 text-red-100">Mengambil lokasi GPS dan menyiapkan laporan.</p>
    </section>

    <div id="offline-badge" class="fixed bottom-4 left-4 right-4 hidden rounded-xl border border-yellow-300 bg-yellow-100 px-4 py-3 text-sm font-bold text-yellow-900">
        Laporan tersimpan offline. Safora akan mencoba mengirim saat koneksi kembali.
    </div>
</main>

<script>
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const startPhase = document.getElementById('phase-start');
    const categoryPhase = document.getElementById('phase-category');
    const sendingPhase = document.getElementById('phase-sending');
    const countdownNode = document.getElementById('countdown');
    const countdownBar = document.getElementById('countdown-bar');
    const descriptionNode = document.getElementById('description');
    const sendStatus = document.getElementById('send-status');

    let selectedCategory = null;
    let countdownTimer = null;
    let countdownValue = 10;
    let locationPayload = { latitude: null, longitude: null };
    let locationPromise = Promise.resolve();

    function setPhase(phase) {
        startPhase.classList.toggle('hidden', phase !== 'start');
        categoryPhase.classList.toggle('hidden', phase !== 'category');
        sendingPhase.classList.toggle('hidden', phase !== 'sending');
    }

    function requestLocation() {
        if (!navigator.geolocation) {
            sendStatus.textContent = 'GPS tidak tersedia. Laporan tetap dikirim.';
            return;
        }

        locationPromise = new Promise((resolve) => {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    locationPayload = {
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                    };
                    resolve();
                },
                () => {
                    locationPayload = { latitude: null, longitude: null };
                    resolve();
                },
                { enableHighAccuracy: true, timeout: 4500, maximumAge: 0 }
            );
        });
    }

    function startCategoryCountdown() {
        countdownValue = 10;
        countdownNode.textContent = countdownValue;
        countdownBar.style.width = '100%';

        countdownTimer = setInterval(() => {
            countdownValue -= 1;
            countdownNode.textContent = countdownValue;
            countdownBar.style.width = `${Math.max(countdownValue, 0) / 7 * 100}%`;

            if (countdownValue <= 0) {
                clearInterval(countdownTimer);
                submitEmergency(selectedCategory || 'Lainnya');
            }
        }, 1000);
    }

    let isSubmitting = false;

    async function submitEmergency(category) {
        if (isSubmitting) return;
        isSubmitting = true;

        selectedCategory = category || 'Lainnya';
        if (countdownTimer) clearInterval(countdownTimer);
        setPhase('sending');
        sendStatus.textContent = 'Mengambil lokasi terakhir lalu mengirim laporan.';

        await Promise.race([
            locationPromise,
            new Promise((resolve) => setTimeout(resolve, 2000)),
        ]);

        sendStatus.textContent = 'Mengirim laporan dan menghubungi partner terdekat.';

        const payload = {
            category: selectedCategory,
            description: descriptionNode.value || null,
            latitude: locationPayload.latitude,
            longitude: locationPayload.longitude,
            anonymous: 1,
        };

        if (!navigator.onLine) {
            localStorage.setItem('Safora_pending_report', JSON.stringify(payload));
            document.getElementById('offline-badge').classList.remove('hidden');
            sendStatus.textContent = 'Koneksi terputus. Laporan disimpan dan akan dikirim saat online.';
            return;
        }

        try {
            const response = await fetch('/emergency', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(payload),
            });

            if (!response.ok) {
                throw new Error('Gagal mengirim laporan.');
            }

            const data = await response.json();
            window.location.href = data.tracking_url;
        } catch (error) {
            localStorage.setItem('Safora_pending_report', JSON.stringify(payload));
            document.getElementById('offline-badge').classList.remove('hidden');
            sendStatus.textContent = 'Koneksi tidak stabil. Laporan disimpan dan akan dicoba ulang otomatis.';
            isSubmitting = false;
        }
    }

    document.getElementById('panic-button').addEventListener('click', () => {
        requestLocation();
        setPhase('category');
        startCategoryCountdown();
    });

    document.getElementById('show-description').addEventListener('click', () => {
        requestLocation();
        setPhase('category');
        descriptionNode.classList.remove('hidden');
        startCategoryCountdown();
    });

    document.querySelectorAll('.category-button').forEach((button) => {
        button.addEventListener('click', () => submitEmergency(button.dataset.category));
    });

    window.addEventListener('online', async () => {
        const pending = localStorage.getItem('Safora_pending_report');
        if (!pending) return;

        try {
            const response = await fetch('/emergency', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: pending,
            });

            if (response.ok) {
                localStorage.removeItem('Safora_pending_report');
                const data = await response.json();
                window.location.href = data.tracking_url;
            }
        } catch (error) {
            document.getElementById('offline-badge').classList.remove('hidden');
        }
    });

    if (localStorage.getItem('Safora_pending_report')) {
        document.getElementById('offline-badge').classList.remove('hidden');
    }
</script>
</body>
</html>
<?php /**PATH D:\CODING\olivia_final\resources\views/pages/emergency.blade.php ENDPATH**/ ?>