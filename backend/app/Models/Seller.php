<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Seller extends Model
{

    protected $fillable = [
        'user_id',
        'store_name',
        'store_slug',
        'description',
        'verification_status',
        'verification_notes',
        'rating_avg',
        'commission_override_pct',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
