<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $fillable = [
        'user_id', 'plat_id', 'rating', 'feedback'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function plat() {
        return $this->belongsTo(Plat::class);
    }
}
