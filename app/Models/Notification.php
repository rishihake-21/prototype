<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    public const TYPE_SYLLABUS_SUBMITTED = 'syllabus_submitted';
    public const TYPE_SYLLABUS_APPROVED = 'syllabus_approved';
    public const TYPE_SYLLABUS_REJECTED = 'syllabus_rejected';
    public const TYPE_CHANGES_REQUESTED = 'changes_requested';
    public const TYPE_ELECTIVE_POOL_UPDATED = 'elective_pool_updated';
    public const TYPE_ELECTIVE_SELECTED = 'elective_selected';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }
}
