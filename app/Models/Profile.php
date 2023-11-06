<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Profile extends Model
{
    protected $guarded = [];
    protected $appends = ['profile_picture'];

    public function getProfilePictureAttribute()
    {
        return $this->attributes['profile_picture'] != null ? Storage::disk('gcs')->url($this->attributes['profile_picture']) : null;
    }
}
