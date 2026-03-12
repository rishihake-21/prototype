<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentComponent extends Model
{
    protected $fillable = [
        'course_id',
        'type',
        'max_marks',
        'min_pass_marks',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
