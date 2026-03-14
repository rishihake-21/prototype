<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ElectiveGroupCourse extends Model
{
    protected $fillable = [
        'elective_group_id',
        'course_master_id',
        'display_order',
    ];
}
