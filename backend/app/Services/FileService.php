<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class FileService
{
    public function saveFile($file, $path, $name)
    {
        $fileName = $name . '.' . $file->extension();

        if (Storage::exists($path . '/' . $fileName)) {
            Storage::delete($path . '/' . $fileName);
        }

        $file->storeAs($path, $fileName);

        return true;
    }
}
