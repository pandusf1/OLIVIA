<?php
    // show back only when we're inside dashboard flow (user/admin/partner)
    // and not on the initial dashboard page (/dashboard).
    $isDashboardInitial = in_array(request()->route()?->getName(), ['dashboard', 'admin.index', 'partner.index']);

    // Default fallback to dashboard if opened directly (no history)
    $fallbackUrl = auth()->check() ? route('dashboard') : url('/');

    // If backUrl is provided explicitly (e.g., from controller/view), we use it as fallback.
    // In many pages, $backUrl is assigned request()->headers->get('referer').
    $resolvedFallbackUrl = (isset($backUrl) && $backUrl) ? $backUrl : $fallbackUrl;

    // Prevent redirecting to the same page or temporary/auth pages that can loop/error
    $currentPath = trim(parse_url(url()->current(), PHP_URL_PATH), '/');
    $fallbackPath = trim(parse_url($resolvedFallbackUrl, PHP_URL_PATH), '/');

    if ($currentPath === $fallbackPath
        || str_contains($fallbackPath, 'login')
        || str_contains($fallbackPath, 'register')
        || str_contains($fallbackPath, 'emergency')
        || str_contains($fallbackPath, 'chat')) {
        $resolvedFallbackUrl = $fallbackUrl;
    }

    $resolvedBackLabel = $backLabel ?? 'Kembali';
    $hideBrand = !$isDashboardInitial;
    
    // Only show back button if not on initial dashboard
    $showBackButton = !$isDashboardInitial;

    $authName = auth()->check() ? auth()->user()->name : '';
    $mobileAuthName = mb_strlen($authName) > 14 ? mb_substr($authName, 0, 14) : $authName;
?>

<nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
    <div class="max-w-6xl mx-auto px-6 h-14 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <?php if($showBackButton): ?>
                <a href="<?php echo e($resolvedFallbackUrl); ?>" 
                   class="text-gray-400 hover:text-gray-700 text-sm transition flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    <?php echo e($resolvedBackLabel); ?>

                </a>
                <div class="w-px h-4 bg-gray-200"></div>
            <?php endif; ?>

            <?php if(!$hideBrand): ?>
                <a href="<?php echo e(auth()->check() ? route('dashboard') : '/'); ?>" class="flex items-center gap-2">
                    <div class="w-7 h-7 bg-red-700 rounded-lg flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944z" clip-rule="evenodd"/></svg>
                    </div>
                    <span class="font-bold text-gray-900 text-sm">Safora</span>
                </a>
            <?php endif; ?>
        </div>
        <div class="flex items-center gap-4">
            <?php if(auth()->guard()->check()): ?>
                <div class="flex items-center">
                    <?php if (isset($component)) { $__componentOriginaldf8083d4a852c446488d8d384bbc7cbe = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dropdown','data' => ['align' => 'right','width' => '48']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['align' => 'right','width' => '48']); ?>
                         <?php $__env->slot('trigger', null, []); ?> 
                            <button class="flex items-center text-gray-900 text-sm font-medium hover:text-red-700 transition gap-1 focus:outline-none">
                                <span class="sm:hidden"><?php echo e($mobileAuthName); ?></span>
                                <span class="hidden sm:inline"><?php echo e($authName); ?></span>
                                <svg class="fill-current h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                         <?php $__env->endSlot(); ?>

                         <?php $__env->slot('content', null, []); ?> 
                            <?php if (isset($component)) { $__componentOriginal68cb1971a2b92c9735f83359058f7108 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal68cb1971a2b92c9735f83359058f7108 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dropdown-link','data' => ['href' => '/settings']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dropdown-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => '/settings']); ?>
                                Pengaturan
                             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal68cb1971a2b92c9735f83359058f7108)): ?>
<?php $attributes = $__attributesOriginal68cb1971a2b92c9735f83359058f7108; ?>
<?php unset($__attributesOriginal68cb1971a2b92c9735f83359058f7108); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal68cb1971a2b92c9735f83359058f7108)): ?>
<?php $component = $__componentOriginal68cb1971a2b92c9735f83359058f7108; ?>
<?php unset($__componentOriginal68cb1971a2b92c9735f83359058f7108); ?>
<?php endif; ?>

                            <form method="POST" action="<?php echo e(route('logout')); ?>">
                                <?php echo csrf_field(); ?>
                                <?php if (isset($component)) { $__componentOriginal68cb1971a2b92c9735f83359058f7108 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal68cb1971a2b92c9735f83359058f7108 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dropdown-link','data' => ['href' => route('logout'),'onclick' => 'event.preventDefault();
                                                    this.closest(\'form\').submit();']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dropdown-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('logout')),'onclick' => 'event.preventDefault();
                                                    this.closest(\'form\').submit();']); ?>
                                    Keluar
                                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal68cb1971a2b92c9735f83359058f7108)): ?>
<?php $attributes = $__attributesOriginal68cb1971a2b92c9735f83359058f7108; ?>
<?php unset($__attributesOriginal68cb1971a2b92c9735f83359058f7108); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal68cb1971a2b92c9735f83359058f7108)): ?>
<?php $component = $__componentOriginal68cb1971a2b92c9735f83359058f7108; ?>
<?php unset($__componentOriginal68cb1971a2b92c9735f83359058f7108); ?>
<?php endif; ?>
                            </form>
                         <?php $__env->endSlot(); ?>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe)): ?>
<?php $attributes = $__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe; ?>
<?php unset($__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf8083d4a852c446488d8d384bbc7cbe)): ?>
<?php $component = $__componentOriginaldf8083d4a852c446488d8d384bbc7cbe; ?>
<?php unset($__componentOriginaldf8083d4a852c446488d8d384bbc7cbe); ?>
<?php endif; ?>
                </div>
            <?php else: ?>
                <a href="<?php echo e(route('login')); ?>" class="text-gray-600 hover:text-gray-900 text-sm">Masuk</a>
                <a href="<?php echo e(route('register')); ?>" class="bg-gray-900 hover:bg-gray-700 text-white text-sm px-4 py-2 rounded-lg font-semibold transition">Daftar</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<?php echo $__env->make('partials.toasts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php if(session('system_error')): ?>
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            showToast('<?php echo e(addslashes(session('system_error'))); ?>', 'error');
        });
    </script>
<?php endif; ?>
<?php /**PATH D:\CODING\olivia_final\resources\views/partials/nav-auth.blade.php ENDPATH**/ ?>