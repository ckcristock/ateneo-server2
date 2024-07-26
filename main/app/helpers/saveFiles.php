<?php
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

if (!function_exists("saveFiles")) {
    function saveFiles($file, $path)
    {
        $file_info = $file;
        $extension = pathinfo($file_info['name'], PATHINFO_EXTENSION);
        $allowed_extensions = ["pdf", "png", "jpeg", "jpg", "doc", "docx", "xlsx", "mp3", "mp4"];
        if (!in_array(strtolower($extension), $allowed_extensions)) {
            return response()->json(['error' => 'Tipo de archivo no permitido'], 422);
        }
        $file_content = base64_decode(
            preg_replace(
                "#^data:[a-z]+/[\w\+]+;base64,#i",
                "",
                $file['file']
            )
        );
        $file_path = $path . Str::random(30) . time() . '.' . $extension;
        Storage::disk()->put($file_path, $file_content, "public");
        return $file_path;
    }
}