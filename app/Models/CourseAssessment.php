<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseAssessment extends Model
{
    protected $fillable = [
        'course_id',
        'component_id',
        'max_marks',
        'min_marks',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function component()
    {
        return $this->belongsTo(SchemeAssessmentComponent::class, 'component_id');
    }
}
