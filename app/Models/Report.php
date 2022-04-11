<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'message' ,
        'reported_user_id',
        'user_id' ,
        'status'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function reported_user()
    {
        return $this->hasOne(User::class,'reported_user_id');
    }

}
