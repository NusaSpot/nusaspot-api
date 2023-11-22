<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Detect extends Model
{
    protected $guarded = [];

    public function detectDetail()
    {
        return $this->hasMany(DetectDetail::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
