<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$mitras = \App\Models\Mitra::all();

echo "TOTAL MITRA: " . $mitras->count() . "\n\n";

$grouped = $mitras->groupBy('mitra_type');

foreach ($grouped as $type => $list) {
    echo "=== KATEGORI: " . strtoupper($type) . " (" . $list->count() . " mitra) ===\n";
    foreach ($list as $m) {
        echo "- " . $m->mitra_name . " (" . $m->city . ") | Telp: " . $m->phone . " | Email: " . $m->email . "\n";
    }
    echo "\n";
}
