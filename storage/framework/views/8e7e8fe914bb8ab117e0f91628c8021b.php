<div id="toast-container" class="fixed top-5 right-5 z-[9999] flex flex-col gap-3 w-full max-w-md px-4 pointer-events-none"></div>

<style>
    .toast-item {
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        transform: translateX(120%);
        opacity: 0;
    }
    .toast-item.show {
        transform: translateX(0);
        opacity: 1;
    }
    .toast-item.hide {
        transform: translateY(-20px);
        opacity: 0;
    }
</style>

<script>
    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        if (!container) return;

        // Toast element
        const toast = document.createElement('div');
        toast.className = 'toast-item pointer-events-auto flex items-start gap-3.5 w-full bg-white/95 border rounded-2xl p-4.5 shadow-[0_10px_30px_rgb(0,0,0,0.08)] backdrop-blur-md';

        // Select styles and icon based on type
        let iconHtml = '';
        let borderClass = '';
        
        if (type === 'success') {
            borderClass = 'border-green-100';
            iconHtml = `
                <div class="flex items-center justify-center shrink-0 w-8 h-8 rounded-xl bg-green-50 border border-green-100 text-green-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </div>
            `;
        } else if (type === 'error') {
            borderClass = 'border-red-100';
            iconHtml = `
                <div class="flex items-center justify-center shrink-0 w-8 h-8 rounded-xl bg-red-50 border border-red-100 text-red-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
            `;
        } else if (type === 'warning') {
            borderClass = 'border-yellow-100';
            iconHtml = `
                <div class="flex items-center justify-center shrink-0 w-8 h-8 rounded-xl bg-yellow-50 border border-yellow-100 text-yellow-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
            `;
        } else {
            borderClass = 'border-gray-200';
            iconHtml = `
                <div class="flex items-center justify-center shrink-0 w-8 h-8 rounded-xl bg-gray-50 border border-gray-100 text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            `;
        }

        toast.classList.add(borderClass);

        toast.innerHTML = `
            ${iconHtml}
            <div class="flex-1 min-w-0 pt-0.5">
                <p class="text-sm font-semibold text-gray-900 leading-snug font-sans">${message}</p>
            </div>
            <button class="shrink-0 text-gray-400 hover:text-gray-600 transition p-1.5 rounded-xl hover:bg-gray-50" onclick="dismissToast(this.closest('.toast-item'))">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        `;

        container.appendChild(toast);

        // Trigger transition
        setTimeout(() => {
            toast.classList.add('show');
        }, 50);

        // Auto dismiss after 5 seconds
        const autoDismiss = setTimeout(() => {
            dismissToast(toast);
        }, 5000);

        toast.dataset.timeoutId = autoDismiss;
    }

    function dismissToast(toast) {
        if (!toast) return;
        if (toast.dataset.timeoutId) {
            clearTimeout(toast.dataset.timeoutId);
        }
        toast.classList.remove('show');
        toast.classList.add('hide');
        setTimeout(() => {
            toast.remove();
        }, 400);
    }
</script>

<?php if(session('success')): ?>
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            showToast('<?php echo e(addslashes(session('success'))); ?>', 'success');
        });
    </script>
<?php endif; ?>


<?php if(session('warning')): ?>
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            showToast('<?php echo e(addslashes(session('warning'))); ?>', 'warning');
        });
    </script>
<?php endif; ?>

<?php if(session('error')): ?>
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            showToast('<?php echo e(addslashes(session('error'))); ?>', 'error');
        });
    </script>
<?php endif; ?>

<?php if(session('status')): ?>
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            showToast('<?php echo e(addslashes(session('status'))); ?>', 'info');
        });
    </script>
<?php endif; ?>

<?php if($errors->any()): ?>
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                showToast('<?php echo e(addslashes($error)); ?>', 'error');
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        });
    </script>
<?php endif; ?>
<?php /**PATH D:\CODING\olivia_final\resources\views/partials/toasts.blade.php ENDPATH**/ ?>