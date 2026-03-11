<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skpcard extends Model
{
    use HasFactory;

    protected $fillable = [
        'cid',
        'name',
        'birthday',
        'address',
        'phone',
        'buy_date',
        'ex_date',
        'price',
        'rcpt',
    ];

    protected $casts = [
        'birthday' => 'date',
        'buy_date' => 'date',
        'ex_date' => 'date',
    ];
}
