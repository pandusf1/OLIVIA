<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$filePath = 'public/192.jpg';
if (!file_exists($filePath)) {
    // try to find any image in public
    $files = glob('public/*.jpg');
    if (!empty($files)) {
        $filePath = $files[0];
    } else {
        die("No test image found in public/\n");
    }
}

echo "Testing image: $filePath (Size: " . filesize($filePath) . " bytes)\n";

$start = microtime(true);
$compressed = \App\Models\Evidence::compressImageIfNeeded($filePath, 'image/jpeg');
$duration = microtime(true) - $start;

echo "Duration: " . round($duration, 3) . " seconds\n";
echo "Compressed size: " . strlen($compressed) . " bytes\n";
if (strlen($compressed) < filesize($filePath)) {
    echo "Compression succeeded!\n";
} else {
    echo "Compression skipped or failed.\n";
}
