<?php

require_once 'vendor/autoload.php';

use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Database\Capsule\Manager as Capsule;

// Set up Laravel container
$container = new Container;
$container['config'] = [
    'database.default' => 'sqlite',
    'database.connections.sqlite' => [
        'driver' => 'sqlite',
        'database' => 'database/database.sqlite',
        'prefix' => '',
    ],
];

// Set up Eloquent
$capsule = new Capsule($container);
$capsule->addConnection($container['config']['database.connections.sqlite']);
$capsule->setEventDispatcher(new Dispatcher($container));
$capsule->setAsGlobal();
$capsule->bootEloquent();

// Load models
require_once 'app/Models/Syllabus.php';

// Verify data
$syllabi = \App\Models\Syllabus::all();
echo "Total syllabi: " . $syllabi->count() . "\n\n";

foreach($syllabi as $syllabus) {
    echo "Title: " . $syllabus->title . "\n";
    echo "Course Code: " . $syllabus->course_code . "\n";
    echo "Level (from code): " . $syllabus->getCourseLevel() . "\n";
    echo "Status: " . $syllabus->status . "\n";
    echo "Department: " . ($syllabus->department ? $syllabus->department->name : 'None') . "\n";
    echo "---\n";
}