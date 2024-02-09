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
     *                 @OA\Property(property="id", type="integer", description="ID пользователя"),
     *                 @OA\Property(property="code", type="integer", description="Код пользователя"),
     *                 @OA\Property(property="role_id", type="integer", description="ID роли пользователя"),
     *                 @OA\Property(property="user_status", type="integer", description="Статус пользователя"),
     *                 @OA\Property(property="phone", type="string", description="Номер телефона пользователя"),
     *                 @OA\Property(property="name", type="string", nullable=true, description="Имя пользователя"),
     *                 @OA\Property(property="email", type="string", nullable=true, description="Email пользователя"),
     *                 @OA\Property(property="avatar", type="string", description="Аватар пользователя"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", description="Дата и время создания пользователя"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", description="Дата и время последнего обновления пользователя"),
     *                 @OA\Property(property="user_type", type="integer", description="Тип пользователя")
     *             ),
     *             @OA\Property(
     *                 property="driver",
     *                 type="object",
     *                 description="Данные водителя",
     *                 @OA\Property(property="id", type="integer", description="ID водителя"),
     *                 @OA\Property(property="user_id", type="integer", description="ID пользователя"),
     *                 @OA\Property(property="city_id", type="integer", nullable=true, description="ID города"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", description="Дата и время создания водителя"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", description="Дата и время последнего обновления водителя")
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
        $user = Auth::user();
        $driver = Driver::where('user_id', $user->id)->first();
        return response()->json(['user' => $user, 'driver' => $driver]);
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

        $request->validate([
            'phone' => 'required|string',
            'code' => 'required|integer',
        ]);

        if ($this->phoneCodeAuthentication($request->phone, $request->code)) {
            $user = Auth::user();
            if ($user->user_status === null) {
                $user->user_status = 0;
                $user->avatar = "users/default.png";
                $user->user_type = 1;
                $user->save();
            }
            $driver = Driver::firstOrCreate(['user_id' => $user->id]);
            $driverSpecification = DriverSpecification::firstOrCreate(['driver_id' => $driver->id]);
            $driverDocs = DriverDoc::firstOrCreate(['driver_id' => $driver->id]);
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json(['access_token' => $token]);
        }
        return response()->json(['error' => 'Телефон или код не совпадают']);
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
        $user = User::where('phone', $phone)->first();
        if ($user && $user->code === $code) {
            Auth::login($user);
            return true;
        }
        return false;
    }
}
