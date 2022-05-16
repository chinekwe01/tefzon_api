<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameweekPoint extends Model
{
    use HasFactory;
    protected $fillable = [
        'player_name',
        'player_position',
        'player_id',
        'position_id',
        'is_captain',
        'is_vice_captain',
        'point',
        'gameweek',
        'user_id',
        'is_starting',
        'image_path',
        'next_fixture'


    ];
}
