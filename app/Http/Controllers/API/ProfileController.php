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

    private function getQuotes($result)
    {
        switch ($result) {
            case "Kekurangan Berat Badan":
                $quotes = "Tingkatkan asupan kalori Anda dengan makan makanan bergizi dan seimbang";
                break;
            case "Normal":
                $quotes = "Jaga berat badan Anda tetap stabil dengan makan makanan bergizi dan seimbang";
                break;
            case "Kelebihan Berat Badan":
                $quotes = "Mulailah dengan perubahan seperti olahraga beban secara disiplin setiap harinya";
                break;
            case "Obesitas Tipe I":
                $quotes = "Mulailah dengan perubahan seperti olahraga dan kurangi makanan berkalori dan gula";
                break;
            case "Obesitas Tipe II":
                $quotes = "Berkonsultasilah dengan dokter dan kurangi makan secara bertahap";
                break;
            case "Obesitas Tipe III":
                $quotes = "Berkonsultasilah dengan dokter atau ahli diet untuk mengembangkan rencana penurunan berat badan yang aman dan efektif";
                break;

            default:
                break;
        }

        return $quotes;
    }

    public function storeProfile(Request $request)
    {
        $request->validate([
            'gender' => 'required|in:male,female',
            'date_of_birth' => 'required|date',
            'phone' => 'required|string',
            'profile_picture' => 'required',
            'weight' => 'required',
            'height' => 'required',
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
        $profile->weight = $request->weight;
        $profile->height = $request->height;

        if($request->profile_picture){
            $image = $request->file('profile_picture');
            $resizedImage = Image::make($image);
            $imageString = (string) $resizedImage->encode();

            $imagePath = 'img/profile/' . time() . "_$userId" . "_" ."profile-picture.png";
            Storage::disk('gcs')->put($imagePath, $imageString);
    
            $profile->profile_picture = $imagePath;
        }

        $data = [
		    'tinggi' => $request->height,
            'berat' => $request->weight
		];
        
        $detectImageUrl = env('DETECT_OBESITY_URL');
        $curl = curl_init();
		curl_setopt_array($curl, array(
		    CURLOPT_URL => $detectImageUrl,
		    CURLOPT_RETURNTRANSFER => true,
		    CURLOPT_ENCODING => "",
		    CURLOPT_MAXREDIRS => 10,
		    CURLOPT_TIMEOUT => 30000,
		    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		    CURLOPT_CUSTOMREQUEST => "POST",
		    CURLOPT_POSTFIELDS => $data,
		    CURLOPT_HTTPHEADER => array(
		        "accept: */*",
		        "accept-language: en-US,en;q=0.8",
		        "content-type: multipart/form-data",
		    ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);

		if ($err) {
            return $this->errorResponse("Failed to make prediction request. Error: $err");
		} else {
		    $result = json_decode($response, true);
            $profile->body_status = $result['result'];
            $profile->quotes = $this->getQuotes($result['result']);
		}

        $profile->save();

        $profile['name'] = $user->name;
        $profile['email'] = $user->email;
        return $this->successResponse($profile, 'Sukses Update Profile !', 200);
    }

    public function updateBody(Request $request)
    {
        $request->validate([
            'weight' => 'required',
            'height' => 'required',
        ]);

        $user = auth()->user();
        $userId = $user->id;
        $profile = $user->profile;

        if (!$profile) {
            $profile = new Profile();
        }

        $profile->user_id = $userId;
        $profile->weight = $request->weight;
        $profile->height = $request->height;

        $data = [
		    'tinggi' => $request->height,
            'berat' => $request->weight
		];
        
        $detectImageUrl = env('DETECT_OBESITY_URL');
        $curl = curl_init();
		curl_setopt_array($curl, array(
		    CURLOPT_URL => $detectImageUrl,
		    CURLOPT_RETURNTRANSFER => true,
		    CURLOPT_ENCODING => "",
		    CURLOPT_MAXREDIRS => 10,
		    CURLOPT_TIMEOUT => 30000,
		    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		    CURLOPT_CUSTOMREQUEST => "POST",
		    CURLOPT_POSTFIELDS => $data,
		    CURLOPT_HTTPHEADER => array(
		        "accept: */*",
		        "accept-language: en-US,en;q=0.8",
		        "content-type: multipart/form-data",
		    ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);

		if ($err) {
            return $this->errorResponse("Failed to make prediction request. Error: $err");
		} else {
		    $result = json_decode($response, true);
            $profile->body_status = $result['result'];
            $profile->quotes = $this->getQuotes($result['result']);
		}

        $profile->save();

        $profile['name'] = $user->name;
        $profile['email'] = $user->email;
        return $this->successResponse($profile, 'Sukses Update Profile !', 200);
    }

    public function viewProfile()
    {
        $user = auth()->user();
    
        if ($user->profile) {
            $user->profile['name'] = $user->name;
            $user->profile['email'] = $user->email;
    
            return $this->successResponse($user->profile);
        } else {
            $userData = [
                'name' => $user->name,
                'email' => $user->email,
                'is_guest' => $user->is_guest
            ];
            return $this->successResponse($userData);
        }
    }
}
