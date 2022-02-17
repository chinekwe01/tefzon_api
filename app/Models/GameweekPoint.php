<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameweekPoint extends Model
{
    use HasFactory;
    protected $fillable = [
        'point',
        'points',
        'gameweek',
        'user_id',
      

    ];
}
