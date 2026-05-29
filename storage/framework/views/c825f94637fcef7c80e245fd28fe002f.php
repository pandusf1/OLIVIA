<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Safora — Pengaturan</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
        <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap');
        * { font-family: 'Space Grotesk', sans-serif; }
        h1 { font-family: 'Space Grotesk', sans-serif !important; }
        .font-unbounded { font-family: 'Space Grotesk', sans-serif !important; }
    </style>
</head>
<body class="bg-[#faf9f7] text-gray-900 antialiased min-h-screen">
    <?php
        $backUrl = route('dashboard');
        $backLabel = 'Dashboard';
        $pendingPhoneVerification = session('verify_user_phone') ?: (auth()->user()->phone && !auth()->user()->phone_is_verified ? auth()->user()->phone : null);
        $phoneResendAvailableAt = auth()->check() ? \Illuminate\Support\Facades\Cache::get('phone_verification_resend_available_at:' . auth()->id()) : null;
        $phoneResendSeconds = session('phone_resend_seconds', $phoneResendAvailableAt ? max(0, $phoneResendAvailableAt - time()) : 0);
    ?>
    <?php echo $__env->make('partials.nav-auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="max-w-3xl mx-auto px-6 py-10">
        <div class="mb-8">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-1">AKUN</p>
            <h1 class="font-unbounded text-3xl font-black text-gray-900">Pengaturan</h1>
        </div>

        <?php if(session('success')): ?><div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl mb-6 text-sm">✓ <?php echo e(session('success')); ?></div><?php endif; ?>
        <?php if(session('warning')): ?><div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-xl mb-6 text-sm">⚠️ <?php echo e(session('warning')); ?></div><?php endif; ?>
        <?php if(session('error')): ?><div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl mb-6 text-sm">✗ <?php echo e(session('error')); ?></div><?php endif; ?>

        
        <div class="flex gap-1 border-b border-gray-200 mb-8">
            <?php $__currentLoopData = ['profil'=>'Profil','keamanan'=>'Keamanan','notifikasi'=>'Notifikasi','akun'=>'Akun']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <button onclick="switchTab('<?php echo e($key); ?>')" id="tab-<?php echo e($key); ?>"
                class="px-4 py-2.5 text-sm font-semibold border-b-2 transition -mb-px
                <?php echo e($key==='profil' ? 'border-gray-900 text-gray-900' : 'border-transparent text-gray-400 hover:text-gray-700'); ?>">
                <?php echo e($label); ?>

            </button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        
        <div id="panel-profil" class="tab-panel">
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-4">
                    <div class="w-14 h-14 bg-red-700 rounded-2xl flex items-center justify-center text-xl font-black text-white">
                        <?php echo e(strtoupper(substr(auth()->user()->name, 0, 1))); ?>

                    </div>
                    <div>
                        <p class="font-bold text-gray-900"><?php echo e(auth()->user()->name); ?></p>
                        <p class="text-gray-400 text-sm"><?php echo e(auth()->user()->email); ?></p>
                        <p class="text-gray-400 text-xs">Bergabung <?php echo e(auth()->user()->created_at->format('d M Y')); ?></p>
                    </div>
                </div>
                <form action="<?php echo e(route('profile.update')); ?>" method="POST" class="px-6 py-6 space-y-4">
                    <?php echo csrf_field(); ?> <?php echo method_field('patch'); ?>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">Nama</label>
                        <input type="text" name="name" value="<?php echo e(old('name', auth()->user()->name)); ?>" class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition" required>
                        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">Email</label>
                        <input type="email" name="email" value="<?php echo e(old('email', auth()->user()->email)); ?>" class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition" required>
                        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">No. HP</label>
                        <p class="text-gray-400 text-xs">Format nomor tanpa + (contoh: 6281234567890)</p>
                        <input type="tel" name="phone" value="<?php echo e(old('phone', auth()->user()->phone)); ?>" placeholder="628xxxxxxxxx" class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition">
                        <div class="flex items-center justify-between gap-3 mt-1">
                            <?php if(auth()->user()->phone): ?>
                            <?php endif; ?>
                        </div>
                        <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="flex justify-end"><button type="submit" class="bg-gray-900 hover:bg-gray-700 text-white px-6 py-2.5 rounded-xl font-semibold text-sm transition">Simpan</button></div>
                </form>
            </div>
        </div>

        
        <div id="panel-keamanan" class="tab-panel hidden">
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100"><h2 class="font-bold text-gray-900">Ubah Password</h2></div>
                <form action="<?php echo e(route('password.update')); ?>" method="POST" class="px-6 py-6 space-y-4">
                    <?php echo csrf_field(); ?> <?php echo method_field('put'); ?>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">Password Saat Ini</label>
                        <input type="password" name="current_password" class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition">
                        <?php $__errorArgs = ['current_password','updatePassword'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">Password Baru</label>
                        <input type="password" name="password" id="new-pass" class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition">
                        <div class="mt-1.5 h-1 bg-gray-100 rounded-full overflow-hidden"><div id="str-bar" class="h-full rounded-full transition-all duration-300 w-0"></div></div>
                        <p id="str-label" class="text-xs mt-1 text-gray-400"></p>
                        <?php $__errorArgs = ['password','updatePassword'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">Konfirmasi</label>
                        <input type="password" name="password_confirmation" class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none transition">
                    </div>
                    <div class="flex justify-end"><button type="submit" class="bg-gray-900 hover:bg-gray-700 text-white px-6 py-2.5 rounded-xl font-semibold text-sm transition">Perbarui</button></div>
                </form>
            </div>
        </div>

        
        <div id="panel-notifikasi" class="tab-panel hidden">
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100"><h2 class="font-bold text-gray-900">Preferensi Notifikasi</h2></div>
                <div class="px-6 py-6 space-y-3">
                    <form action="<?php echo e(route('settings.update')); ?>" method="POST" id="form-nearby-alerts">
                        <?php echo csrf_field(); ?> <?php echo method_field('patch'); ?>
                        <input type="hidden" name="receive_nearby_alerts" value="0">
                        <div class="flex items-center justify-between border border-gray-100 rounded-xl px-4 py-3">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">WhatsApp Alert Korban Terdekat</p>
                                <p class="text-gray-400 text-xs">Terima alert ketika ada korban di sekitar yang butuh bantuan</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="receive_nearby_alerts" value="1" <?php echo e(auth()->user()->receive_nearby_alerts ? 'checked' : ''); ?> class="sr-only peer" onchange="document.getElementById('form-nearby-alerts').submit()">
                                <div class="w-10 h-6 bg-gray-200 peer-checked:bg-red-700 rounded-full transition-all after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-4"></div>
                            </label>
                        </div>
                    </form>

                    <?php $__currentLoopData = [['WhatsApp Alert Panic','Kirim alert ke trusted contact saat panic',true],['Update Status','Notifikasi saat status laporan berubah',true],['Pengingat Keamanan','Tips keamanan berkala',false]]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$l,$d,$c]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center justify-between border border-gray-100 rounded-xl px-4 py-3 opacity-70">
                        <div><p class="text-sm font-semibold text-gray-900"><?php echo e($l); ?></p><p class="text-gray-400 text-xs"><?php echo e($d); ?></p></div>
                        <label class="relative inline-flex items-center cursor-not-allowed">
                            <input type="checkbox" <?php echo e($c?'checked':''); ?> disabled class="sr-only peer">
                            <div class="w-10 h-6 bg-gray-200 peer-checked:bg-red-700 rounded-full transition-all after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-4"></div>
                        </label>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <p class="text-gray-400 text-xs pt-2">💡 WhatsApp alert kepanikan akan selalu dikirim ke kontak terpercaya tanpa batas jarak.</p>
                </div>
            </div>
        </div>

        
        <div id="panel-akun" class="tab-panel hidden">
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden mb-4">
                <div class="px-6 py-4 border-b border-gray-100"><h2 class="font-bold text-gray-900">Info Akun</h2></div>
                <div class="px-6 py-5 space-y-3 text-sm">
                    <?php $__currentLoopData = [['Role', auth()->user()->role??'user'],['Jumlah Laporan', $reportCount.' laporan'],['Bergabung', auth()->user()->created_at->format('d M Y')]]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$k,$v]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex justify-between"><span class="text-gray-400"><?php echo e($k); ?></span><span class="text-gray-900 font-medium"><?php echo e($v); ?></span></div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <div class="bg-white border border-red-100 rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-red-100"><h2 class="font-bold text-red-600">Hapus Akun</h2></div>
                <div class="px-6 py-6">
                    <p class="text-gray-400 text-sm mb-4">Semua data akan terhapus permanen dan tidak bisa dipulihkan.</p>
                    <button onclick="document.getElementById('del-form').classList.toggle('hidden')" class="bg-red-700 hover:bg-red-800 text-white px-5 py-2.5 rounded-xl font-semibold text-sm transition">Hapus Akun Saya</button>
                    <div id="del-form" class="hidden mt-4 border border-red-100 rounded-xl p-4">
                        <form action="<?php echo e(route('profile.destroy')); ?>" method="POST">
                            <?php echo csrf_field(); ?> <?php echo method_field('delete'); ?>
                            <input type="password" name="password" placeholder="Konfirmasi password..." class="w-full border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 text-sm focus:outline-none mb-3">
                            <?php $__errorArgs = ['password','userDeletion'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mb-2"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <div class="flex gap-2">
                                <button type="submit" class="flex-1 bg-red-700 hover:bg-red-800 text-white py-2.5 rounded-xl font-bold text-sm">Ya, Hapus</button>
                                <button type="button" onclick="document.getElementById('del-form').classList.add('hidden')" class="flex-1 border border-gray-200 text-gray-600 py-2.5 rounded-xl font-semibold text-sm">Batal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if($pendingPhoneVerification): ?>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
        <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-xl">
            <h3 class="text-lg font-bold text-gray-900 mb-2">Verifikasi WhatsApp</h3>
            <p class="text-sm text-gray-500 mb-4">Masukkan 5 digit kode yang telah dikirimkan ke nomor <strong><?php echo e($pendingPhoneVerification); ?></strong>.</p>

            <?php if(session('error')): ?>
                <div class="bg-red-50 text-red-800 text-xs px-3 py-2 rounded-lg mb-3">✗ <?php echo e(session('error')); ?></div>
            <?php endif; ?>

            <form action="<?php echo e(route('profile.phone.verify')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="mb-4">
                    <input type="text" name="code" maxlength="5" placeholder="_____" class="w-full text-center text-2xl tracking-[0.5em] font-bold border border-gray-200 focus:border-gray-400 rounded-xl px-4 py-3 focus:outline-none transition" required>
                </div>

                <button type="submit" class="w-full bg-gray-900 hover:bg-gray-700 text-white py-3 rounded-xl font-semibold text-sm transition">Verifikasi</button>
            </form>
            <form action="<?php echo e(route('profile.phone.resend')); ?>" method="POST" class="mt-3 js-resend-form">
                <?php echo csrf_field(); ?>
                <button type="submit" data-resend-button data-initial-seconds="<?php echo e((int) $phoneResendSeconds); ?>" class="w-full border border-gray-200 hover:bg-gray-50 text-gray-700 py-3 rounded-xl font-semibold text-sm transition disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-400 disabled:hover:bg-gray-100 flex items-center justify-center gap-2">
                    <span data-resend-spinner class="hidden h-4 w-4 animate-spin rounded-full border-2 border-gray-300 border-t-gray-800"></span>
                    <span data-resend-label>Kirim Ulang Kode</span>
                </button>
            </form>
            <form action="<?php echo e(route('profile.phone.remove')); ?>" method="POST" class="mt-3">
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
                <button type="submit" class="w-full text-gray-500 hover:text-gray-700 py-2 rounded-xl text-sm transition">Batal & Hapus</button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script>
    const tabs=['profil','keamanan','notifikasi','akun'];
    function switchTab(n){tabs.forEach(t=>{document.getElementById('panel-'+t).classList.add('hidden');const b=document.getElementById('tab-'+t);b.classList.remove('border-gray-900','text-gray-900');b.classList.add('border-transparent','text-gray-400');});document.getElementById('panel-'+n).classList.remove('hidden');const ab=document.getElementById('tab-'+n);ab.classList.add('border-gray-900','text-gray-900');ab.classList.remove('border-transparent','text-gray-400');}
    document.getElementById('new-pass').addEventListener('input',function(){const v=this.value;let s=0;if(v.length>=8)s++;if(v.length>=12)s++;if(/[A-Z]/.test(v))s++;if(/[0-9]/.test(v))s++;if(/[^A-Za-z0-9]/.test(v))s++;const c=['','bg-red-500','bg-orange-500','bg-yellow-500','bg-blue-500','bg-green-500'];const l=['','Sangat lemah','Lemah','Cukup','Kuat','Sangat kuat'];document.getElementById('str-bar').className='h-full rounded-full transition-all duration-300 '+(c[s]||'');document.getElementById('str-bar').style.width=v.length?s*20+'%':'0%';document.getElementById('str-label').textContent=v.length?l[s]:'';});
    <?php if($errors->updatePassword->any()): ?> switchTab('keamanan'); <?php endif; ?>
    <?php if($errors->userDeletion->any()): ?> switchTab('akun'); document.getElementById('del-form').classList.remove('hidden'); <?php endif; ?>
    <?php if(session('success') == 'Pengaturan notifikasi berhasil diperbarui.'): ?> switchTab('notifikasi'); <?php endif; ?>
    <?php if($pendingPhoneVerification || $errors->has('phone') || $errors->has('name') || $errors->has('email')): ?> switchTab('profil'); <?php endif; ?>

    document.querySelectorAll('.js-resend-form').forEach((form) => {
        const button = form.querySelector('[data-resend-button]');
        const label = form.querySelector('[data-resend-label]');
        const spinner = form.querySelector('[data-resend-spinner]');
        if (!button || !label) return;

        let remaining = Number(button.dataset.initialSeconds || 0);
        const defaultText = 'Kirim Ulang Kode';
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
            label.textContent = 'Mengirim kode...';
        });
    });
    </script>
</body>
</html>

<?php /**PATH D:\CODING\olivia_final\resources\views/settings/index.blade.php ENDPATH**/ ?>