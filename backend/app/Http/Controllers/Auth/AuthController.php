<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Driver;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\ParserController;
use App\Models\City;
use App\Models\DriverSpecification;
use App\Models\DriverDoc;
use Session;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Enums\UserStatus;
use App\Enums\UserType;


class AuthController extends Controller
{


    /**
     * Получение данных пользователя (аутентифицированный запрос)
     *
     * @OA\Get(
     *     path="/user",
     *     operationId="GetUser",
     *     summary="Получение данных пользователя (аутентифицированный запрос)",
     *     tags={"Authentication"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Успешная аутентификация или регистрация",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 description="Данные пользователя",
     *                 @OA\Property(property="user_status", type="string", description="Статус пользователя", enum={"DocumentsNotUploaded", "Verification", "Verified"}),
     *                 @OA\Property(property="phone", type="string", description="Номер телефона пользователя"),
     *                 @OA\Property(property="name", type="string", nullable=true, description="Имя пользователя"),
     *                 @OA\Property(property="email", type="string", nullable=true, description="Email пользователя"),
     *                 @OA\Property(property="user_type", type="string", description="Тип пользователя", enum={"Driver", "Manager", "Admin"}),
     *                 @OA\Property(property="city_name", type="string", description="Название города"),
     *                 @OA\Property(
     *                     property="docs",
     *                     type="array",
     *                     description="Данные документов водителя",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="DriverDocumentType", type="string", description="Тип документа",enum={"image_licence_front", "image_licence_back", "image_pasport_front", "image_pasport_address", "image_fase_and_pasport"}),
     *                         @OA\Property(property="url", type="string", nullable=true, description="URL документа")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 description="Список ошибок валидации"
     *             )
     *         )
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function GetUser(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        $user->user_type = UserType::from($user->user_type)->name;
        $user->user_status = UserStatus::from($user->user_status)->name;
        $driver = Driver::where('user_id', $user->id)->with('city')->first();
        $driverDocs = DriverDoc::where('driver_id', $driver->id)->first(['image_licence_front', 'image_licence_back', 'image_pasport_front', 'image_pasport_address', 'image_fase_and_pasport']);

        $docs = [];
        foreach ($driverDocs->toArray() as $key => $value) {
            $docs[] = [
                'type' => $key,
                'url' => asset('uploads') . DIRECTORY_SEPARATOR . $value,
            ];
        }
        if (!$driver->city) {
            $user->city_name = 'Москва';
        } else {
            $user->city_name = $driver->city->name;
        }


        $user->docs = $docs;

        unset($user->id, $user->code, $user->role_id, $user->avatar, $user->email_verified_at, $user->settings, $user->created_at, $user->updated_at);

        return response()->json([$user]);
    }
    /**
     * Аутентификация пользователя или регистрация нового
     *
     * @OA\Post(
     *     path="/user/login",
     *     operationId="loginOrRegister",
     *     summary="Аутентификация пользователя или регистрация нового",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="phone", type="string", example="1234567890", description="Номер телефона пользователя"),
     *             @OA\Property(property="code", type="integer", example=1234, description="Код аутентификации")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешная аутентификация или регистрация",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string", description="Токен аутентификации")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object", description="Список ошибок валидации")
     *         )
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function loginOrRegister(Request $request)
    {
        $name = "DocumentsNotUploaded";
        $typeValue = UserStatus::{$name}->value;
        $value = 0;
        $typeName = UserStatus::from($value)->name;

        echo $typeName;
        echo $typeValue;
        dd();
        $request->validate([
            'phone' => 'required|string',
            'code' => 'required|integer',
        ]);
        $user = $this->phoneCodeAuthentication($request->phone, $request->code);
        if ($user) {
            if ($user->user_status === null) {
                $user->user_status = UserStatus::DocumentsNotUploaded->value;
                $user->avatar = "users/default.png";
                $user->user_type = UserType::Driver->value;
                $user->code = null;
                $user->save();
            }
            $driver = Driver::firstOrCreate(['user_id' => $user->id]);
            $driverSpecification = DriverSpecification::firstOrCreate(['driver_id' => $driver->id]);
            $driverDocs = DriverDoc::firstOrCreate(['driver_id' => $driver->id]);
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json(['access_token' => $token]);
        }
        return response()->json(null, 401);
    }
    /**
     * Выход пользователя из системы
     *
     * @OA\Post(
     *     path="/user/logout",
     *     operationId="logout",
     *     summary="Выход пользователя из системы",
     *     tags={"Authentication"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Пользователь успешно вышел из системы",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Пользователь успешно вышел из системы")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Ошибка аутентификации",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Недопустимый токен аутентификации")
     *         )
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'User logged out successfully']);
    }
    /**
     * Создание и отправка проверочного кода на указанный номер телефона
     *
     * @OA\Post(
     *     path="/user/code",
     *     operationId="CreateAndSendCode",
     *     summary="Создание и отправка проверочного кода на указанный номер телефона",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="phone", type="string", example="1234567890", description="Номер телефона пользователя")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Запрос успешно выполнен",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true, description="Успешность операции")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object", description="Список ошибок валидации")
     *         )
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function CreateAndSendCode(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'string', 'max:255'],
        ]);
        $phone = $request->phone;
        $user = User::firstOrCreate(['phone' => $phone]);
        $code = rand(10000000, 99999999);
        $user->code = $code;
        if (!$user->phone) {
            $user->phone = $phone;
        }
        $user->save();
        $response = Http::get('https://sms.ru/sms/send', [
            'api_id' => 'AFA267B8-9272-4CEB-CE8B-7EE807275EA9',
            'to' => $phone,
            'msg' => 'Проверочный код: ' . $code,
            'json' => 1
        ]);
        if ($response->successful()) {
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false]);
        }
    }

    private function phoneCodeAuthentication($phone, $code)
    {
        if ($code) {
            $user = User::where('phone', $phone)->where('code', $code)->first();
            if ($user) {
                Auth::login($user);
                return $user;
            }
        }
        return null;
    }

    /**
     * Удаление пользователя и связанных записей
     *
     * @OA\Delete(
     *     path="/user",
     *     operationId="DeleteUser",
     *     summary="Удаление пользователя и связанных записей",
     *     tags={"Authentication"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Пользователь успешно удален",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Пользователь успешно удален")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Ошибка аутентификации",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Недопустимый токен аутентификации")
     *         )
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function DeleteUser(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        $driver = Driver::where('user_id', $user->id)->first();

        // Удаление папки с фотографиями пользователя
        $folderPath = 'uploads/user/' . $user->id;
        if (Storage::exists($folderPath)) {
            Storage::deleteDirectory($folderPath);
        }

        // Удаление записей о пользователе и водителе
        $user->delete();
        if ($driver) {
            $driver->delete();
        }

        return response()->json(['message' => 'Пользователь успешно удален']);
    }
}
