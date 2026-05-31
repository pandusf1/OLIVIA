<?php
$log = file_get_contents('storage/logs/laravel.log');
$errors = explode('[2026-', $log);
$unique = [];

foreach ($errors as $error) {
    if (trim($error) === '') continue;
    $firstLine = explode("\n", $error)[0];
    // Keep only the error message, strip the timestamps
    $msg = preg_replace('/^\d\d-\d\d \d\d:\d\d:\d\d\] /', '', $firstLine);
    // Group similar transaction aborts
    if (str_contains($msg, 'In failed sql transaction')) {
        $msg = 'SQLSTATE[25P02]: In failed sql transaction: 7 ERROR: current transaction is aborted';
    }
    if (!isset($unique[$msg])) {
        $unique[$msg] = 0;
    }
    $unique[$msg]++;
}

echo "UNIQUE ERRORS LOGGED:\n";
foreach ($unique as $msg => $count) {
    echo "- [Count: $count] $msg\n";
}
