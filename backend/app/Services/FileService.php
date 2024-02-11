<?php

namespace App\Services;

use Illuminate\Support\Facades\File;


class FileService
{
    public function saveFile($file, $name, $oldFileName = null)
    {
        $fileName = $name . '.' . $file->extension();
        $path = public_path('uploads');


        if ($oldFileName) {
            $oldFilePath = $path . DIRECTORY_SEPARATOR . $oldFileName;
            if (File::exists($oldFilePath)) {
                File::delete($oldFilePath);
            }
        }


        $file->move($path, $fileName);

        return true;
    }
}
