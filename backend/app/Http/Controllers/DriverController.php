<?php

namespace App\Http\Controllers;

use App\Enums\UserStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Driver;
use App\Models\DriverDoc;
use App\Services\FileService;

class DriverController extends Controller
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


    public function uploadDocs(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:image_licence_front,image_licence_back,image_pasport_front,image_pasport_address,image_fase_and_pasport',
            'file' => 'required|file|mimes:png,jpg,jpeg|max:7168', // Максимальный размер файла 7 МБ
        ]);

        $type = $request->type;
        $user = Auth::guard('sanctum')->user();
        $user_id = $user->id;
        $driver = Driver::where('user_id', $user_id)->first();
        $docs = DriverDoc::where('driver_id', $driver->id)->first();


        if (!$docs) {
            $docs = new DriverDoc(['driver_id' => $driver->id]);
        }
        $oldFileName = $docs->{$type};

        $fileService = new FileService;
        $name = uuid_create(UUID_TYPE_RANDOM);
        $docs->{$type} = $name . '.' . $request->file('file')->extension();
        $fileService->saveFile($request->file('file'), $name, $oldFileName);
        $docs->save();
        $readyToVerify = $docs->image_licence_front && $docs->image_licence_back && $docs->image_pasport_front && $docs->image_pasport_address && $docs->image_fase_and_pasport;
        if ($readyToVerify) {
            $user->user_status = UserStatus::Verification->value;
            $user->save();
        }
        return response()->json(['success' => true]);
    }
}
