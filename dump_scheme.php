<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$scheme = \App\Models\Scheme::first();
echo "HEADER ROWS:\n";
echo json_encode($scheme->getHeaderRows(), JSON_PRETTY_PRINT);

echo "\n\nLEAF COLUMNS:\n";
echo json_encode($scheme->getLeafColumns(), JSON_PRETTY_PRINT);
