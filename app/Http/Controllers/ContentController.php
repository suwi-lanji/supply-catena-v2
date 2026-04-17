<?php

namespace App\Http\Controllers;

class ContentController extends Controller
{
    public function show($filename)
    {
        $path = storage_path("app/public/{$filename}");

        if (! file_exists($path)) {
            abort(404);
        }

        $file = file_get_contents($path);
        $type = mime_content_type($path);

        return response($file)->header('Content-Type', $type);
    }
}
