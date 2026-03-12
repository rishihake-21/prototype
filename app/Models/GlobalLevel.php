<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlobalLevel extends Model
{
    protected $fillable = [
        'level_code',
        'level_name',
        'sort_order',
    ];
}
