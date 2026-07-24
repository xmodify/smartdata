<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerComplain extends Model
{
    protected $table = 'customer_complains';

    protected $fillable = [
        'type',
        'name',
        'detail',
        'call_back',
        'phone',
        'email',
        'status',
        'responded_by',
        'response_note',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];
}
