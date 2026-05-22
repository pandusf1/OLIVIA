<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Safora — Partner Management</title>

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        *{font-family:'Inter',sans-serif;}
    </style>
</head>

<body class="bg-[#faf9f7] text-gray-900 min-h-screen">

<?php $showBrand = true; ?>
<?php echo $__env->make('partials.nav-auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="max-w-6xl mx-auto px-6 py-10">

    <div class="flex items-center justify-between mb-8">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-1">
                ADMIN PANEL
            </p>

            <h1 class="text-3xl font-black">
                Partner Management
            </h1>

            <p class="text-gray-400 text-sm mt-1">
                Kelola semua partner terverifikasi Safora.
            </p>
        </div>

        <a href="<?php echo e(route('admin.partners.create')); ?>"
           class="bg-gray-900 text-white px-5 py-3 rounded-xl text-sm font-bold hover:bg-black transition">
            + Tambah Partner
        </a>
    </div>

    <div class="space-y-3">

        <?php $__currentLoopData = $partners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $partner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

        <div class="bg-white border border-gray-200 rounded-2xl p-5 flex items-center justify-between">

            <div>
                <div class="flex items-center gap-2 flex-wrap">

                    <h2 class="font-bold text-lg">
                        <?php echo e($partner->partner_name); ?>

                    </h2>

                    <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600">
                        <?php echo e($partner->partner_type); ?>

                    </span>

                    <?php if($partner->verified): ?>
                        <span class="text-xs px-2 py-1 rounded-full bg-green-50 text-green-700">
                            Verified
                        </span>
                    <?php else: ?>
                        <span class="text-xs px-2 py-1 rounded-full bg-red-50 text-red-700">
                            Unverified
                        </span>
                    <?php endif; ?>

                    <?php if($partner->is_active ?? true): ?>
                        <span class="text-xs px-2 py-1 rounded-full bg-blue-50 text-blue-700">
                            Aktif
                        </span>
                    <?php else: ?>
                        <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600">
                            Nonaktif
                        </span>
                    <?php endif; ?>
                </div>

                <p class="text-sm text-gray-400 mt-1">
                    <?php echo e($partner->city); ?>

                </p>

                <p class="text-sm text-gray-500">
                    <?php echo e($partner->email); ?>

                </p>
            </div>

            <div class="flex flex-wrap justify-end gap-2">
                <form method="POST"
                      action="<?php echo e(route('admin.partners.verify', $partner->id)); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PATCH'); ?>

                    <button
                        class="px-4 py-2 rounded-xl text-sm font-semibold border border-gray-200 hover:border-gray-400 transition">

                        <?php echo e($partner->verified ? 'Cabut Verifikasi' : 'Verifikasi'); ?>


                    </button>
                </form>

                <form method="POST"
                      action="<?php echo e(route('admin.partners.active', $partner->id)); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PATCH'); ?>

                    <button
                        class="px-4 py-2 rounded-xl text-sm font-semibold border border-gray-200 hover:border-gray-400 transition">
                        <?php echo e(($partner->is_active ?? true) ? 'Nonaktifkan' : 'Aktifkan'); ?>

                    </button>
                </form>
            </div>

        </div>

        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    </div>
</div>

</body>
</html>
<?php /**PATH D:\CODING\olivia_final\resources\views\pages\admin\partners\index.blade.php ENDPATH**/ ?>