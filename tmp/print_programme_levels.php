<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$levels = App\Models\ProgrammeLevel::where('programme_id', 1)->orderBy('sort_order')->get();
foreach ($levels as $lv) {
    echo $lv->id . " " . $lv->level_code . " sort=" . $lv->sort_order
        . " courses_limit=" . ($lv->courses_limit ?? 'null')
        . " credits_limit=" . ($lv->credits_limit ?? 'null')
        . " marks_limit=" . ($lv->marks_limit ?? 'null')
        . "\n";
}

