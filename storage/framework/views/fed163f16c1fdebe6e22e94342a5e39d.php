<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Safora — Laporan</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap');
        * { font-family: 'Space Grotesk', sans-serif; };
        h1 { font-family: 'Space Grotesk', sans-serif !important; };
        .font-unbounded { font-family: 'Space Grotesk', sans-serif !important; };        
    </style>
</head>
<body class="bg-[#faf9f7] text-gray-900 antialiased min-h-screen">

    <?php
        $backUrl = request()->headers->get('referer') ?: (auth()->check() ? route('dashboard') : '/');
        $backLabel = 'Kembali';
        $showBrand = false;
    ?>
    <?php echo $__env->make('partials.nav-auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="fixed top-16 right-4 z-50">
        <button onclick="document.getElementById('stealth').classList.remove('hidden')" class="bg-white border border-gray-200 text-gray-400 px-3 py-1.5 rounded-lg text-xs font-mono transition shadow-sm">🔢</button>
    </div>

    <div class="max-w-3xl mx-auto px-6 py-10">

        <?php if(session('success')): ?>
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl mb-6 text-sm">✓ <?php echo e(session('success')); ?></div>
        <?php endif; ?>

        
        <div class="mb-6">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-1">LAPORAN #<?php echo e(strtoupper(substr($report->id, 0, 8))); ?></p>
                <h1 class="font-unbounded font-bold text-3xl font-black text-gray-900">
                    <?php echo e($report->category); ?>

                </h1>
            <div class="flex items-center gap-4 mt-2 text-sm text-gray-400 flex-wrap">
                <span><?php echo e($report->category); ?></span>
                <?php if($report->created_at): ?>
                <span class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <?php echo e($report->created_at->format('d/m/Y, H:i:s')); ?>

                </span>
                <?php endif; ?>
<?php if($report->latitude): ?>
<div class="flex items-center gap-2 flex-wrap">

    <span class="flex items-center gap-1">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
        </svg>

        <?php echo e(number_format($report->latitude,4)); ?>,
        <?php echo e(number_format($report->longitude,4)); ?>

    </span>

    <a
        href="https://www.google.com/maps?q=<?php echo e($report->latitude); ?>,<?php echo e($report->longitude); ?>"
        target="_blank"
        class="inline-flex items-center gap-1 font-unbounded bg-red-700 hover:bg-red-800 text-white text-xs px-3 py-1.5 rounded-lg transition"
    >
        Buka Map
    </a>

</div>
<?php endif; ?>
            </div>
        </div>

        
        <div class="bg-white border border-gray-200 rounded-2xl p-6 mb-6">
            <h2 class="font-bold text-gray-900 mb-5">Status</h2>

            <?php
            $stages = [
                ['Submitted','Terkirim'],['Routed','Diteruskan'],['Viewed','Dilihat'],['In Progress','Ditangani'],['Resolved','Selesai']
            ];
            $currentIdx = array_search($report->status, array_column($stages, 0));
            ?>

            
            <div class="flex items-center gap-0 mb-6">
                <?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => [$key, $label]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex flex-col items-center flex-1">
                    <div class="flex items-center w-full">
                        <?php if($i > 0): ?>
                        <div class="flex-1 h-0.5 <?php echo e($i <= $currentIdx ? 'bg-green-500' : 'bg-gray-200'); ?> transition-all"></div>
                        <?php endif; ?>
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0 transition-all
                            <?php echo e($i < $currentIdx ? 'bg-green-500 text-white' : ($i == $currentIdx ? 'bg-green-500 text-white ring-4 ring-green-100' : 'bg-gray-100 text-gray-400')); ?>">
                            <?php if($i < $currentIdx): ?>
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <?php elseif($i == $currentIdx): ?>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3" stroke-width="3"/></svg>
                            <?php else: ?>
                            <?php echo e($i+1); ?>

                            <?php endif; ?>
                        </div>
                        <?php if($i < count($stages)-1): ?>
                        <div class="flex-1 h-0.5 <?php echo e($i < $currentIdx ? 'bg-green-500' : 'bg-gray-200'); ?> transition-all"></div>
                        <?php endif; ?>
                    </div>
                    <p class="text-xs mt-1.5 <?php echo e($i <= $currentIdx ? 'text-gray-700 font-semibold' : 'text-gray-400'); ?> text-center"><?php echo e($label); ?></p>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            
            <div class="space-y-3 border-t border-gray-100 pt-4">
                <?php $__currentLoopData = $report->statusLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex items-start gap-3">
                    <div class="w-2 h-2 bg-red-500 rounded-full mt-1.5 flex-shrink-0"></div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900"><?php echo e($log->new_status); ?></p>
                        <p class="text-xs text-gray-400"><?php echo e(\Carbon\Carbon::parse($log->changed_at)->format('d/m/Y, H:i:s')); ?></p>
                        <?php if($log->new_status === 'Routed'): ?>
                            <?php if($report->partner): ?>
                                <div class="mt-2 bg-white border border-gray-100 rounded-lg shadow-sm inline-block min-w-full">
                                    <p class="text-xs text-gray-500 mb-1">Diteruskan ke:</p>
                                    <p class="text-sm font-semibold text-gray-800"><?php echo e($report->partner->partner_name); ?></p>
                                    <p class="text-[11px] text-gray-500 mt-1 flex items-center gap-1.5"><span class="w-3.5 flex justify-center">📞</span> <?php echo e($report->partner->phone ?? '-'); ?></p>
                                    <p class="text-[11px] text-gray-500 mt-0.5 flex items-start gap-1.5"><span class="w-3.5 flex justify-center mt-0.5">📍</span> <span class="flex-1"><?php echo e($report->partner->address ?? '-'); ?></span></p>
                                </div>
                            <?php else: ?>
                                <p class="text-xs text-gray-500">Diteruskan ke partner</p>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if($log->new_status === 'Submitted'): ?><p class="text-xs text-gray-500">Laporan dikirim</p><?php endif; ?>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        
        <div class="bg-white border border-gray-200 rounded-2xl p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-gray-900">Bukti (<?php echo e($report->evidences->count()); ?>)</h2>
                <button onclick="document.getElementById('upload-area').classList.toggle('hidden')" class="text-red-700 hover:text-red-800 text-sm font-semibold transition flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Tambah Bukti
                </button>
            </div>

            <div id="upload-area" class="hidden mb-4 p-4 border border-dashed border-gray-200 rounded-xl">
                <form action="/tracking/<?php echo e($report->id); ?>/evidence" method="POST" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <div class="text-center cursor-pointer mb-3" onclick="document.getElementById('ev-file').click()">
                        <p class="text-2xl mb-1">📁</p>
                        <p class="text-gray-500 text-sm">Klik untuk pilih file</p>
                        <p class="text-gray-400 text-xs mt-1">Bisa pilih lebih dari 1 (maks. 20MB/file)</p>
                        <div id="ev-name" class="text-green-600 text-xs mt-2 hidden text-left flex flex-col items-center"></div>
                    </div>
                    <input type="file" name="evidence[]" id="ev-file" class="hidden" multiple>
                    <button type="submit" class="w-full bg-gray-900 hover:bg-gray-700 text-white py-2.5 rounded-xl text-sm font-semibold transition mt-2">Upload</button>
                </form>
            </div>
            <script>
                const dt = new DataTransfer();
                document.getElementById('ev-file').addEventListener('change', function(e) {
                    for (let file of this.files) {
                        dt.items.add(file);
                    }
                    this.files = dt.files;
                    const el = document.getElementById('ev-name');
                    el.innerHTML = '';
                    if (this.files.length > 0) {
                        el.classList.remove('hidden');
                        for(let i=0; i<this.files.length; i++) {
                            el.innerHTML += '<div>✓ ' + this.files[i].name + '</div>';
                        }
                    } else {
                        el.classList.add('hidden');
                    }
                });
            </script>

            <?php if($report->evidences->count() > 0): ?>
            <div class="space-y-2">
                <?php $__currentLoopData = $report->evidences; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex items-center justify-between bg-gray-50 rounded-xl px-4 py-3">
                    <div>
                        <p class="text-sm text-gray-700"><?php echo e($ev->file_type); ?></p>
                        <p class="text-xs text-gray-400 font-mono"><?php echo e(substr($ev->file_hash,0,20)); ?>...</p>
                        <p class="text-xs text-gray-400"><?php echo e(\Carbon\Carbon::parse($ev->uploaded_at)->format('d M Y, H:i')); ?></p>
                    </div>
                    <a href="<?php echo e(asset('storage/'.$ev->file_url)); ?>" target="_blank" class="text-red-700 hover:text-red-800 text-sm font-semibold transition">Lihat →</a>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <?php else: ?>
            <p class="text-gray-400 text-sm text-center py-2">Belum ada bukti. Tambah dari tombol di atas.</p>
            <?php endif; ?>
        </div>

        
        <div class="bg-white border border-gray-200 rounded-2xl p-6 mb-6">
            <h2 class="font-semibold text-gray-900 mb-2">Bagikan ke Saksi</h2>
            <p class="text-gray-400 text-sm mb-3">Berikan ID ini ke saksi agar mereka bisa upload bukti tambahan.</p>
            <div class="flex gap-2">
                <code class="flex-1 bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-xs text-gray-600 font-mono break-all"><?php echo e($report->id); ?></code>
                <button id="copy-btn" onclick="copyId('<?php echo e($report->id); ?>')" class="bg-gray-900 hover:bg-gray-700 text-white px-4 py-3 rounded-xl text-xs font-semibold transition whitespace-nowrap">Salin</button>
            </div>
        </div>

        
        <?php if($report->witnessReports && $report->witnessReports->sum(fn($w)=>$w->evidences->count()) > 0): ?>
        <div class="bg-white border border-gray-200 rounded-2xl p-6">
            <h2 class="font-bold text-gray-900 mb-4">👥 Bukti dari Saksi</h2>
            <?php $__currentLoopData = $report->witnessReports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $__currentLoopData = $w->evidences; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $we): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex items-center justify-between bg-gray-50 rounded-xl px-4 py-3 mb-2">
                    <div><p class="text-sm text-gray-700">Saksi: <?php echo e($w->witness_name ?: 'Anonim'); ?></p><p class="text-xs text-gray-400"><?php echo e($we->file_type); ?></p></div>
                    <a href="<?php echo e(asset('storage/'.$we->file_url)); ?>" target="_blank" class="text-green-700 hover:text-green-800 text-sm font-semibold transition">Lihat →</a>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php endif; ?>

    </div>

    
    <div id="stealth" class="hidden fixed inset-0 bg-gray-100 z-[100] flex items-center justify-center">
        <div class="bg-white rounded-3xl shadow-xl w-80 overflow-hidden border border-gray-200">
            <div class="bg-gray-900 px-6 py-4 text-right"><div id="scd" class="text-white text-3xl font-light">0</div></div>
            <div class="grid grid-cols-4">
                <?php $__currentLoopData = ['AC','±','%','÷','7','8','9','×','4','5','6','−','1','2','3','+','0','0','.','=']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <button onclick="scp('<?php echo e($k); ?>')" class="py-5 text-xl font-medium border border-gray-100 <?php echo e(in_array($k,['÷','×','−','+','='])?'bg-orange-400 text-white':(in_array($k,['AC','±','%'])?'bg-gray-100 text-black':'bg-white text-black')); ?>"><?php echo e($k); ?></button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <button onclick="document.getElementById('stealth').classList.add('hidden')" class="fixed bottom-8 text-xs text-gray-400 underline">Kembali</button>
    </div>
    <script>
    function copyId(id){navigator.clipboard.writeText(id).then(()=>{const b=document.getElementById('copy-btn');b.textContent='✓ Tersalin!';setTimeout(()=>b.textContent='Salin',2000);});}
    let sv='0',so=null,sp2=null,sn=true;
    function scp(k){const d=document.getElementById('scd');if(k==='AC'){sv='0';so=null;sp2=null;sn=true;}else if(k==='±'){sv=(parseFloat(sv)*-1).toString();}else if(k==='%'){sv=(parseFloat(sv)/100).toString();}else if(['÷','×','−','+'].includes(k)){sp2=parseFloat(sv);so=k;sn=true;}else if(k==='='){if(so&&sp2!==null){const c=parseFloat(sv);sv=so==='÷'?(sp2/c).toString():so==='×'?(sp2*c).toString():so==='−'?(sp2-c).toString():(sp2+c).toString();so=null;sp2=null;sn=true;}}else if(k==='.'){if(sn){sv='0.';sn=false;}else if(!sv.includes('.'))sv+='.';}else{if(sn||sv==='0'){sv=k;sn=false;}else sv+=k;}d.textContent=sv;}
    // Auto-refresh penuh dinonaktifkan agar navigasi/klik lebih cepat (tanpa reload server-side).
    // Jika butuh update realtime, sebaiknya gunakan polling AJAX endpoint yang spesifik (bukan reload full page).
    // setInterval(() => { window.location.reload(); }, 15000);
    </script>
</body>
</html>

<?php /**PATH D:\CODING\olivia_final\resources\views/pages/tracking.blade.php ENDPATH**/ ?>