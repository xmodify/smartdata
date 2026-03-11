<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'role',
        'active',
        'allow_hosxp_report',
        'allow_asset',
        'allow_personnel',
        'allow_incident',
        'allow_skpcard',
        'allow_audit',
        'allow_assessment',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function hasAccessHosxpReport()
    {
        return $this->role === 'admin' || $this->allow_hosxp_report === 'Y';
    }

    public function hasAccessAsset()
    {
        return $this->role === 'admin' || $this->allow_asset === 'Y';
    }

    public function hasAccessPersonnel()
    {
        return $this->role === 'admin' || $this->allow_personnel === 'Y';
    }

    public function hasAccessIncident()
    {
        return $this->role === 'admin' || $this->allow_incident === 'Y';
    }

    public function hasAccessSkpcard()
    {
        return $this->role === 'admin' || $this->allow_skpcard === 'Y';
    }

    public function hasAccessAudit()
    {
        return $this->role === 'admin' || $this->allow_audit === 'Y';
    }

    public function hasAccessAssessment()
    {
        return $this->role === 'admin' || $this->allow_assessment === 'Y';
    }

    public function hasAccessRole($role)
    {
        return $this->role === $role;
    }
}
