<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LicenseModule extends Model
{
    use HasFactory;

    protected $table = 'license_modules';

    protected $fillable = [
        'program_id',
        'code',
        'name',
        'description',
    ];

    /**
     * Get the program that owns the module.
     */
    public function program()
    {
        return $this->belongsTo(LicenseProgram::class, 'program_id');
    }

    /**
     * Get the activations for this module.
     */
    public function activations()
    {
        return $this->hasMany(LicenseModuleActivation::class, 'module_id');
    }
}
