<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchemeAssessmentComponent extends Model
{
    protected $fillable = [
        'scheme_id',
        'parent_id',
        'component_code',
        'component_name',
        'type',
        'value_kind',
        'is_input',
        'contributes_to_total',
        'display_order',
    ];

    protected $casts = [
        'is_input' => 'boolean',
        'contributes_to_total' => 'boolean',
    ];

    public function scheme()
    {
        return $this->belongsTo(Scheme::class);
    }

    public function parent()
    {
        return $this->belongsTo(SchemeAssessmentComponent::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(SchemeAssessmentComponent::class, 'parent_id')->orderBy('display_order');
    }

    public function isLeaf()
    {
        return !$this->children()->exists();
    }
}
