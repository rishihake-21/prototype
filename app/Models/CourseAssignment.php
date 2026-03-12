<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseAssignment extends Model
{
    protected $fillable = [
        'course_id',
        'department_id',
        'faculty_user_id',
        'assigned_by',
        'assigned_at',
        'deadline',
        'term',
        'academic_year',
        'status',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'deadline'    => 'date',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function faculty()
    {
        return $this->belongsTo(User::class, 'faculty_user_id');
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
    public function syllabi()
    {
        return $this->hasMany(Syllabus::class, 'assignment_id');
    }
}
