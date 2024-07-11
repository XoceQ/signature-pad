<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Signature;



class SignatureController extends Controller
{
    public function index()
    {
        return view('signature-pad');
    }

    public function store(Request $request)
    {
        $folderPath = storage_path('app/public/signatures/'); // Create signatures folder in public directory

    $image_parts = explode(";base64,", $request->signed);
    $image_type_aux = explode("image/", $image_parts[0]);
    $image_type = $image_type_aux[1];
    $image_base64 = base64_decode($image_parts[1]);

    // Create a new image from the base64 string
    $signature_image = imagecreatefromstring($image_base64);

    // Get the width and height of the signature image
    $width = imagesx($signature_image);
    $height = imagesy($signature_image);

    // Create a new true color image with a white background
    $white_background = imagecreatetruecolor($width, $height);
    $white = imagecolorallocate($white_background, 255, 255, 255); // White color
    imagefill($white_background, 0, 0, $white);

    // Copy the signature image onto the white background
    imagecopy($white_background, $signature_image, 0, 0, 0, 0, $width, $height);

    // Save the new image
    $file_name = uniqid() . '.' . $image_type;
    $file_path = $folderPath . $file_name;

    if ($image_type == 'png') {
        imagepng($white_background, $file_path);
    } elseif ($image_type == 'jpeg' || $image_type == 'jpg') {
        imagejpeg($white_background, $file_path);
    }

    // Destroy the images to free up memory
    imagedestroy($signature_image);
    imagedestroy($white_background);

    // Save in your data in database here.
    Signature::create([
        'name' => $request->name,
        'signature' => $file_name
    ]);

    return back()->with('success', 'Successfully saved the signature');

    }
}
