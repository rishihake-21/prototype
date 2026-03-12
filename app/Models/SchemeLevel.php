<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchemeLevel extends Model
{
    protected $fillable = [
        'scheme_id',
        'level_code',
        'level_name',
        'sort_order',
    ];

    public function scheme()
    {
        return $this->belongsTo(Scheme::class);
    }
}
