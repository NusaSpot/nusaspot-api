<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Nutritionist extends Model
{
    use SoftDeletes;

    public function nutritionistProfile(): HasOne
    {
        return $this->hasOne(NutritionistProfile::class);
    }

    public function getEligibleValueAttribute()
    {
        switch ($this->is_eligible) {
            case "not_completed":
                $value = 'Belum Lengkap';
                break;
            case "pending":
                $value = 'Pending';
                break;
            case "rejected":
                $value = 'Ditolak';
                break;
            case "approved":
                $value = 'Diterima';
                break;
        }

        return $value;
    }
}
