<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'head_user_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /** SRS: Many-to-many; faculty can belong to multiple departments. */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_departments');
    }

    public function syllabi(): BelongsToMany
    {
        return $this->belongsToMany(Syllabus::class, 'course_department')->withTimestamps();
    }

    public function head(): BelongsTo
    {
        return $this->belongsTo(User::class, 'head_user_id');
    }
}
