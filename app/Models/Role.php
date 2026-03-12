<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'permissions',
        'is_active',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
    ];

    public const ADMIN = 'admin';
    public const CREATOR = 'creator';
    public const APPROVER = 'approver';

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function isAdmin(): bool
    {
        return $this->name === self::ADMIN;
    }

    public function isCreator(): bool
    {
        return $this->name === self::CREATOR;
    }

    public function isApprover(): bool
    {
        return $this->name === self::APPROVER;
    }
}
