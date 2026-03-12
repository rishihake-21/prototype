<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProgrammeLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'programme_id',
        'level_code',
        'level_name',
        'sort_order',
        'courses_limit',
        'th_limit',
        'tu_limit',
        'pr_limit',
        'hours_limit',
        'credits_limit',
        'marks_limit',
        'is_audit',
    ];

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function structure(): HasOne
    {
        return $this->hasOne(ProgrammeStructure::class, 'level_id');
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'level_id');
    }

    /**
     * Whether this level corresponds to Audit Courses (Level-0 or AU).
     */
    public function isAudit(): bool
    {
        return $this->is_audit || in_array($this->level_code, ['Level-0', 'AU', 'Audit']);
    }

    /**
     * Recalculate from associated courses and return the aggregate data.
     */
    public function calculateFromCourses(): array
    {
        $courses = $this->courses()->whereNull('deleted_at')->get();

        $th = (int) $courses->sum('th_hours');
        $tu = (int) $courses->sum('tu_hours');
        $pr = (int) $courses->sum('pr_hours');

        return [
            'course_count'  => $courses->count(),
            'th'            => $th,
            'tu'            => $tu,
            'pr'            => $pr,
            'hours'         => $th + $tu + $pr,
            'credits'       => (float) $courses->sum('credits'),
            'marks'         => (int) $courses->sum('total_marks'),
        ];
    }
}
