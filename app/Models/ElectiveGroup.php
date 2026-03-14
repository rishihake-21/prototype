<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ElectiveGroup extends Model
{
    protected $fillable = [
        'scheme_id',
        'code',
        'name',
        'elective_type',
        'select_count',
        'semester',
    ];

    public function courses()
    {
        return $this->hasManyThrough(Course::class, ElectiveGroupCourse::class, 'elective_group_id', 'id', 'id', 'course_master_id');
    }
}
