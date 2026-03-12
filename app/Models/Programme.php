<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Programme extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'department_id',
        'scheme_id',
        'academic_year',
        'scheme_type',
        'status',
        'submitted_by',
        'published_at',
        'description',
    ];

    public const SCHEME_STANDARD = 'standard';

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public const STATUS_DRAFT    = 'draft';
    public const STATUS_ACTIVE   = 'active';
    public const STATUS_ARCHIVED = 'archived';

    /**
     * The standard 6 curriculum levels (Level-0 through Level-5).
     */
    public static function defaultLevels(): array
    {
        return [
            ['level_code' => 'Level-0', 'level_name' => 'Audit Courses',              'sort_order' => 0],
            ['level_code' => 'Level-1', 'level_name' => 'Foundation Courses',         'sort_order' => 1],
            ['level_code' => 'Level-2', 'level_name' => 'Basic Technology Courses',   'sort_order' => 2],
            ['level_code' => 'Level-3', 'level_name' => 'Allied Courses',             'sort_order' => 3],
            ['level_code' => 'Level-4', 'level_name' => 'Applied Technology Courses', 'sort_order' => 4],
            ['level_code' => 'Level-5', 'level_name' => 'Diversified Courses',        'sort_order' => 5],
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function levels(): HasMany
    {
        return $this->hasMany(ProgrammeLevel::class)->orderBy('sort_order');
    }

    public function structure(): HasMany
    {
        return $this->hasMany(ProgrammeStructure::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function samplePaths(): HasMany
    {
        return $this->hasMany(SamplePath::class);
    }

    public function awardClassCourses(): HasMany
    {
        return $this->hasMany(AwardClassCourse::class)->orderBy('sort_order');
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isArchived(): bool
    {
        return $this->status === self::STATUS_ARCHIVED;
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function scheme(): BelongsTo
    {
        return $this->belongsTo(Scheme::class);
    }

    public static function normalizeSchemeType(?string $schemeType): string
    {
        $value = strtolower(trim((string) $schemeType));

        return match ($value) {
            '', 'legacy_2021', 'new_2023', 'new_2023+', 'standard' => self::SCHEME_STANDARD,
            default => self::SCHEME_STANDARD,
        };
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT    => 'Draft',
            self::STATUS_ACTIVE   => 'Active',
            self::STATUS_ARCHIVED => 'Archived',
        ];
    }
}

