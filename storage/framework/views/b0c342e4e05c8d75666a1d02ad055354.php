<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Safora</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <style>*{font-family:'Inter',sans-serif;} h1,.font-unbounded{font-family:'Space Grotesk',sans-serif!important;}</style>
</head>
<body class="bg-[#faf9f7] antialiased min-h-screen">
    <div class="min-h-screen flex flex-col">
        
        <nav class="bg-white border-b border-gray-200">
            <div class="max-w-6xl mx-auto px-6 h-14 flex items-center">
                <a href="/" class="flex items-center gap-2">
                    <div class="w-7 h-7 bg-red-700 rounded-lg flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944z" clip-rule="evenodd"/></svg>
                    </div>
                    <span class="font-bold text-gray-900 text-sm">Safora</span>
                    <span class="text-gray-400 text-xs">SUARA & PERLINDUNGAN</span>
                </a>
            </div>
        </nav>

        
        <div class="flex-1 flex flex-col items-center justify-center px-6 py-12">
            <div class="w-full max-w-sm bg-white border border-gray-200 rounded-2xl p-8 shadow-sm">
                <?php echo e($slot); ?>

            </div>
            <p class="text-gray-400 text-xs mt-6">
                <a href="/emergency" class="text-red-700 hover:text-red-800 transition">Laporan darurat tanpa login →</a>
            </p>
        </div>
    </div>
</body>
</html>
<?php /**PATH D:\CODING\olivia_final\resources\views/layouts/guest.blade.php ENDPATH**/ ?>