<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\DriverDoc;
use App\Models\User;

class FileController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/upload-file",
     *      operationId="uploadFile",
     *      tags={"Files"},
     *     security={{"bearerAuth": {}}},
     *      summary="Upload a file",
     *      description="Upload a file and associate it with the authenticated user's driver documents.",
     *      @OA\RequestBody(
     *          required=true,
     *          description="File to upload",
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  type="object",
     *                  @OA\Property(
     *                      property="file",
     *                      description="File to upload",
     *                      type="string",
     *                      format="binary"
     *                  ),
     *                  @OA\Property(
     *                      property="type",
     *                      description="Type of the file",
     *                      type="string",
     *                      enum={"image_licence_front", "image_licence_back", "image_pasport_front", "image_pasport_address", "image_fase_and_pasport"}
     *                  ),
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="File uploaded successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad request",
     *          @OA\JsonContent(
     *              @OA\Property(property="error", type="string", example="Invalid file type"),
     *          ),
     *      ),
     *      security={
     *          {"bearerAuth": {}}
     *      }
     * )
     */
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
        $fileName = now()->format('YmdHis') . '_' . Str::random(10) . '.' . $request->file->extension();

        $filePath = $request->file->storeAs('uploads/user/' . $user_id, $fileName); //сохраняет в \backend\storage\app\uploads\user

        $docs->{$type} = $filePath;
        $docs->save();

        return response()->json(['success' => true]);
    }
}
