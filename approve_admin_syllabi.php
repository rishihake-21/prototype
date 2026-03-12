<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Syllabus;
use App\Services\WorkflowService;
use Carbon\Carbon;

// Find the System Administrator user created by AdminUserSeeder
$admin = User::where('email', 'admin@institution.edu')->first();

if (!$admin) {
    echo "System Administrator user (admin@institution.edu) not found.\n";
    exit(1);
}

// Get all syllabi created by the System Administrator that are not yet approved
$syllabi = Syllabus::where('submitted_by', $admin->id)
    ->where('status', '!=', Syllabus::STATUS_APPROVED)
    ->get();

if ($syllabi->isEmpty()) {
    echo "No syllabi found for System Administrator that need approval.\n";
    exit(0);
}

/** @var WorkflowService $workflow */
$workflow = $app->make(WorkflowService::class);

echo "Found {$syllabi->count()} syllabi created by System Administrator to set as approved.\n\n";

foreach ($syllabi as $syllabus) {
    $oldStatus = $syllabus->status;

    try {
        // Prefer using normal workflow where allowed
        if ($syllabus->canBeApprovedOrRejected()) {
            $workflow->approve(
                $syllabus,
                $admin,
                'Automatically approved for syllabi created by System Administrator.'
            );
        } else {
            // Force-approve even drafts/other states
            $syllabus->status = Syllabus::STATUS_APPROVED;
            $syllabus->approved_by = $admin->id;
            $syllabus->approved_at = Carbon::now();
            $syllabus->save();
        }

        echo "Syllabus ID {$syllabus->id} - {$syllabus->title}: {$oldStatus} -> approved\n";
    } catch (\Throwable $e) {
        echo "Failed to set syllabus ID {$syllabus->id} as approved: {$e->getMessage()}\n";
    }
}

echo "\nDone.\n";

