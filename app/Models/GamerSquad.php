<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GamerSquad extends Model
{
    use HasFactory;
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
        'starting'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
