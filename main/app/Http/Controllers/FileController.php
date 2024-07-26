<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FileController extends Controller
{
    public function file(Request $request)
    {
        $path = $request->get('path');
        if ($path) {
            $download = public_path('app/' . $path);
            return response()->download($download);
        }
        return 'path not found';
    }

    public function fileView(Request $request)
    {
        $path = $request->get('path');
        if ($path) {
            $download = public_path('app/' . $path);
            return response()->file($download);
        }
        return 'path not found';
    }
}
