<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeagueUser extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','league_id','is_owner'];
}
