<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeagueOverview extends Model
{
    use HasFactory;

    protected $fillable = [
        'winner_id',
        'winner_price',
        'second_id',
        'second_price',
        'third_id',
        'third_price',
        'league_id'
    ];

    public function league(){
        return $this->belongsTo(League::class);
    }

    public function winner()
    {
        return $this->hasOne(User::class, 'id', 'winner_id');
    }

    public function second()
    {
        return $this->hasOne(User::class, 'id', 'second_id');
    }

    public function third()
    {
        return $this->hasOne(User::class, 'id', 'third_id');
    }
}
