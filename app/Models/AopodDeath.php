<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AopodDeath extends Model
{
    protected $table = 'aopod_deaths';
    protected $guarded = [];
    protected $casts = [
        'fetched_at' => 'datetime',
    ];
}
