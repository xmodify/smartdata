<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderId extends Model
{
    protected $table = 'provider_id';
    
    protected $fillable = [
        'name',
        'health_id_client_id',
        'health_id_secret',
        'provider_id_client_id',
        'provider_id_secret',
        'active'
    ];
}
