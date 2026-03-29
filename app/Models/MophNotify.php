<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MophNotify extends Model
{
    protected $table = 'moph_notify';
    protected $fillable = ['name', 'client_id', 'secret'];
}
