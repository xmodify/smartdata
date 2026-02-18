<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SysVar extends Model
{
    protected $table = 'sys_var';
    protected $primaryKey = 'sys_name';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'sys_name',
        'sys_name_th',
        'sys_value',
    ];
}
