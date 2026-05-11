<?php
// api/index.php

// Buat folder sementara untuk Laravel agar tidak error saat menulis view/cache
$storageFolders = [
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache',
    '/tmp/storage/framework/sessions',
];

foreach ($storageFolders as $folder) {
    if (!is_dir($folder)) {
        mkdir($folder, 0755, true);
    }
}

require __DIR__ . '/../public/index.php';