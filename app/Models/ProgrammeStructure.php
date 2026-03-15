<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgrammeStructure extends Model
{
    use HasFactory;

    protected $table = 'programme_structure';

    protected $fillable = [
        'programme_id',
        'level_id',
        'total_courses_offered',
        'courses_to_complete',
        'compulsory_count',
        'elective_offered_count',
        'elective_count',
        'th_hours',
        'tu_hours',
        'pr_hours',
        'total_hours',
        'total_credits',
        'total_marks',
        'notes',
    ];

    protected $casts = [
        'total_credits' => 'decimal:2',
    ];

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(ProgrammeLevel::class, 'level_id');
    }

    /**
     * Compute total_hours from the TH+TU+PR fields.
     */
    public function computedTotalHours(): int
    {
        return $this->th_hours + $this->tu_hours + $this->pr_hours;
    }

    /**
     * Recalculate from associated courses and return the data (does not auto-save).
     */
    public function calculateFromCourses(): array
    {
        $courses = Course::where('programme_id', $this->programme_id)
            ->where('level_id', $this->level_id)
            ->whereNull('deleted_at')
            ->get();

        $th    = $courses->sum('th_hours');
        $tu    = $courses->sum('tu_hours');
        $pr    = $courses->sum('pr_hours');
        $total = $th + $tu + $pr;

        return [
            'total_courses_offered' => $courses->count(),
            'th_hours'              => $th,
            'tu_hours'              => $tu,
            'pr_hours'              => $pr,
            'total_hours'           => $total,
            'total_credits'         => $courses->sum('credits'),
            'total_marks'           => $courses->sum('total_marks'),
        ];
    }
}
