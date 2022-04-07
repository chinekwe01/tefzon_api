<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeagueTable extends Model
{
    use HasFactory;
    protected $table = 'league_table';
    protected $fillable = [
        'rank',
        'points',
        'gameweek',
        'user_id',
        'league_id',

    ];
    protected $hidden = [
        'rank'


    ];

    public function league()
    {
        return $this->belongsTo(League::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
