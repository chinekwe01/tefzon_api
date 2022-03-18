<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, softDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'phone',
        'is_admin',
        'profile',
        'referral_link'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function socialaccounts()
    {
        return $this->hasMany(LinkedSocialAccount::class);
    }

    public function squad()
    {
        return $this->hasMany(GamerSquad::class);
    }
    public function forwards()
    {
        return $this->squad()->get()->filter(function ($a) {
            return $a->position_id == 4;
        });
    }
    public function defenders()
    {
        return $this->squad()->get()->filter(function ($a) {
            return $a->position_id == 2;
        });
    }
    public function goalkeepers()
    {
        return $this->squad()->get()->filter(function ($a) {
            return $a->position_id == 1;
        });
    }
    public function midfielders()
    {
        return $this->squad()->get()->filter(function ($a) {
            return $a->position_id == 3;
        });
    }
    public function totalvalue()
    {
        return $this->squad()->get()->map(function ($a) {return $a->value;})->reduce(function ($a, $b) {
            return $a + $b;
        }, 0);;
    }

    public function referral()
    {
        return $this->belongsTo(Referral::class);
    }
    public function leagues()
    {
        return $this->belongsToMany(League::class)->withPivot('is_owner');
    }
}