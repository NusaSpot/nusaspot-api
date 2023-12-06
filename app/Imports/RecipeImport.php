<?php

namespace App\Imports;

use App\Models\Recipe;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class RecipeImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Recipe([
            'image' => $row['image'],
            'category' => $row['category'],
            'title' => $row['title'],
            'ingredients' => $row['ingredients'],
            'tutorials' => $row['tutorials'],
        ]);
    }
}