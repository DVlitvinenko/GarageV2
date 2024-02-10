<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\DriverDoc;
use App\Models\User;
use App\Http\Controllers\Suit;


class FileController extends Controller
{

    public function uploadFile(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:image_licence_front,image_licence_back,image_pasport_front,image_pasport_address,image_fase_and_pasport',
            'file' => 'required|file|mimes:png,jpg,jpeg|max:7168', // Максимальный размер файла 7 МБ
        ]);
        $type = $request->type;
        $user = Auth::user();
        $user_id = $user->id;
        $driver = Driver::where('user_id', $user_id)->first();
        $docs = DriverDoc::where('driver_id', $driver->id)->first();
        if (!$docs) {
            $docs = new DriverDoc(['driver_id' => $driver->id]);
        }
        $fileName = $type . '.' . $request->file->extension();
        $filePath = 'uploads/user/' . $user_id . '/' . $fileName;

        if (Storage::exists($filePath . $fileName)) {
            Storage::delete($filePath . $fileName);
        }

        $request->file->storeAs('uploads/user/' . $user_id, $fileName);
        $docs->{$type} = $filePath;
        $docs->save();
        return response()->json(['success' => true]);
    }
}
