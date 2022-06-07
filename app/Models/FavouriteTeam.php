<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FavouriteTeam extends Model
{
    use HasFactory;

    protected $fillable = ['team_id', 'user_id','team_name', 'image'];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
