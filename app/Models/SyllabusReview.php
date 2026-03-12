<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyllabusReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'syllabus_id',
        'reviewer_id',
        'status',
        'comments',
        'version_reviewed',
        'review_date',
    ];

    protected $casts = [
        'review_date' => 'datetime',
    ];

    public function syllabus(): BelongsTo
    {
        return $this->belongsTo(Syllabus::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
