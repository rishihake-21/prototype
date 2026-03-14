<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$t0 = microtime(true);

$programme = App\Models\Programme::with(['levels', 'scheme.assessmentComponents'])->find(1);
if (!$programme) {
    fwrite(STDERR, "Programme 1 not found.\n");
    exit(1);
}

$t1 = microtime(true);
echo "Loaded Programme(1) in " . (int) round(($t1 - $t0) * 1000) . "ms\n";

$scheme = $programme->scheme;
if (!$scheme) {
    fwrite(STDERR, "Programme 1 has no linked scheme.\n");
    exit(1);
}

$rows = $scheme->getCourseAssessmentHeaderRows();
$leaves = $scheme->getCourseAssessmentLeafColumns();
$t2 = microtime(true);

echo "Computed scheme rows/leaves in " . (int) round(($t2 - $t1) * 1000) . "ms\n";
echo "HeaderRows=" . count($rows) . " Leaves=" . count($leaves) . "\n";
echo "Leaf names: " . implode(', ', array_map(static fn (array $x) => (string) ($x['name'] ?? ''), $leaves)) . "\n";

