<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Imports\RecipeImport;
use App\Models\Recipe;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class RecipeController extends Controller
{
    use ResponseTrait;

    public function recipe(Request $request)
    {
        $searchQuery = $request->input('search');

        if (strpos($searchQuery, ',') !== false) {
            $keywords = explode(',', $searchQuery);

            $keywords = array_filter(array_map('trim', $keywords));

            $recipes = Recipe::where(function ($query) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $query->orWhere('title', 'like', '%' . $keyword . '%')
                        ->orWhere('ingredients', 'like', '%' . $keyword . '%');
                }
            })->get();
        } else {
            $recipes = Recipe::where('title', 'like', '%' . $searchQuery . '%')
                ->orWhere('ingredients', 'like', '%' . $searchQuery . '%')
                ->get();
        }

        if ($recipes->isEmpty()) {
            return $this->errorResponse('No recipes found for the given search query.', 404);
        }

        return $this->successResponse($recipes);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        $file = $request->file('file');
        Excel::import(new RecipeImport, $file);

        
        return response()->json(['data' => Recipe::all()]);
    }

    public function recipeDetail($recipeId)
    {
        $recipe = Recipe::find($recipeId);
        return $this->successResponse($recipe);
    }
}
