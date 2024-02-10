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

    /**
     * Загрузка файла
     *
     * Этот эндпоинт позволяет аутентифицированным пользователям загружать файл и связывать его с их документами водителя.
     *
     * @OA\Post(
     *     path="/api/upload-file",
     *     operationId="uploadFile",
     *     tags={"Files"},
     *     security={{"bearerAuth": {}}},
     *     summary="Загрузить файл",
     *     description="Загрузить файл и связать его с документами водителя аутентифицированного пользователя",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Файл для загрузки",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="file",
     *                     description="Файл для загрузки",
     *                     type="string",
     *                     format="binary"
     *                 ),
     *                 @OA\Property(
     *                     property="type",
     *                     description="Тип файла",
     *                     type="string",
     *                     enum={"image_licence_front", "image_licence_back", "image_pasport_front", "image_pasport_address", "image_fase_and_pasport"}
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Файл успешно загружен",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Неверный запрос",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Недопустимый тип файла"),
     *         ),
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     *
     * @param \Illuminate\Http\Request $request The request object containing the file and its type
     * @return \Illuminate\Http\JsonResponse JSON response indicating the success or failure of the file upload
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
        $fileName = $type . $request->file->extension();
        $filePath = 'uploads/user/' . $user_id . '/' . $fileName;

        if (Storage::exists($filePath . $fileName)) {
            Storage::delete($filePath . $fileName); // Удаление существующего файла
        }

        $request->file->storeAs('uploads/user/' . $user_id, $fileName); // Сохранение нового файла
        $docs->{$type} = $filePath;
        $docs->save();
        return response()->json(['success' => true]);
    }
}
