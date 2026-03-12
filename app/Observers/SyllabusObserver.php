<?php

namespace App\Observers;

use App\Models\Syllabus;
use App\Services\AuditService;

class SyllabusObserver
{
    public function __construct(private AuditService $auditService)
    {
    }

    public function created(Syllabus $syllabus): void
    {
        $this->auditService->log('syllabus.created', $syllabus, [], $syllabus->toArray());
    }

    public function updated(Syllabus $syllabus): void
    {
        $oldValues = $syllabus->getOriginal();
        $newValues = $syllabus->getChanges();
        
        $this->auditService->log('syllabus.updated', $syllabus, $oldValues, $newValues);
    }

    public function deleted(Syllabus $syllabus): void
    {
        $this->auditService->log('syllabus.deleted', $syllabus, $syllabus->toArray(), []);
    }

    public function restored(Syllabus $syllabus): void
    {
        $this->auditService->log('syllabus.restored', $syllabus, [], $syllabus->toArray());
    }
}
