<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Programme;
use App\Models\Scheme;

echo "=== AVAILABLE SCHEMES ===\n\n";
$schemes = Scheme::all();
foreach ($schemes as $scheme) {
    echo "ID: {$scheme->id} - Name: {$scheme->name}\n";
}

echo "\n=== PROGRAMMES WITHOUT SCHEME ===\n\n";
$programmesWithoutScheme = Programme::whereNull('scheme_id')->get();
foreach ($programmesWithoutScheme as $prog) {
    echo "ID: {$prog->id} - Code: {$prog->code} - Name: {$prog->name}\n";
}

echo "\n=== TO FIX: Run one of these commands ===\n\n";
foreach ($programmesWithoutScheme as $prog) {
    echo "php artisan tinker\n";
    echo "> App\\Models\\Programme::find({$prog->id})->update(['scheme_id' => 1]);\n";
    echo "> exit\n\n";
}
