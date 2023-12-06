<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Detect;
use App\Models\DetectDetail;
use App\Models\Recipe;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class DetectController extends Controller
{
    use ResponseTrait;

    public function detect()
    {
        $user = auth()->user();
        $detect = Detect::where('user_id', $user->id)->where('status', 1)->get();
        return $this->successResponse($detect);
    }

    public function detectStart()
    {
        $user = auth()->user();
        $detect = Detect::where('user_id', $user->id)->where('status', 0)->first();
        if(!$detect){
            $detect = new Detect();
            $detect->user_id = $user->id;
            $detect->status = 0;
            $detect->save();
        }

        return $this->successResponse($detect);
    }

    public function detectDetail($detectId)
    {
        $detectDetail = DetectDetail::where('detect_id', $detectId)->get();
        return $this->successResponse($detectDetail);
    }

    public function detectDetailStore($detectId, Request $request)
    {
        $user = auth()->user();
        $detectDetail = new DetectDetail();
        $image = $request->file('image');
        $resizedImage = Image::make($image);
        $imageString = (string) $resizedImage->encode();
        $originalName = $image->getClientOriginalName();
        $imagePath = 'img/detect/' . time() . "_$user->id" . "_" ."$originalName";
        Storage::disk('gcs')->put($imagePath, $imageString);

        $data = [
		    'imgUrl' => Storage::disk('gcs')->url($imagePath),
		];
        
        $curl = curl_init();
		curl_setopt_array($curl, array(
		    CURLOPT_URL => "https://detect-image-1-4m337nn6bq-et.a.run.app/predict-single-url",
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

            $detectDetail->detect_id = $detectId;
            $detectDetail->image = $imagePath;
            $detectDetail->result = $result['result']['category'];
            $detectDetail->save();

            return $this->successResponse(null, 'Success Store Image');
		}
    }

    public function detectDetailDelete($detectId, $detectDetailId)
    {
        DetectDetail::where('detect_id', $detectId)->where('id', $detectDetailId)->delete();
        return $this->successResponse(null, 'Success Delete Image');
    }

    public function detectFinish($detectId)
    {
        $detect = Detect::find($detectId);
        $detect->status = 1;
        $detect->save();

        $resultArray = [];
        foreach ($detect->detectDetail as $detail) {
            $resultArray[] = $detail->result;
        }

        $resultString = implode(', ', $resultArray);

        if (strpos($resultString, ',') !== false) {
            $keywords = explode(',', $resultString);
    
            $keywords = array_filter(array_map('trim', $keywords));
    
            $recipes = Recipe::where(function ($query) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $query->orWhere('title', 'like', '%' . $keyword . '%')
                          ->orWhere('ingredients', 'like', '%' . $keyword . '%');
                }
            })->get();
        } else {
            $recipes = Recipe::where('title', 'like', '%' . $resultString . '%')
                ->orWhere('ingredients', 'like', '%' . $resultString . '%')
                ->get();
        }
    
        if ($recipes->isEmpty()) {
            return $this->errorResponse('No recipes found for the given search query.', 404);
        }

        return $this->successResponse($recipes, 'Success Finish Detection');
    }
}
