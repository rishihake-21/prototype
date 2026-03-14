<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Scheme;
use App\Models\Programme;

echo "=== CHECKING SCHEMES AND ASSESSMENT COMPONENTS ===\n\n";

$schemes = Scheme::with('assessmentComponents')->get();
foreach ($schemes as $scheme) {
    echo "Scheme: {$scheme->name} (ID: {$scheme->id})\n";
    echo "  Assessment Components: " . $scheme->assessmentComponents->count() . "\n";
    
    foreach ($scheme->assessmentComponents as $c) {
        $parentId = $c->parent_id ?? 'NULL';
        echo "    - {$c->component_name} (parent_id: {$parentId}, order: {$c->display_order})\n";
    }
    
    // Test the getLeafColumns method
    echo "  Leaf Columns: " . count($scheme->getLeafColumns()) . "\n";
    foreach ($scheme->getLeafColumns() as $leaf) {
        echo "    - {$leaf['name']} (ID: {$leaf['id']})\n";
    }
    echo "\n";
}

echo "\n=== CHECKING PROGRAMMES ===\n\n";
$programmes = Programme::with('scheme.assessmentComponents')->get();
foreach ($programmes as $prog) {
    echo "Programme: {$prog->name} ({$prog->code})\n";
    if ($prog->scheme) {
        echo "  Scheme: {$prog->scheme->name}\n";
        echo "  Scheme Assessment Components: " . $prog->scheme->assessmentComponents->count() . "\n";
    } else {
        echo "  Scheme: NOT ASSIGNED\n";
    }
    echo "\n";
}
