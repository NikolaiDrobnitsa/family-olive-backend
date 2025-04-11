<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'is_admin',
        'is_verified',
        'verification_code',
        'interest_type',
        'ip_address',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'verification_code',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_admin' => 'boolean',
        'is_verified' => 'boolean',
    ];

    public function visits()
    {
        return $this->hasMany(Visit::class);
    }

    public function surveyResponses()
    {
        return $this->hasMany(SurveyResponse::class);
    }

    public function isAdmin()
    {
        return $this->is_admin;
    }

    public function isVerified()
    {
        return $this->is_verified;
    }
}
