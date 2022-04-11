<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BannedGamer extends Model
{
    use HasFactory;
    public function user()
    {
        return $this->hasOne(User::class);
    }
    protected $fillable = [
        'message',
        'user_id',
        'start',
        'end',
        'duration'
    ];
}
