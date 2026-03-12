<?php

namespace App\Services;

use App\Models\Syllabus;
use App\Models\SyllabusVersion;

class VersioningService
{
    public function createSnapshot(Syllabus $syllabus): SyllabusVersion
    {
        $version = SyllabusVersion::create([
            'syllabus_id' => $syllabus->id,
            'version_number' => $syllabus->version_number,
            'snapshot' => $syllabus->toArray(),
            'created_by' => auth()->id(),
            'created_at' => now(),
        ]);

        return $version;
    }

    public function getVersion(Syllabus $syllabus, int $versionNumber): ?SyllabusVersion
    {
        return SyllabusVersion::where('syllabus_id', $syllabus->id)
            ->where('version_number', $versionNumber)
            ->first();
    }

    public function getAllVersions(Syllabus $syllabus)
    {
        return SyllabusVersion::where('syllabus_id', $syllabus->id)
            ->orderBy('version_number', 'desc')
            ->get();
    }

    public function getVersionDiff(Syllabus $syllabus, int $fromVersion, int $toVersion): array
    {
        $from = $this->getVersion($syllabus, $fromVersion);
        $to = $this->getVersion($syllabus, $toVersion);

        if (!$from || !$to) {
            throw new \InvalidArgumentException('Version not found');
        }

        $fromSnapshot = $from->snapshot;
        $toSnapshot = $to->snapshot;

        $diff = [];
        $fields = [
            'title', 'course_code', 'course_description', 'learning_outcomes',
            'prerequisites', 'credits', 'duration_weeks', 'instructor_name',
            'instructor_email', 'level', 'semester', 'year', 'objectives',
            'topics', 'assessments', 'resources', 'grading_policy', 'policies'
        ];

        foreach ($fields as $field) {
            $fromValue = $fromSnapshot[$field] ?? null;
            $toValue = $toSnapshot[$field] ?? null;

            if ($fromValue !== $toValue) {
                $diff[$field] = [
                    'from' => $fromValue,
                    'to' => $toValue,
                ];
            }
        }

        return $diff;
    }
}
