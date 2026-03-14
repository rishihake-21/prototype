<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Programme;
use App\Models\Course;

// Get first programme
$programme = Programme::with('scheme.assessmentComponents')->first();

if (!$programme || !$programme->scheme) {
    echo "No programme with scheme found\n";
    exit;
}

echo "Testing programme: {$programme->name}\n";
echo "Scheme: {$programme->scheme->name}\n\n";

$schemeRows = $programme->scheme->getHeaderRows();
$leafCols = $programme->scheme->getLeafColumns();

echo "Header Rows: " . count($schemeRows) . "\n";
foreach ($schemeRows as $rowIndex => $row) {
    echo "Row $rowIndex: " . count($row) . " columns\n";
    foreach ($row as $col) {
        $isLeaf = $col['is_leaf'] ? 'YES' : 'NO';
        echo "  - {$col['name']} (colspan: {$col['colspan']}, rowspan: {$col['rowspan']}, leaf: $isLeaf)\n";
    }
}

echo "\nLeaf Columns: " . count($leafCols) . "\n";
foreach ($leafCols as $leaf) {
    echo "  - ID: {$leaf['id']} | Name: {$leaf['name']}\n";
}

// Test with a course
$course = Course::where('programme_id', $programme->id)->with('assessments')->first();
if ($course) {
    echo "\n\nTesting with course: {$course->course_code}\n";
    $existingMarks = $course->assessments->pluck('max_marks', 'component_id')->toArray();
    echo "Existing marks count: " . count($existingMarks) . "\n";
    foreach ($existingMarks as $compId => $marks) {
        echo "  Component $compId: $marks marks\n";
    }
} else {
    echo "\n\nNo existing courses found (this is likely a new course form)\n";
}
