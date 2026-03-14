<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchemeLearningComponent extends Model
{
    protected $fillable = [
        'scheme_id',
        'parent_id',
        'component_code',
        'component_name',
        'type',
        'display_order',
    ];

    public function scheme()
    {
        return $this->belongsTo(Scheme::class);
    }

    public function parent()
    {
        return $this->belongsTo(SchemeLearningComponent::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(SchemeLearningComponent::class, 'parent_id')->orderBy('display_order');
    }

    public function isLeaf()
    {
        return $this->children()->count() === 0;
    }
}

