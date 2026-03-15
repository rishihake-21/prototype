<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Syllabus extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'syllabi';

    protected $fillable = [
        'title',
        'course_code',
        'course_description',
        'learning_outcomes',
        'prerequisites',
        'credits',
        'duration_weeks',
        'instructor_name',
        'instructor_email',
        'department_id',
        'level',
        'semester',
        'year',
        'objectives',
        'topics',
        'assessments',
        'resources',
        'grading_policy',
        'policies',
        'status',
        'rejection_reason',
        // New fields
        'program_name',
        'academic_year',
        'iks_hours',
        'is_online_exam',
        'elective_group',
        'is_part_of_group',
        'training_location',
        'teaching_scheme',
        'examination_scheme',
        'rationale',
        'industry_employer_outcome',
        'course_objectives',
        'course_outcomes',
        'units',
        'specification_table',
        'practical_tasks',
        'training_schedule',
        'project_phase',
        'group_size_min',
        'group_size_max',
        'logbook_required',
        'industry_supervisor',
        'books',
        'software_websites',
        'equipment_list',
        'self_learning',
        'special_instructional_strategies',
        'mapping_matrix',
        // New fields from enhancement
        'slh_hours',
        'nlh_hours',
        'tw_marks',
        'is_internal_practical',
        'report_format',
        'question_paper_profile',
        'certification_signatures',
        'scheme_type',
        'course_id',
        'assignment_id',
    ];

    protected $casts = [
        'learning_outcomes' => 'array',
        'topics' => 'array',
        'assessments' => 'array',
        'resources' => 'array',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        // New JSON fields
        'teaching_scheme' => 'array',
        'examination_scheme' => 'array',
        'course_objectives' => 'array',
        'course_outcomes' => 'array',
        'units' => 'array',
        'specification_table' => 'array',
        'practical_tasks' => 'array',
        'training_schedule' => 'array',
        'books' => 'array',
        'software_websites' => 'array',
        'equipment_list' => 'array',
        'special_instructional_strategies' => 'array',
        'mapping_matrix' => 'array',
        'is_online_exam' => 'boolean',
        'is_part_of_group' => 'boolean',
        'logbook_required' => 'boolean',
        'industry_supervisor' => 'boolean',
        // New JSON casts
        'report_format' => 'array',
        'question_paper_profile' => 'array',
        'certification_signatures' => 'array',
        'is_internal_practical' => 'boolean',
    ];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CHANGES_REQUESTED = 'changes_requested';
    public const STATUS_ARCHIVED = 'archived';

    public const LEVEL_UNDERGRADUATE = 'undergraduate';
    public const LEVEL_GRADUATE = 'graduate';
    public const LEVEL_DOCTORAL = 'doctoral';
    public const LEVEL_CERTIFICATE = 'certificate';

    public const SEMESTER_FALL = 'Fall';
    public const SEMESTER_SPRING = 'Spring';
    public const SEMESTER_SUMMER = 'Summer';
    public const SEMESTER_WINTER = 'Winter';

    // Programme Names
    public const PROGRAM_CE = 'CE';
    public const PROGRAM_ME = 'ME';
    public const PROGRAM_EE = 'EE';
    public const PROGRAM_IF = 'IF';
    public const PROGRAM_CM = 'CM';

    // Level Types based on 3rd digit of course code
    public const LEVEL_FOUNDATION = 1;      // Level 1: Science & Humanities
    public const LEVEL_BASIC_TECH = 2;      // Level 2: Basic Technology
    public const LEVEL_ALLIED = 3;          // Level 3: Allied Courses
    public const LEVEL_APPLIED = 4;         // Level 4: Applied Technology
    public const LEVEL_DIVERSIFIED = 5;     // Level 5: Diversified Technology

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function departments(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'course_department')->withTimestamps();
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(CourseAssignment::class, 'assignment_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(SyllabusReview::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(SyllabusVersion::class);
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isSubmitted(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    public function isUnderReview(): bool
    {
        return $this->status === self::STATUS_UNDER_REVIEW;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isChangesRequested(): bool
    {
        return $this->status === self::STATUS_CHANGES_REQUESTED;
    }

    public function isArchived(): bool
    {
        return $this->status === self::STATUS_ARCHIVED;
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REJECTED, self::STATUS_CHANGES_REQUESTED]);
    }

    public function canBeSubmitted(): bool
    {
        return $this->isDraft() || $this->isRejected() || $this->isChangesRequested();
    }

    public function canBeApprovedOrRejected(): bool
    {
        return in_array($this->status, [
            self::STATUS_SUBMITTED,
            self::STATUS_UNDER_REVIEW,
            self::STATUS_CHANGES_REQUESTED,
        ]);
    }

    public function canRequestChanges(): bool
    {
        return $this->canBeApprovedOrRejected();
    }

    public function scopeForCreator($query, User $user)
    {
        return $query->where('submitted_by', $user->id);
    }

    public function scopeForApprover($query, User $user)
    {
        if ($user->isAdmin()) {
            $departmentIds = Department::pluck('id')->toArray();
        } else {
            // Primarily look at the department they HEAD
            $headedDeptId = $user->headedDepartment?->id;
            
            if ($headedDeptId) {
                $departmentIds = [$headedDeptId];
            } else {
                // Fallback: any department they belong to as HOD
                $departmentIds = $user->departments()->pluck('departments.id')->toArray();
            }
        }

        if (empty($departmentIds)) {
            return $query->whereRaw('1 = 0');
        }

        return $query
            ->whereIn('status', [
                self::STATUS_SUBMITTED,
                self::STATUS_UNDER_REVIEW,
                self::STATUS_CHANGES_REQUESTED,
                self::STATUS_APPROVED,
                self::STATUS_REJECTED,
            ])
            ->whereHas('departments', function ($q) use ($departmentIds) {
                $q->whereIn('departments.id', $departmentIds);
            });
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->whereHas('departments', function ($q) use ($departmentId) {
            $q->where('departments.id', $departmentId);
        });
    }

    /**
     * Extract level from course code (3rd digit)
     * Course code format: YYLXXX (Year, Level, Sequence)
     */
    public function getCourseLevel(): ?int
    {
        // Prioritize explicit level if set
        if (!is_null($this->level) && $this->level !== '') {
            return (int) $this->level;
        }

        if (empty($this->course_code) || strlen($this->course_code) < 3) {
            return null;
        }
        $levelDigit = substr($this->course_code, 2, 1);
        return is_numeric($levelDigit) ? (int) $levelDigit : null;
    }

    /**
     * Get level type description
     */
    public function getLevelType(): ?string
    {
        $level = $this->getCourseLevel();
        return match ($level) {
            self::LEVEL_FOUNDATION => 'foundation',
            self::LEVEL_BASIC_TECH => 'basic_technology',
            self::LEVEL_ALLIED => 'allied',
            self::LEVEL_APPLIED => 'applied',
            self::LEVEL_DIVERSIFIED => 'diversified',
            default => null,
        };
    }

    /**
     * Check if syllabus is Level 1 (Foundation)
     */
    public function isLevelFoundation(): bool
    {
        return $this->getCourseLevel() === self::LEVEL_FOUNDATION;
    }

    /**
     * Check if syllabus is Level 2 or 5 (Technology courses with units)
     */
    public function isLevelTechnology(): bool
    {
        return in_array($this->getCourseLevel(), [self::LEVEL_BASIC_TECH, self::LEVEL_DIVERSIFIED]);
    }

    /**
     * Check if syllabus is Level 3 (Allied/Elective)
     */
    public function isLevelAllied(): bool
    {
        return $this->getCourseLevel() === self::LEVEL_ALLIED;
    }

    /**
     * Check if syllabus is Level 4 (Applied/Training)
     */
    public function isLevelApplied(): bool
    {
        return $this->getCourseLevel() === self::LEVEL_APPLIED;
    }

    /**
     * Get available programme names
     */
    public static function getProgrammes(): array
    {
        return \App\Models\Programme::where('status', \App\Models\Programme::STATUS_ACTIVE)
            ->pluck('name', 'code')
            ->toArray();
    }

    /**
     * Get elective groups
     */
    public static function getElectiveGroups(): array
    {
        return [
            'Elective I' => 'Elective I',
            'Elective II' => 'Elective II',
            'Elective III' => 'Elective III',
            'Elective IV' => 'Elective IV',
        ];
    }

    /**
     * Get project phases
     */
    public static function getProjectPhases(): array
    {
        return [
            'selection' => 'Selection',
            'planning' => 'Planning',
            'execution' => 'Execution',
            'reporting' => 'Reporting',
        ];
    }

    /**
     * Calculate academic year from current date
     * Format: YYYY-YY (e.g., 2025-26)
     */
    public static function getAcademicYear(): string
    {
        $currentMonth = (int) date('n');
        $currentYear = (int) date('Y');
        
        // Academic year starts in July
        if ($currentMonth >= 7) {
            $startYear = $currentYear;
        } else {
            $startYear = $currentYear - 1;
        }
        
        $endYear = ($startYear + 1) % 100;
        return $startYear . '-' . str_pad($endYear, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Get default report format chapters for Level 4
     */
    public static function getDefaultReportChapters(): array
    {
        return [
            ['chapter' => 1, 'title' => 'Introduction of Industry'],
            ['chapter' => 2, 'title' => 'Organizational Structure'],
            ['chapter' => 3, 'title' => 'Equipment/Software Specifications'],
            ['chapter' => 4, 'title' => 'Safety Procedures'],
        ];
    }

    /**
     * Calculate total contact hours
     */
    public function getTotalContactHours(): int
    {
        $scheme = $this->teaching_scheme ?? [];
        return ($scheme['th_hours'] ?? 0) + ($scheme['tu_hours'] ?? 0) + ($scheme['pr_hours'] ?? 0);
    }

    /**
     * Calculate total marks from examination scheme
     */
    public function getTotalMarks(): int
    {
        $scheme = $this->examination_scheme ?? [];
        $total = 0;
        $total += $scheme['fa_th_max'] ?? 0;
        $total += $scheme['sa_th_max'] ?? 0;
        $total += $scheme['sa_pr_max'] ?? 0;
        $total += $this->tw_marks ?? 0;
        return $total;
    }
}
