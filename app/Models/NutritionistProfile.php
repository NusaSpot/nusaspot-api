<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class NutritionistProfile extends Model
{
    protected $guarded = [];

    public function nutritionist(): BelongsTo
    {
        return $this->belongsTo(Nutritionist::class);
    }

    public function getProfilePictureLinkAttribute()
    {
        return $this->attributes['profile_picture'] != null ? Storage::disk('gcs')->url($this->attributes['profile_picture']) : null;
    }

    public function getCvLinkAttribute()
    {
        return $this->attributes['cv'] != null ? Storage::disk('gcs')->url($this->attributes['cv']) : null;
    }

    public function getEducationValueAttribute()
    {
        switch ($this->education) {
            case "sma":
                $value = 'SMA / SMK Sederajat';
                break;
            case "s1":
                $value = 'Sarjana (S1)';
                break;
            case "d3":
                $value = 'Diploma (D3)';
                break;
            case "s2":
                $value = 'Magister (S2)';
                break;
            case "s3":
                $value = 'Doktor (S3)';
                break;
        }

        return $value;
    }
}
