<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'programme_id',
        'level_id',
        'course_code',
        'course_title',
        'course_abbr',
        'th_hours',
        'tu_hours',
        'pr_hours',
        'total_hours',
        'credits',
        'theory_paper_hrs',
        'total_marks',
        'course_type',
        'elective_group',
        'is_common_course',
        'year',
        'term',
        'is_award',
    ];

    protected $casts = [
        'is_common_course' => 'boolean',
        'is_award' => 'boolean',
        'credits' => 'decimal:2',
    ];

    public const TYPE_COMPULSORY = 'compulsory';
    public const TYPE_ELECTIVE = 'elective';
    public const TYPE_AUDIT = 'audit';

    public static function types(): array
    {
        return [
            self::TYPE_COMPULSORY => 'Compulsory',
            self::TYPE_ELECTIVE => 'Elective',
            self::TYPE_AUDIT => 'Audit',
        ];
    }

    public static function electiveGroups(): array
    {
        return ['Elective I', 'Elective II', 'Elective III', 'Elective IV'];
    }

    // -- Relationships --

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(ProgrammeLevel::class , 'level_id');
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class , 'course_programme_departments');
    }

    public function samplePaths()
    {
        return $this->hasMany(SamplePath::class);
    }

    public function assessments()
    {
        return $this->hasMany(CourseAssessment::class);
    }

    public function assignments()
    {
        return $this->hasMany(CourseAssignment::class);
    }

    // -- Computed helpers --

    public function computedTotalHours(): int
    {
        return $this->th_hours + $this->tu_hours + $this->pr_hours;
    }

    public function computedTotalMarks(): int
    {
        return $this->assessments()->sum('max_marks');
    }

    // -- Boot --

    protected static function boot(): void
    {
        parent::boot();

        // Auto-compute stored totals on save
        static::saving(function (Course $course) {
            $course->total_hours = $course->th_hours + $course->tu_hours + $course->pr_hours;
        });
    }
}
