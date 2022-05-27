<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GamerSquad extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'squad_no',
        'player_name',
        'player_position',
        'player_id',
        'position_id',
        'value',
        'is_captain',
        'is_vice_captain',
        'is_absent',
        'is_injured',
        'user_id',
        'team_id',
        'team',
        'image_path',
        'starting',
        'next_fixture',
        'deleted_by'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function gameweekpoint()
    {
        return $this->hasMany(GameweekPoint::class);
    }
}
