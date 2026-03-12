<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /** SRS Section 2: Role enum on users */
    public const ROLE_ADMIN = 'admin';
    public const ROLE_CDC = 'cdc';
    public const ROLE_HOD = 'hod';
    public const ROLE_FACULTY = 'faculty';
    public const ROLE_OBSERVER = 'observer';

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'profile_photo_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isCdc(): bool
    {
        return $this->role === self::ROLE_CDC;
    }

    public function isHod(): bool
    {
        return $this->role === self::ROLE_HOD;
    }

    public function isFaculty(): bool
    {
        return $this->role === self::ROLE_FACULTY;
    }

    public function isObserver(): bool
    {
        return $this->role === self::ROLE_OBSERVER;
    }

    /** @deprecated Use isFaculty() per SRS */
    public function isCreator(): bool
    {
        return $this->isFaculty();
    }

    /** @deprecated Use isHod() per SRS */
    public function isApprover(): bool
    {
        return $this->isHod();
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /** SRS: Faculty can belong to multiple departments (user_departments). */
    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'user_departments');
    }

    /** Department where this user is HOD (at most one per SRS). */
    public function headedDepartment(): HasOne
    {
        return $this->hasOne(Department::class, 'head_user_id');
    }

    public function syllabiCreated(): HasMany
    {
        return $this->hasMany(Syllabus::class, 'submitted_by');
    }

    public function syllabiApproved(): HasMany
    {
        return $this->hasMany(Syllabus::class, 'approved_by');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(SyllabusReview::class, 'reviewer_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /** All role values for validation/forms. */
    public static function roles(): array
    {
        return [
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_CDC => 'CDC Incharge',
            self::ROLE_HOD => 'HOD',
            self::ROLE_FACULTY => 'Faculty',
            self::ROLE_OBSERVER => 'Observer',
        ];
    }
}
