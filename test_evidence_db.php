<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$total = \App\Models\Evidence::count();
$base64Count = \App\Models\Evidence::where('file_url', 'like', 'data:%')->count();

echo "Total Evidences: $total\n";
echo "Base64 Evidences: $base64Count\n";

$evidences = \App\Models\Evidence::select('id', 'file_type', 'uploader_role', 'uploaded_at')
    ->selectRaw('LENGTH(file_url) as url_length')
    ->orderBy('uploaded_at', 'desc')
    ->limit(10)
    ->get();

foreach ($evidences as $ev) {
    echo "ID: {$ev->id}, Type: {$ev->file_type}, Role: {$ev->uploader_role}, Length: {$ev->url_length}, Created: {$ev->uploaded_at}\n";
}
