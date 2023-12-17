<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Imports\RecipeImport;
use App\Models\Nutritionist;
use App\Models\Recipe;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class NutritionistController extends Controller
{
    use ResponseTrait;

    public function nutritionist(Request $request)
    {
        $searchQuery = $request->input('search');
        
            $nutritionists = Nutritionist::where('name', 'like', '%' . $searchQuery . '%')->where('is_eligible', 'approved')->with('nutritionistProfile')->get();

        if ($nutritionists->isEmpty()) {
            return $this->errorResponse('No nutritionist found for the given search query.', 404);
        }

        return $this->successResponse($nutritionists);
    }
}
