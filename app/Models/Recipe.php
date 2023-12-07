<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Recipe extends Model
{
    protected $guarded = [];
    protected $appends = ['image'];

    public function getImageAttribute()
    {
        return $this->attributes['image'] != null ? Storage::disk('gcs')->url($this->attributes['image']) : null;
    }
}
