<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Scheme extends Model
{
    protected $fillable = [
    'programme_name',
    'programme_code',
    'year'
];

public function schemeLevels()
{
    return $this->hasMany(SchemeLevel::class);
}
}
