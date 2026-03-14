<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchemeLevel extends Model
{
    protected $fillable = [
    'scheme_id',
    'level_name',
    'is_audit',
    'courses_offered',
    'courses_completed',
    'th',
    'tu',
    'pr',
    'total_hours',
    'total_credits',
    'marks'
];

public function courses()
{
    return $this->hasMany(Course::class, 'scheme_level_id');
}
}
