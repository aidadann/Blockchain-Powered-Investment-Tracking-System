<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Investment extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'asset_name',
        'status',
        'blockchain_hash'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
