<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
          'balance',
          'wins',
          'loss',
          'draw',
          'cancelled',
        'account_name', 'bank_name', 'account_no'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
