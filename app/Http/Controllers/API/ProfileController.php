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
            $image = $request->file('profile_picture');
            $resizedImage = Image::make($image);
            $imageString = (string) $resizedImage->encode();

            $imagePath = 'img/profile/' . time() . "_$userId" . "_" ."profile-picture.png";
            Storage::disk('gcs')->put($imagePath, $imageString);
    
            $profile->profile_picture = $imagePath;
        }

        $profile->save();

        $profile['name'] = $user->name;
        $profile['email'] = $user->email;
        return $this->successResponse($profile, 'Sukses Update Profile !', 200);
    }

    public function viewProfile()
    {
        $user = auth()->user();
        $user->profile['name'] = $user->name;
        $user->profile['email'] = $user->email;
        return $this->successResponse($user->profile);
    }
}
