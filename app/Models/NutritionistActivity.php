<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class NutritionistActivity extends Model
{
    protected $guarded = [];

    public function nutritionist(): BelongsTo
    {
        return $this->belongsTo(Nutritionist::class)->withTrashed();
    }

    public function getProof1LinkAttribute()
    {
        return $this->attributes['proof_1'] != null ? Storage::disk('gcs')->url($this->attributes['proof_1']) : null;
    }

    public function getProof2LinkAttribute()
    {
        return $this->attributes['proof_2'] != null ? Storage::disk('gcs')->url($this->attributes['proof_2']) : null;
    }

    public function getProof3LinkAttribute()
    {
        return $this->attributes['proof_3'] != null ? Storage::disk('gcs')->url($this->attributes['proof_3']) : null;
    }
}
