<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AopodFetchLog extends Model
{
    protected $table = 'aopod_fetch_logs';
    protected $guarded = [];
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'fetched_at' => 'datetime',
    ];
}
