<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AwardClassRule extends Model
{
    protected $fillable = [
        'programme_id',
        'rule_key',
        'include_course_ids',
        'exclude_course_types',
    ];

    protected $casts = [
        'include_course_ids' => 'array',
        'exclude_course_types' => 'array',
    ];
}
