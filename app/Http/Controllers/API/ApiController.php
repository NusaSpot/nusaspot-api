<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class ApiController extends Controller
{
    public function firstApi()
    {
        $data = [
            [
                'title' => 'Judul 1',
                'image' => Storage::disk('public')->url('images/image1.png'),
                'description' => 'Deskripsi 1',
            ],
            [
                'title' => 'Judul 2',
                'image' => Storage::disk('public')->url('images/image2.png'),
                'description' => 'Deskripsi 2',
            ],
            [
                'title' => 'Judul 3',
                'image' => Storage::disk('public')->url('images/image3.png'),
                'description' => 'Deskripsi 3',
            ],
            [
                'title' => 'Judul 4',
                'image' => Storage::disk('public')->url('images/image4.png'),
                'description' => 'Deskripsi 4',
            ],
            [
                'title' => 'Judul 5',
                'image' => Storage::disk('public')->url('images/image5.png'),
                'description' => 'Deskripsi 5',
            ],
        ];
    
        $response = [
            'data' => $data,
            'status' => 200, // Ubah status sesuai kebutuhan Anda
        ];
    
        return response()->json($response, $response['status']);
    }
}
