<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LicenseProgram extends Model
{
    use HasFactory;

    protected $table = 'license_programs';

    protected $fillable = [
        'name',
        'code',
        'description',
        'language',
    ];

    /**
     * Get the licenses for the program.
     */
    public function licenses()
    {
        return $this->hasMany(License::class, 'program_id');
    }

    /**
     * Get the modules for the program.
     */
    public function modules()
    {
        return $this->hasMany(LicenseModule::class, 'program_id');
    }
}
