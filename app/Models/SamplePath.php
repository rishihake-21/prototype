<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SamplePath extends Model
{
    use HasFactory;

    public const ENTRY_LEVEL_STANDARD = 'standard';

    protected $fillable = [
        'programme_id',
        'entry_level',
        'term_number',
        'course_id',
    ];

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Human-readable label for a term number.
     * Terms 1,3,5 are "Odd" (1st, 2nd, 3rd Year) and 2,4,6 are "Even".
     */
    public static function termLabel(int $term): string
    {
        $labels = [
            1 => 'Odd - 1st Year',
            2 => 'Even - 1st Year',
            3 => 'Odd - 2nd Year',
            4 => 'Even - 2nd Year',
            5 => 'Odd - 3rd Year',
            6 => 'Even - 3rd Year',
        ];
        return $labels[$term] ?? "Term $term";
    }

    public static function canonicalEntryLevels(): array
    {
        return [self::ENTRY_LEVEL_STANDARD, '10+', '12+', 'Lateral'];
    }
}
