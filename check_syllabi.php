<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$syllabi = \App\Models\Syllabus::all();
echo "Total syllabi: " . $syllabi->count() . "\n\n";

foreach($syllabi as $s) {
    echo "Title: " . $s->title . "\n";
    echo "Course Code: " . $s->course_code . "\n";
    echo "Level: " . $s->getCourseLevel() . "\n";
    echo "Status: " . $s->status . "\n";
    echo "---\n";
}