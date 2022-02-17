<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class League extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'participants',
        'type',
        'duration',
        'start',
        'end',
        'status',
        'code',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('is_owner');
    }
}
