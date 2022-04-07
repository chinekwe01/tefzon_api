<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chip extends Model
{
    use HasFactory;

    protected $fillable = [
        'free_hit',
        'bench_boost',
        'wildcard',
        'triple_captain',
        'free_transfer',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
