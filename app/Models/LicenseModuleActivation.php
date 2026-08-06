<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LicenseModuleActivation extends Model
{
    use HasFactory;

    protected $table = 'license_module_activations';

    protected $fillable = [
        'license_id',
        'module_id',
        'status',
        'expired_at',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
    ];

    /**
     * Get the license that owns this activation.
     */
    public function license()
    {
        return $this->belongsTo(License::class, 'license_id');
    }

    /**
     * Get the module being activated.
     */
    public function module()
    {
        return $this->belongsTo(LicenseModule::class, 'module_id');
    }
}
