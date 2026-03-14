<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$programme = App\Models\Programme::with(['levels', 'scheme.assessmentComponents'])->find(1);
if (!$programme) {
    fwrite(STDERR, "Programme 1 not found.\n");
    exit(1);
}

$html = view('cdc.courses.form', [
    'programme' => $programme,
    'course' => null,
    'types' => App\Models\Course::types(),
    'electiveGroups' => App\Models\Course::electiveGroups(),
    'departments' => App\Models\Department::orderBy('name')->get(),
    'schemeRows' => $programme->scheme?->getCourseAssessmentHeaderRows() ?? [],
    'leafCols' => $programme->scheme?->getCourseAssessmentLeafColumns() ?? [],
    'marks' => [],
])->render();

echo "render_len=" . strlen($html) . "\n";
echo substr($html, 0, 300) . "\n";

