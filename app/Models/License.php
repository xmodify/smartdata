<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class License extends Model
{
    use HasFactory;

    protected $table = 'licenses';

    protected $fillable = [
        'program_id',
        'license_key',
        'customer_name',
        'hcode',
        'hardware_id',
        'license_type',
        'status',
        'activated_at',
        'expired_at',
        'notes',
    ];

    protected $casts = [
        'activated_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    /**
     * Get the program that owns the license.
     */
    public function program()
    {
        return $this->belongsTo(LicenseProgram::class, 'program_id');
    }

    /**
     * Get the module activations for the license.
     */
    public function moduleActivations()
    {
        return $this->hasMany(LicenseModuleActivation::class, 'license_id');
    }

    /**
     * Get the activated modules for the license.
     */
    public function activatedModules()
    {
        return $this->belongsToMany(LicenseModule::class, 'license_module_activations', 'license_id', 'module_id')
            ->withPivot('status', 'expired_at')
            ->withTimestamps();
    }

    /**
     * Check if the license is expired.
     */
    public function isExpired()
    {
        if (is_null($this->expired_at)) {
            return false;
        }
        return $this->expired_at->isPast();
    }

    /**
     * Generate a unique license key.
     */
    public static function generateKey($prefix = 'SMART')
    {
        do {
            $key = sprintf(
                '%s-%s-%s-%s',
                strtoupper($prefix),
                strtoupper(Str::random(4)),
                strtoupper(Str::random(4)),
                strtoupper(Str::random(4))
            );
        } while (self::where('license_key', $key)->exists());

        return $key;
    }
}
