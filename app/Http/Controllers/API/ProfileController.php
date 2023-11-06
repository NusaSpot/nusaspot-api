<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ProfileController extends Controller
{
    use ResponseTrait;

    public function storeProfile(Request $request)
    {
        $request->validate([
            'gender' => 'required|in:male,female',
            'date_of_birth' => 'required|date',
            'phone' => 'required|string',
            'profile_picture' => 'required',
        ]);

        $user = auth()->user();
        $userId = $user->id;
        $profile = $user->profile;

        if (!$profile) {
            $profile = new Profile();
        }

        $profile->user_id = $userId;
        $profile->gender = $request->gender;
        $profile->date_of_birth = $request->date_of_birth;
        $profile->phone = $request->phone;

        if($request->profile_picture){
            $base64Image = $request->input('profile_picture');

            $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image));

            $imagePath = 'img/profile/' . time() . "_$userId" . "_" ."profile-picture.png";
            Storage::disk('gcs')->put($imagePath, $image);
    
            $profile->profile_picture = $imagePath;
        }

        $profile->save();

        return $this->successResponse($profile, 'Sukses Update Profile !', 200);
    }

    public function viewProfile()
    {
        $user = auth()->user();
        return $this->successResponse($user->profile);
    }
}
