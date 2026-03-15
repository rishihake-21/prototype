<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseAssignment extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_CHANGES_REQUESTED = 'changes_requested';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'course_id',
        'department_id',
        'faculty_user_id',
        'assigned_by',
        'assigned_at',
        'deadline',
        'term',
        'academic_year',
        'status',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'deadline'    => 'date',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function faculty()
    {
        return $this->belongsTo(User::class, 'faculty_user_id');
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
    public function syllabi()
    {
        return $this->hasMany(Syllabus::class, 'assignment_id');
    }

    /**
     * @return array<int, string>
     */
    public static function activeStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_IN_PROGRESS,
            self::STATUS_SUBMITTED,
            self::STATUS_UNDER_REVIEW,
            self::STATUS_CHANGES_REQUESTED,
            self::STATUS_REJECTED,
        ];
    }

    public function isActive(): bool
    {
        return in_array($this->status, self::activeStatuses(), true);
    }
}
