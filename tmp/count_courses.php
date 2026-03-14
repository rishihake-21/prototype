<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$total = App\Models\Course::where('programme_id', 1)->count();
$active = App\Models\Course::where('programme_id', 1)->whereNull('deleted_at')->count();
$deleted = App\Models\Course::where('programme_id', 1)->onlyTrashed()->count();

echo "programme_id=1 total={$total} active={$active} deleted={$deleted}\n";

$rows = App\Models\Course::where('programme_id', 1)->withTrashed()->orderBy('id')->get(['id', 'course_code', 'credits', 'total_marks', 'deleted_at']);
foreach ($rows as $c) {
    echo "{$c->id} code={$c->course_code} credits={$c->credits} total_marks={$c->total_marks} deleted_at=" . ($c->deleted_at ?: 'null') . "\n";
}

