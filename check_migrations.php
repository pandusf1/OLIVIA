<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$migrations = DB::table('migrations')->get();
foreach ($migrations as $m) {
    echo $m->batch . ' - ' . $m->migration . PHP_EOL;
}
