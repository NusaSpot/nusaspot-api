<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Detect;
use App\Models\DetectDetail;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
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

        $detectDetail->detect_id = $detectId;
        $detectDetail->image = $imagePath;
        $detectDetail->save();

        return $this->successResponse(null, 'Success Store Image');
    }

    public function detectDetailDelete($detectId, $detectDetailId)
    {
        DetectDetail::where('detect_id', $detectId)->where('id', $detectDetailId)->delete();
        return $this->successResponse(null, 'Success Delete Image');
    }

    public function detectStartRecognition($detectId)
    {
        $user = auth()->user();
        $detectDetail = DetectDetail::where('detect_id', $detectId)->get();

        //recognition result update to detect detail

        return $this->successResponse(['nanti disini data image dan hasil recognizenya muncul'], 'Success Detect Result');
    }

    public function detectFinish($detectId)
    {
        $detect = Detect::find($detectId);
        $detect->status = 1;
        $detect->save();

        return $this->successResponse(null, 'Success Finish Detection');
    }
}
