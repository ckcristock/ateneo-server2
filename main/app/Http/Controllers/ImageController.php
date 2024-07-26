<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Laravel\Facades\Image;


class ImageController extends Controller
{
    public function image(Request $request)
    {
        $path = $request->get('path');
        if ($path) {
            $path = public_path('app/public') . '/' . $path;
            return response()->file($path);
        }
        return 'path not found';
    }

    public function optimize()
    {
        $dir = public_path('app/public/people');
        $files = scandir($dir);
        foreach ($files as $file) {
            if (is_file($dir . '/' . $file) && getimagesize($dir . '/' . $file)) {
                $image = Image::read($dir . '/' . $file);
                $image->cover(800, 800);
                $image->save($dir . '/' . $file);
            }
        }
    }
}
