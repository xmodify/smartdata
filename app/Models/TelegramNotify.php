<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramNotify extends Model
{
    protected $table = 'telegram_notify';
    protected $primaryKey = 'name';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['name', 'name_th', 'value'];
}
