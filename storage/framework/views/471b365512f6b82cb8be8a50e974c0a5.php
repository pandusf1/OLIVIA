<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Safora — Data Partner</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <style> @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap');
        * { font-family: 'Space Grotesk', sans-serif; }
        h1 { font-family: 'Space Grotesk', sans-serif !important; }
        .font-unbounded { font-family: 'Space Grotesk', sans-serif !important; }
    </style>
</head>
<body class="bg-[#faf9f7] text-gray-900 antialiased min-h-screen">
    <?php echo $__env->make('partials.nav-auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="max-w-5xl mx-auto px-6 py-10">

        <?php if(session('success')): ?>
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl mb-6 text-sm">
                ✓ <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <?php
            // dashboard-detail: brand (logo+teks) disembunyikan, tombol kembali akan mengarah ke page sebelumnya.
            $backUrl = $backUrl ?? request()->headers->get('referer');
            $backLabel = $backLabel ?? 'Kembali';
            $showBrand = false;
        ?>

        
        <div class="mb-6">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl border border-gray-100 bg-gray-100 overflow-hidden flex-shrink-0">
                    <?php if(!empty($partner->image_url)): ?>
                        <a href="<?php echo e($partner->image_url); ?>" target="_blank" rel="noopener noreferrer" class="block w-full h-full">
                            <img src="<?php echo e($partner->image_url); ?>" alt="<?php echo e($partner->partner_name); ?>" class="w-full h-full object-cover"/>
                        </a>
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center text-gray-400">—</div>
                    <?php endif; ?>
                </div>


                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-1">INFORMASI DATA PARTNER</p>
<p class="text-gray-900 font-bold text-xl truncate"><?php echo e($partner->partner_name); ?></p>
                    <p class="text-gray-500 text-sm mt-1"><?php echo e($partner->partner_type); ?></p>
                </div>
            </div>
        </div>

        
        <div class="bg-white border border-gray-200 rounded-2xl p-6 mb-4">
            <?php
                $hasLatLng = filled($partner->latitude) && filled($partner->longitude);
            ?>

            <div>
                <h2 class="font-semibold text-gray-900 mb-6">Map & Informasi Partner</h2>

                <div class="grid lg:grid-cols-12 gap-6 items-start">
                    
                    <div class="lg:col-span-5">

                        <div class="relative overflow-hidden rounded-xl bg-[#faf9f7] border border-gray-100 p-4">

                            <div class="absolute inset-0 pointer-events-none" style="
                            background:
                                radial-gradient(circle at 30% 25%, rgba(239,68,68,0.10) 0%, rgba(239,68,68,0.00) 55%),
                                radial-gradient(circle at 70% 65%, rgba(239,68,68,0.08) 0%, rgba(239,68,68,0.00) 50%),
                                repeating-linear-gradient(135deg, rgba(0,0,0,0.05) 0, rgba(0,0,0,0.05) 1px, transparent 1px, transparent 14px),
                                repeating-linear-gradient(45deg, rgba(0,0,0,0.03) 0, rgba(0,0,0,0.03) 1px, transparent 1px, transparent 18px);
                            filter: saturate(1.05) contrast(1.02);
                        ">
                        </div>

                        <div class="relative">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="w-2.5 h-2.5 rounded-full bg-red-700" title="Lokasi partner"></span>
                                <p class="text-xs font-semibold text-gray-600">Lokasi partner</p>
                            </div>

                            <div class="relative w-full h-64 rounded-xl border border-gray-100 bg-white/40 overflow-hidden">
                                <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2">
                                    <div class="w-3 h-3 rounded-full bg-red-700 shadow-md shadow-red-200"></div>
                                    <div class="w-7 h-7 rounded-full border-2 border-red-200/80 absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 animate-pulse" style="animation-duration:2.2s"></div>
                                </div>

                                <?php if($hasLatLng): ?>
                                    <div class="absolute left-3 bottom-3 text-[11px] text-gray-600 bg-white/90 border border-gray-100 rounded-lg px-3 py-2">
                                        <?php echo e($partner->latitude); ?>, <?php echo e($partner->longitude); ?>

                                    </div>
                                <?php else: ?>
                                    <div class="absolute left-3 bottom-3 text-[11px] text-gray-500 bg-white/90 border border-gray-100 rounded-lg px-3 py-2">
                                        Koordinat tidak tersedia
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if($hasLatLng): ?>
                                <div class="mt-3">
                                    <a href="https://maps.google.com/?q=<?php echo e($partner->latitude); ?>,<?php echo e($partner->longitude); ?>" target="_blank" class="inline-flex items-center gap-2 text-sm font-semibold text-red-700 hover:text-red-800 transition underline">
                                        <span>📍</span>
                                        Lihat di Google Maps
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                
                <div class="lg:col-span-7">
                    <div class="flex flex-col gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Email</p>
                            <p class="text-gray-800 font-semibold mt-1"><?php echo e($partner->email ? $partner->email : '-'); ?></p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Kontak</p>
                            <p class="text-gray-800 font-semibold mt-1"><?php echo e($partner->phone ? $partner->phone : '-'); ?></p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Kota</p>
                            <p class="text-gray-800 font-semibold mt-1"><?php echo e($partner->city ? $partner->city : '-'); ?></p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Alamat</p>
                            <p class="text-gray-800 font-semibold mt-1"><?php echo e($partner->address ? $partner->address : '-'); ?></p>
                        </div>
                    </div>

                    <div class="mt-4 bg-[#faf9f7] border border-gray-100 rounded-xl p-4">
                        <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Catatan</p>
                        <p class="text-gray-500 text-sm mt-1">
                            Jam kerja atau informasi lainnya.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>


            <?php
                $canShowPriceList = in_array($partner->partner_type, ['legal', 'counselor'], true);
            ?>

            <?php if(!$canShowPriceList): ?>
                <div class="text-sm text-gray-500 bg-[#faf9f7] border border-gray-100 rounded-xl p-4">
                </div>
            <?php else: ?>
        
        <div class="bg-white border border-gray-200 rounded-2xl p-6">
            <h2 class="font-semibold text-gray-900 mb-4">Daftar Pricelist</h2>

            
            <div id="priceListSelectedState" class="hidden">0</div>

                <?php if($priceLists->count() === 0): ?>
                    <div class="text-sm text-gray-500 bg-[#faf9f7] border border-gray-100 rounded-xl p-4">
                        Belum ada price list untuk partner ini.
                    </div>
                <?php else: ?>

                    <div class="space-y-3">
                        <?php $__currentLoopData = $priceLists; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div
                                data-price-list-id="<?php echo e($pl->id); ?>"
                                data-selected="0"
                                class="price-list-card flex items-start justify-between gap-4 bg-[#faf9f7] border border-gray-100 rounded-xl p-4 cursor-pointer transition"
                            >
                                <div>
                                    <p class="font-semibold text-gray-900"><?php echo e($pl->service_name); ?></p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="font-black text-gray-900 font-semibold">Rp <?php echo e(number_format($pl->price, 0, ',', '.')); ?></p>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                    
                    <div id="selectedPriceListCtaWrap" class="mt-6 hidden">
                        <button
                            id="selectedPriceListCta"
                            type="button"
                            class="w-full bg-gray-900 hover:bg-black text-white font-bold py-2 rounded-2xl transition active:scale-[0.98] text-base"
                        >
                            Lanjut
                        </button>
                    </div>

                <?php endif; ?>
            <?php endif; ?>
        </div>

        
        <div id="servicesModal" class="fixed inset-0 z-50 hidden">
            <div class="absolute inset-0 bg-black/40"></div>
            <div class="relative max-w-md mx-auto mt-20 bg-white border border-gray-200 rounded-2xl shadow-xl overflow-hidden">
                <div class="p-5 border-b border-gray-100">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Layanan Terpilih</p>
                            <h3 class="font-black text-lg text-gray-900">Ringkasan Pricelist</h3>
                        </div>
                        <button
                            type="button"
                            id="modalCloseX"
                            class="text-gray-400 hover:text-gray-600 transition text-sm font-bold px-2 py-1 rounded-full"
                            aria-label="Tutup"
                        >
                            ✕
                        </button>
                    </div>
                </div>

                <div class="p-5">
                    <div id="selectedServicesList" class="space-y-3">
                        
                    </div>

                    <div id="selectedServicesEmpty" class="hidden text-sm text-gray-500 bg-[#faf9f7] border border-gray-100 rounded-xl p-4 mt-1">
                        Tidak ada layanan dipilih.
                    </div>
                </div>

                <div class="p-5 border-t border-gray-100 flex gap-3">
                    <button
                        type="button"
                        id="modalBackBtn"
                        class="flex-1 bg-white hover:bg-gray-50 border border-gray-200 text-gray-900 font-bold py-2 rounded-2xl transition"
                    >
                        Kembali
                    </button>
                    <button
                        type="button"
                        id="modalContinueBtn"
                        class="flex-1 bg-gray-900 hover:bg-black text-white font-bold py-2 rounded-2xl transition active:scale-[0.98]"
                    >
                        Lanjut
                    </button>
                </div>
            </div>
        </div>

</body>
</html>

<script>
(function () {
    const cards = Array.from(document.querySelectorAll('.price-list-card'));
    const ctaWrap = document.getElementById('selectedPriceListCtaWrap');
    const ctaBtn = document.getElementById('selectedPriceListCta');

    const modal = document.getElementById('servicesModal');
    const modalCloseX = document.getElementById('modalCloseX');
    const modalBackBtn = document.getElementById('modalBackBtn');
    const modalContinueBtn = document.getElementById('modalContinueBtn');

    const selectedServicesList = document.getElementById('selectedServicesList');
    const selectedServicesEmpty = document.getElementById('selectedServicesEmpty');

    // selected ids
    const selected = new Set();

    function setCardSelected(card, isSelected) {
        if (isSelected) {
            card.dataset.selected = '1';
            card.classList.add('bg-green-50', 'border-green-200');
            card.classList.remove('bg-[#faf9f7]', 'border-gray-100');
        } else {
            card.dataset.selected = '0';
            card.classList.remove('bg-green-50', 'border-green-200');
            card.classList.add('bg-[#faf9f7]', 'border-gray-100');
        }
    }

    function refreshCta() {
        const count = selected.size;
        ctaWrap.classList.toggle('hidden', count === 0);
    }

    function getSelectedServicesData() {
        const items = [];
        cards.forEach((card) => {
            const id = String(card.dataset.priceListId);
            if (!selected.has(id)) return;

            const titleEl = card.querySelector('p.font-semibold.text-gray-900');
            const durationEl = card.querySelector('p.text-xs.text-gray-500');
            const priceEl = card.querySelector('p.font-black.text-gray-900');
            const currencyEl = card.querySelector('p.text-xs.text-gray-400');

            items.push({
                id,
                serviceName: titleEl ? titleEl.textContent.trim() : '',
                durationText: durationEl ? durationEl.textContent.trim() : '',
                priceText: priceEl ? priceEl.textContent.trim() : '',
                currencyText: currencyEl ? currencyEl.textContent.trim() : '',
            });
        });
        return items;
    }

    function renderModalList() {
        const items = getSelectedServicesData();
        selectedServicesList.innerHTML = '';

        if (items.length === 0) {
            selectedServicesEmpty.classList.remove('hidden');
            return;
        }
        selectedServicesEmpty.classList.add('hidden');

        items.forEach((item) => {
            const row = document.createElement('div');
            row.className = 'flex items-start justify-between gap-3 bg-[#faf9f7] border border-gray-100 rounded-xl p-4';

            row.innerHTML = `
                <div class="min-w-0">
                    <p class="font-semibold text-gray-900 truncate">${escapeHtml(item.serviceName)}</p>
                    ${item.durationText ? `<p class="text-xs text-gray-500 mt-1">${escapeHtml(item.durationText)}</p>` : ''}
                    <p class="text-xs text-gray-400 mt-1">${escapeHtml(item.currencyText)}</p>
                </div>
                <div class="text-right shrink-0">
                    <p class="font-black text-gray-900 font-semibold">${escapeHtml(item.priceText)}</p>
                    <button
                        type="button"
                        class="mt-2 inline-flex bg-white border border-gray-200 hover:bg-gray-50 text-gray-900 text-xs font-bold px-3 py-2 rounded-xl transition"
                        data-remove-id="${escapeHtml(item.id)}"
                    >
                        Hapus
                    </button>
                </div>
            `;

            selectedServicesList.appendChild(row);
        });

        selectedServicesList.querySelectorAll('[data-remove-id]').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                const removeId = String(e.currentTarget.dataset.removeId);
                selected.delete(removeId);

                const card = cards.find(c => String(c.dataset.priceListId) === removeId);
                if (card) setCardSelected(card, false);

                refreshCta();
                renderModalList();
            });
        });
    }

    function openModal() {
        renderModalList();
        modal.classList.remove('hidden');
    }

    function closeModal() {
        modal.classList.add('hidden');
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '<')
            .replace(/>/g, '>')
            .replace(/"/g, '"')
            .replace(/'/g, '&#039;');
    }

    cards.forEach((card) => {
        card.addEventListener('click', () => {
            const id = String(card.dataset.priceListId);
            const isSelected = selected.has(id);

            if (isSelected) {
                selected.delete(id);
                setCardSelected(card, false);
            } else {
                selected.add(id);
                setCardSelected(card, true);
            }
            refreshCta();
        });
    });

    if (ctaBtn) {
        ctaBtn.addEventListener('click', () => {
            openModal();
        });
    }

    modalCloseX && modalCloseX.addEventListener('click', closeModal);
    modalBackBtn && modalBackBtn.addEventListener('click', closeModal);

    modalContinueBtn && modalContinueBtn.addEventListener('click', () => {
        const firstId = Array.from(selected)[0];
        if (!firstId) return;
        const url = `<?php echo e(url('/pembayaran')); ?>/${firstId}`;
        window.location.href = url;
    });
})();
</script>


</body>
</html>


<?php /**PATH D:\CODING\olivia_final\resources\views/pages/user/data_partner.blade.php ENDPATH**/ ?>