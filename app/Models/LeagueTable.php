<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeagueTable extends Model
{
    use HasFactory;
    protected $fillable = [
        'ranks',
        'points',
        'gameweek',
        'user_id',
        'league_id',
       
    ];
}
