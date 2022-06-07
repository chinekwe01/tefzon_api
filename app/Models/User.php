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
        'avatar',
        'referral_link',
        'last_name',
        'first_name',
        'gender',
        'address',
        'dob',
        'country',
        'favourite_teams'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'updated_at',
        'email_verified_at',
        'deleted_at',
        'deleted_by',
        'is_admin'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function favourite_teams()
    {
        return $this->hasMany(FavouriteTeam::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function withdraw_requests()
    {
        return $this->hasMany(WithdrawRequest::class);
    }
    public function active_chips()
    {
        return $this->hasMany(ActiveChip::class);
    }
    public function histories()
    {
        return $this->hasMany(History::class);
    }


    public function referrals()
    {
        return $this->hasMany(Referral::class);
    }

    public function accountdetails()
    {
        return $this->hasOne(AccountDetail::class);
    }

    public function socialaccounts()
    {
        return $this->hasMany(LinkedSocialAccount::class);
    }

    public function squad()
    {
        return $this->hasMany(GamerSquad::class);
    }
    public function freesquad()
    {
        return $this->hasMany(FreeHitSquad::class);
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

    public function freeforwards()
    {
        return $this->freesquad()->get()->filter(function ($a) {
            return $a->position_id == 4;
        });
    }
    public function freedefenders()
    {
        return $this->freesquad()->get()->filter(function ($a) {
            return $a->position_id == 2;
        });
    }
    public function freegoalkeepers()
    {
        return $this->freesquad()->get()->filter(function ($a) {
            return $a->position_id == 1;
        });
    }
    public function freemidfielders()
    {
        return $this->freesquad()->get()->filter(function ($a) {
            return $a->position_id == 3;
        });
    }
    public function totalvalue()
    {
        return $this->squad()->get()->map(function ($a) {
            return $a->value;
        })->reduce(function ($a, $b) {
            return $a + $b;
        }, 0);;
    }
    public function chip()
    {
        return $this->hasOne(Chip::class);
    }

    public function referral()
    {
        return $this->belongsTo(Referral::class);
    }
    public function leagues()
    {
        return $this->belongsToMany(League::class)->withPivot('is_owner');
    }
    public function gameweekpoint()
    {
        return $this->hasMany(GameweekPoint::class);
    }
}
