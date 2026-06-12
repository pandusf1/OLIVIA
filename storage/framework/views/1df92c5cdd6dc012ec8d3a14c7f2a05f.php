<?php if (isset($component)) { $__componentOriginal69dc84650370d1d4dc1b42d016d7226b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b = $attributes; } ?>
<?php $component = App\View\Components\GuestLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('guest-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\GuestLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <h1 class="font-unbounded text-xl font-black text-gray-900 mb-1">Verifikasi OTP</h1>
    <p class="text-gray-400 text-sm mb-6">Kami telah mengirimkan 5-digit kode OTP ke nomor WhatsApp terdaftar Anda (<strong><?php echo e($maskedPhone); ?></strong>).</p>

    <!-- Status Sesi -->
    <?php if (isset($component)) { $__componentOriginal7c1bf3a9346f208f66ee83b06b607fb5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7c1bf3a9346f208f66ee83b06b607fb5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.auth-session-status','data' => ['class' => 'mb-4 bg-green-50 border border-green-200 text-green-700 px-3 py-2 rounded-lg text-sm','status' => session('status')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('auth-session-status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4 bg-green-50 border border-green-200 text-green-700 px-3 py-2 rounded-lg text-sm','status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(session('status'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7c1bf3a9346f208f66ee83b06b607fb5)): ?>
<?php $attributes = $__attributesOriginal7c1bf3a9346f208f66ee83b06b607fb5; ?>
<?php unset($__attributesOriginal7c1bf3a9346f208f66ee83b06b607fb5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7c1bf3a9346f208f66ee83b06b607fb5)): ?>
<?php $component = $__componentOriginal7c1bf3a9346f208f66ee83b06b607fb5; ?>
<?php unset($__componentOriginal7c1bf3a9346f208f66ee83b06b607fb5); ?>
<?php endif; ?>

    <form method="POST" action="<?php echo e(route('password.otp-verify')); ?>">
        <?php echo csrf_field(); ?>

        <div class="mb-4">
            <label class="block text-gray-600 text-xs font-semibold mb-1.5 uppercase tracking-wider text-center">Kode OTP 5-Digit</label>
            <input type="text" name="code" maxlength="5" placeholder="_____" autofocus required
                class="w-full text-center text-2xl tracking-[0.5em] font-bold border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 focus:outline-none transition bg-white">
            <?php if (isset($component)) { $__componentOriginalf94ed9c5393ef72725d159fe01139746 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf94ed9c5393ef72725d159fe01139746 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-error','data' => ['messages' => $errors->get('code'),'class' => 'mt-1 text-red-500 text-xs text-center']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['messages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->get('code')),'class' => 'mt-1 text-red-500 text-xs text-center']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $attributes = $__attributesOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__attributesOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $component = $__componentOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__componentOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
        </div>

        <button type="submit" class="w-full bg-red-700 hover:bg-red-800 text-white py-3 rounded-xl font-bold text-sm transition">Verifikasi OTP</button>
    </form>

    <form action="<?php echo e(route('password.otp-resend')); ?>" method="POST" class="mt-3 js-resend-form">
        <?php echo csrf_field(); ?>
        <button type="submit" data-resend-button data-initial-seconds="<?php echo e((int) session('phone_resend_seconds', 60)); ?>" 
            class="w-full border border-gray-200 hover:bg-gray-50 text-gray-700 py-3 rounded-xl font-semibold text-sm transition disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-400 disabled:hover:bg-gray-100 flex items-center justify-center gap-2">
            <span data-resend-spinner class="hidden h-4 w-4 animate-spin rounded-full border-2 border-gray-300 border-t-gray-800"></span>
            <span data-resend-label>Kirim Ulang OTP</span>
        </button>
    </form>

    <div class="mt-6 text-center">
        <a href="<?php echo e(route('password.request')); ?>" class="text-xs text-gray-400 hover:text-gray-600 transition">Kembali ke halaman sebelumnya</a>
    </div>

    <script>
    document.querySelectorAll('.js-resend-form').forEach((form) => {
        const button = form.querySelector('[data-resend-button]');
        const label = form.querySelector('[data-resend-label]');
        const spinner = form.querySelector('[data-resend-spinner]');
        if (!button || !label) return;

        let remaining = Number(button.dataset.initialSeconds || 0);
        const defaultText = 'Kirim Ulang OTP';
        let timer = null;

        const render = () => {
            if (remaining > 0) {
                button.disabled = true;
                label.textContent = `Kirim ulang dalam ${remaining} detik`;
            } else {
                button.disabled = false;
                label.textContent = defaultText;
                if (timer) window.clearInterval(timer);
            }
        };

        if (remaining > 0) {
            render();
            timer = window.setInterval(() => {
                remaining -= 1;
                render();
            }, 1000);
        }

        form.addEventListener('submit', () => {
            button.disabled = true;
            if (spinner) spinner.classList.remove('hidden');
            label.textContent = 'Mengirim OTP...';
        });
    });
    </script>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $attributes = $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $component = $__componentOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?>
<?php /**PATH D:\CODING\olivia_final\resources\views/auth/forgot-password-otp.blade.php ENDPATH**/ ?>