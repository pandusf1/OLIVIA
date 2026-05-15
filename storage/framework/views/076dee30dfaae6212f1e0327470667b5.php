<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>SuraRa — Darurat</title>
    <?php echo app('Illuminate\Foundation\Vite')('resources/css/app.css'); ?>
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap');*{font-family:'Inter',sans-serif;} h1,.font-unbounded{font-family:\'Unbounded\',sans-serif!important;}</style>
</head>
<body class="bg-[#faf9f7] text-gray-900 antialiased min-h-screen">

    <?php
        $backUrl = request()->headers->get('referer') ?: (auth()->check() ? route('dashboard') : '/');
        $backLabel = 'Kembali';
        $showBrand = false;
    ?>

    <?php echo $__env->make('partials.nav-auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <div class="fixed top-16 right-4 z-50">
        <button onclick="document.getElementById('stealth').classList.remove('hidden')" class="bg-white border border-gray-200 hover:border-gray-300 text-gray-500 px-3 py-1.5 rounded-lg text-xs font-mono transition shadow-sm">🔢</button>
    </div>

    <div class="max-w-2xl mx-auto px-6 py-12">

        
        <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 flex items-start gap-3 mb-8 text-sm">
            <span class="text-amber-500 mt-0.5">ⓘ</span>
            <p class="text-amber-800">Untuk darurat jiwa: hubungi 112 (darurat nasional) atau 118 (ambulans). SuraRa bekerja paralel untuk menyimpan bukti & menghubungkan ke LBH/psikolog.</p>
        </div>

        <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-3">QUICK EMERGENCY</p>
        <h1 class="font-unbounded text-4xl font-black text-gray-900 mb-2">Tarik nafas. Kami di sini.</h1>
        <p class="text-gray-500 mb-10">Tekan tombol di bawah. Kategori & lokasi akan diatur otomatis.</p>

        
        <div id="phase-panic">
            <button onclick="startCountdown()"
                class="w-full bg-red-700 hover:bg-red-800 text-white py-6 rounded-2xl font-black text-xl tracking-wide transition flex items-center justify-center gap-3 mb-8">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                TEKAN UNTUK BANTUAN
            </button>

            
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-white border border-gray-200 rounded-2xl p-5 cursor-pointer hover:border-gray-300 transition" onclick="document.getElementById('detailed-form').classList.toggle('hidden')">
                    <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center mb-3">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <p class="font-bold text-gray-900 text-sm">Laporan Detail</p>
                    <p class="text-gray-400 text-xs mt-1">Kejadian lama atau arsip lengkap</p>
                </div>
                <a href="/witness" class="bg-white border border-gray-200 rounded-2xl p-5 hover:border-gray-300 transition block">
                    <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center mb-3">
                        <svg class="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944z" clip-rule="evenodd"/></svg>
                    </div>
                    <p class="font-bold text-gray-900 text-sm">Jadi Saksi</p>
                    <p class="text-gray-400 text-xs mt-1">Punya bukti tambahan? Upload dengan kode</p>
                </a>
            </div>
        </div>

        
        <div id="phase-countdown" class="hidden">
            <div class="bg-white border border-gray-200 rounded-2xl p-8 text-center">
                <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-4">MENGIRIM DALAM</p>
                <p id="countdown-num" class="text-8xl font-black text-red-700 mb-4">5</p>
                <div class="w-full bg-gray-100 rounded-full h-1.5 mb-6 overflow-hidden">
                    <div id="cd-bar" class="bg-red-700 h-1.5 rounded-full transition-all duration-1000" style="width:100%"></div>
                </div>
                <button onclick="cancelCountdown()" class="w-full border border-gray-300 hover:border-gray-400 text-gray-700 py-3 rounded-xl font-bold transition">BATAL</button>
                <p id="cd-cat" class="text-gray-400 text-xs mt-3"></p>
            </div>
        </div>

        
        <div id="phase-form" class="hidden">
            <div class="bg-white border border-gray-200 rounded-2xl p-6">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-2 h-2 bg-red-700 rounded-full animate-pulse"></div>
                    <p class="text-red-700 text-xs font-semibold uppercase tracking-widest">Laporan Aktif</p>
                </div>
                <h2 class="font-black text-xl text-gray-900 mb-1">Apa yang terjadi?</h2>
                <p class="text-gray-400 text-sm mb-5">Pilih kategori. Lokasi sudah terekam otomatis.</p>
                <form id="emergencyForm" action="/emergency" method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="latitude" id="f-latitude">
                    <input type="hidden" name="longitude" id="f-longitude">
                    <div class="grid grid-cols-2 gap-3 mb-5">
                        <?php $__currentLoopData = [['Salah Tangkap','⚖️','Penyalahgunaan wewenang'],['Pelecehan','🛡️','Pelecehan seksual / verbal'],['Kekerasan','👊','KDRT atau kekerasan fisik'],['Kecelakaan','🚑','Butuh ambulans']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$val,$icon,$desc]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="cursor-pointer">
                            <input type="radio" name="category" value="<?php echo e($val); ?>" class="peer hidden" required>
                            <div class="peer-checked:bg-red-50 peer-checked:border-red-400 border border-gray-200 rounded-xl p-4 text-center hover:border-gray-300 transition">
                                <p class="text-2xl mb-1.5"><?php echo e($icon); ?></p>
                                <p class="text-sm font-bold text-gray-900"><?php echo e($val); ?></p>
                                <p class="text-xs text-gray-400 mt-0.5"><?php echo e($desc); ?></p>
                            </div>
                        </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <div class="flex items-center justify-between border border-gray-200 rounded-xl px-4 py-3 mb-4">
                        <div><p class="text-sm font-semibold text-gray-900">Mode Anonim</p><p class="text-xs text-gray-400">Identitas tidak ditampilkan publik</p></div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="anonymous" value="1" checked class="sr-only peer">
                            <div class="w-10 h-6 bg-gray-200 peer-checked:bg-red-700 rounded-full transition-all after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-4"></div>
                        </label>
                    </div>
                    <textarea name="description" rows="2" placeholder="Deskripsi singkat (opsional)..." class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-gray-400 mb-4 resize-none"></textarea>
                    <div id="loc-status" class="flex items-center gap-2 mb-4 text-xs text-gray-400">
                        <div class="w-2 h-2 bg-yellow-400 rounded-full animate-pulse"></div>Mengambil lokasi GPS...
                    </div>
                    <button type="submit" class="w-full bg-red-700 hover:bg-red-800 text-white py-4 rounded-xl font-black text-base transition">🚨 KIRIM LAPORAN DARURAT</button>
                </form>
            </div>
        </div>

        
        <div id="detailed-form" class="hidden mt-6">
            <div class="bg-white border border-gray-200 rounded-2xl p-6">
                <h3 class="font-bold text-gray-900 mb-4">📄 Laporan Detail</h3>
                <p class="text-gray-400 text-sm mb-4">Untuk kejadian lama, arsip, atau laporan lengkap dengan lebih banyak detail.</p>
                <a href="/emergency" class="inline-block bg-gray-900 hover:bg-gray-700 text-white px-5 py-2.5 rounded-xl font-semibold text-sm transition">Buat Laporan Detail →</a>
            </div>
        </div>
    </div>

    
    <div id="stealth" class="hidden fixed inset-0 bg-gray-100 z-[100] flex items-center justify-center">
        <div class="bg-white rounded-3xl shadow-2xl w-80 overflow-hidden border border-gray-200">
            <div class="bg-gray-900 px-6 py-4 text-right"><div id="cd" class="text-white text-3xl font-light">0</div></div>
            <div class="grid grid-cols-4">
                <?php $__currentLoopData = ['AC','±','%','÷','7','8','9','×','4','5','6','−','1','2','3','+','0','0','.','=']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <button onclick="cp('<?php echo e($k); ?>')" class="py-5 text-xl font-medium border border-gray-100 <?php echo e(in_array($k,['÷','×','−','+','='])?'bg-orange-400 text-white':(in_array($k,['AC','±','%'])?'bg-gray-100 text-black':'bg-white text-black')); ?>"><?php echo e($k); ?></button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <button onclick="document.getElementById('stealth').classList.add('hidden')" class="fixed bottom-8 text-xs text-gray-400 underline">Kembali</button>
    </div>

    <div id="offline-badge"
     class="hidden fixed bottom-4 left-4 bg-yellow-100 border border-yellow-300 text-yellow-800 px-4 py-2 rounded-xl text-sm z-50">
    ⏳ Laporan tersimpan offline
    </div>

<script>

let ci = null;
let lat = null;
let lng = null;

// GPS
if (navigator.geolocation) {

    navigator.geolocation.getCurrentPosition(

        p => {

            lat = p.coords.latitude;
            lng = p.coords.longitude;

            document.getElementById('f-latitude').value = lat;
            document.getElementById('f-longitude').value = lng;

            document.getElementById('loc-status').innerHTML =
                '<div class="w-2 h-2 bg-green-500 rounded-full"></div>' +
                '<span class="text-green-600">Lokasi aktif</span>';

        },

        () => {

            document.getElementById('loc-status').innerHTML =
                '<div class="w-2 h-2 bg-red-500 rounded-full"></div>' +
                '<span class="text-red-500">GPS tidak tersedia</span>';

        }

    );

}

// Countdown
function startCountdown() {

    document.getElementById('phase-panic').classList.add('hidden');
    document.getElementById('phase-countdown').classList.remove('hidden');

    let n = 5;

    document.getElementById('countdown-num').textContent = n;

    ci = setInterval(() => {

        n--;

        document.getElementById('countdown-num').textContent = n;
        document.getElementById('cd-bar').style.width = (n / 5 * 100) + '%';

        if (n <= 0) {

            clearInterval(ci);

            document.getElementById('phase-countdown').classList.add('hidden');
            document.getElementById('phase-form').classList.remove('hidden');

        }

    }, 1000);

}

function cancelCountdown() {

    clearInterval(ci);

    document.getElementById('phase-countdown').classList.add('hidden');
    document.getElementById('phase-panic').classList.remove('hidden');

}


// STEALTH CALCULATOR
let cv='0',co=null,cp2=null,cn=true;

function cp(k){

    const d=document.getElementById('cd');

    if(k==='AC'){
        cv='0';co=null;cp2=null;cn=true;
    }

    else if(k==='±'){
        cv=(parseFloat(cv)*-1).toString();
    }

    else if(k==='%'){
        cv=(parseFloat(cv)/100).toString();
    }

    else if(['÷','×','−','+'].includes(k)){
        cp2=parseFloat(cv);
        co=k;
        cn=true;
    }

    else if(k==='='){

        if(co&&cp2!==null){

            const c=parseFloat(cv);

            cv=
                co==='÷'?(cp2/c).toString():
                co==='×'?(cp2*c).toString():
                co==='−'?(cp2-c).toString():
                (cp2+c).toString();

            co=null;
            cp2=null;
            cn=true;

        }

    }

    else if(k==='.'){

        if(cn){
            cv='0.';
            cn=false;
        }

        else if(!cv.includes('.')){
            cv+='.';
        }

    }

    else{

        if(cn||cv==='0'){
            cv=k;
            cn=false;
        }

        else{
            cv+=k;
        }

    }

    d.textContent=cv;

}



// OFFLINE QUEUE
const form = document.getElementById('emergencyForm');

form.addEventListener('submit', async function(e) {

    e.preventDefault();

    const formData = {
        category: document.querySelector('input[name="category"]:checked')?.value,
        description: document.querySelector('textarea[name="description"]').value,
        latitude: document.getElementById('f-latitude').value,
        longitude: document.getElementById('f-longitude').value,
        anonymous: document.querySelector('input[name="anonymous"]').checked ? 1 : 0,
    };

    // OFFLINE
    if (!navigator.onLine) {

        localStorage.setItem(
            'surara_pending_report',
            JSON.stringify(formData)
        );

            // tampilkan badge offline
        document.getElementById('offline-badge')
            .classList.remove('hidden');

        alert('Offline.\nLaporan disimpan sementara.');

        return;

    }

    // ONLINE
    try {

        const response = await fetch('/emergency', {

            method: 'POST',

            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },

            body: JSON.stringify(formData)

        });

        if (response.ok) {

            alert('Laporan berhasil dikirim.');

            window.location.href = '/dashboard';

        } else {

            alert('Gagal mengirim laporan.');

        }

    } catch (err) {

        localStorage.setItem(
            'surara_pending_report',
            JSON.stringify(formData)
        );

        // tampilkan badge offline
        document.getElementById('offline-badge')
            .classList.remove('hidden');

        alert('Koneksi gagal.\nLaporan disimpan sementara.');

    }

});

