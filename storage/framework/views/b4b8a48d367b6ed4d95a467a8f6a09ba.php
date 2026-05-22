<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Safora — Trusted Contact</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap');*{font-family:'Inter',sans-serif;} h1,.font-unbounded{font-family:'Space Grotesk',sans-serif!important;}</style>
</head>
<body class="bg-[#faf9f7] text-gray-900 antialiased min-h-screen">
    <?php
        $backUrl = request()->headers->get('referer') ?: route('dashboard');
        $backLabel = 'Kembali';
        $showBrand = false;
    ?>
    <?php echo $__env->make('partials.nav-auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="max-w-lg mx-auto px-6 py-10">
        <div class="mb-8">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-1">TRUSTED CONTACT</p>
            <h1 class="font-unbounded text-3xl font-bold text-gray-900">Kontak Terpercaya</h1>
            <p class="text-gray-400 text-sm mt-1">Orang-orang ini otomatis dapat alert WhatsApp saat kamu menekan panic button, lengkap dengan lokasi dan link tracking.</p>
        </div>

        <?php if(session('success')): ?><div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl mb-6 text-sm">✓ <?php echo e(session('success')); ?></div><?php endif; ?>

<div class="bg-white border border-gray-200 rounded-2xl p-6 mb-6">
            <h2 class="font-semibold text-gray-900 mb-4">+ Tambah Kontak</h2>
            <form action="/trusted-contact" method="POST" class="space-y-3">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">Nama</label>
                    <input type="text" name="contact_name" placeholder="Nama kontak..." class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">No. WhatsApp</label>
                    <input type="text" name="contact_phone" placeholder="628xxxxxxxxx" class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition" required>
                    <p class="text-gray-400 text-xs mt-1">Format internasional tanpa + (contoh: 6281234567890)</p>
                </div>
<button class="w-full bg-gray-900 hover:bg-gray-700 text-white py-3 rounded-xl font-semibold text-sm transition">Tambah Kontak</button>
            </form>
        </div>

        <?php if($contacts->count() > 0): ?>
        <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
<div class="px-5 py-3 border-b border-gray-100">
                <p class="font-semibold text-gray-900 text-sm">Kontak Tersimpan (<?php echo e($contacts->count()); ?>)</p>
            </div>
            <div class="divide-y divide-gray-50">
                <?php $__currentLoopData = $contacts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex items-center justify-between px-5 py-4">
                    <div>
                        <p class="font-semibold text-gray-900"><?php echo e($c->contact_name); ?></p>
                        <p class="text-gray-400 text-sm"><?php echo e($c->contact_phone); ?></p>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="<?php echo e(route('trusted-contact.edit', $c->id)); ?>" class="text-gray-600 hover:text-gray-900 text-sm font-semibold transition">Edit</a>
                        <form action="/trusted-contact/<?php echo e($c->id); ?>" method="POST">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button class="text-red-500 hover:text-red-700 text-sm font-semibold transition">Hapus</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <?php else: ?>
        <div class="bg-white border border-gray-200 rounded-2xl p-8 text-center">
            <p class="text-gray-400 text-sm">Belum ada kontak tersimpan.</p>
        </div>
        <?php endif; ?>

        <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 mt-6">
            <p class="text-amber-800 text-xs">💡 Tambahkan keluarga, pasangan, atau teman dekat. Mereka akan menerima lokasi GPS, status darurat, dan link tracking otomatis saat kamu panic.</p>
        </div>
    </div>
</body>
</html>

<?php /**PATH D:\CODING\olivia_final\resources\views\trusted-contacts\index.blade.php ENDPATH**/ ?>