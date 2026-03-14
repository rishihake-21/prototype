<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$p = App\Models\Programme::find(1);
if (!$p) {
    echo "Programme(1) not found\n";
    exit(1);
}

echo "programme_id={$p->id} name={$p->name} scheme_id=" . ($p->scheme_id ?? 'null') . "\n";

$scheme = $p->scheme;
if (!$scheme) {
    echo "scheme: null\n";
    exit(0);
}

echo "scheme_name={$scheme->name}\n";
echo "assessment_components_count=" . $scheme->assessmentComponents()->count() . "\n";
echo "course_leaf_count=" . count($scheme->getCourseAssessmentLeafColumns()) . "\n";