const ok = await isInternetReachable();

if (!ok) return;

// AUTO SYNC
window.addEventListener('online', async () => {

    console.log('Internet kembali...');

    // tunggu koneksi stabil
    await new Promise(resolve => setTimeout(resolve, 5000));

    // cek apakah internet benar-benar bisa akses luar
    const ok = await isInternetReachable();

    if (!ok) {
        console.log('Internet belum stabil');
        return;
    }

    const pending = localStorage.getItem('surara_pending_report');

    if (!pending) return;

    const data = JSON.parse(pending);

    try {

        const response = await fetch('/emergency', {

            method: 'POST',

            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },

            body: JSON.stringify(data)

        });

        if (response.ok) {

            localStorage.removeItem('surara_pending_report');

            // sembunyikan badge
            document.getElementById('offline-badge')
                .classList.add('hidden');

            alert('Laporan offline berhasil dikirim otomatis.');

        } else {

            console.log('Server reject:', response.status);

        }

    } catch (err) {

        console.log('Sync gagal:', err);

    }

});


// CHECK INTERNET
async function isInternetReachable() {

    try {

        await fetch('https://www.google.com/favicon.ico', {
            mode: 'no-cors'
        });

        return true;

    } catch {

        return false;

    }

}

// CHECK PENDING REPORT SAAT PAGE LOAD
if (localStorage.getItem('surara_pending_report')) {

    document.getElementById('offline-badge')
        .classList.remove('hidden');

}

</script>
</body>
</html>
<?php /**PATH D:\CODING\olivia_final\resources\views/pages/emergency.blade.php ENDPATH**/ ?>