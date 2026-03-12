<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Users ===\n";
$users = \App\Models\User::all();
foreach($users as $user) {
    echo $user->name . " (" . $user->email . ") - " . $user->role . "\n";
}

echo "\n=== Syllabi ===\n";
$syllabi = \App\Models\Syllabus::all();
foreach($syllabi as $s) {
    echo $s->title . " (" . $s->course_code . ") - Level: " . $s->getCourseLevel() . " - Status: " . $s->status . "\n";
}