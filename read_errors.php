<?php
$log = file_get_contents('storage/logs/laravel.log');
$errors = explode('[2026-', $log);
$count = count($errors);
echo "TOTAL ERRORS COUNT: " . $count . "\n\n";

for ($i = max(1, $count - 3); $i < $count; $i++) {
    echo "===========================================\n";
    echo "ERROR " . $i . ":\n";
    echo "[2026-" . substr($errors[$i], 0, 800) . "\n";
}
