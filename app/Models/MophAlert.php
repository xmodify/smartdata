<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class MophAlert extends Model
{
    protected $table = 'moph_alert';
    protected $fillable = ['name', 'client_id', 'secret', 'active', 'enable_2fa'];
}
