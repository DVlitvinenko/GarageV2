<?php

namespace App\Http\Controllers\Auth;

use App\Enums\BookingStatus;
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
use App\Models\Division;
use App\Models\RentTerm;
use Session;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Enums\CarClass;
use Illuminate\Support\Carbon;

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
     * @OA\Response(
     *     response=200,
     *     description="Успешная аутентификация или регистрация",
     *     @OA\JsonContent(
     *         @OA\Property(
     *             property="user",
     *             type="object",
     *             description="Данные пользователя",
     *             @OA\Property(property="user_status", type="string", description="Статус пользователя"),
     *             @OA\Property(property="phone", type="string", description="Номер телефона пользователя"),
     *             @OA\Property(property="name", type="string", nullable=true, description="Имя пользователя"),
     *             @OA\Property(property="email", type="string", nullable=true, description="Email пользователя"),
     *             @OA\Property(property="user_type", type="string", description="Тип пользователя"),
     *             @OA\Property(property="city_name", type="string", description="Название города"),
     *             @OA\Property(
     *                 property="docs",
     *                 type="array",
     *                 description="Данные документов водителя",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="type", type="string", description="Тип документа"),
     *                     @OA\Property(property="url", type="string", nullable=true, description="URL документа")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="bookings",
     *                 type="array",
     *                 description="Список бронирований",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", description="Идентификатор бронирования"),
     *                     @OA\Property(property="status", type="string", description="Статус бронирования"),
     *                     @OA\Property(property="car_id", type="integer", description="Идентификатор автомобиля"),
     *                     @OA\Property(property="start_date", type="string", description="Дата начала бронирования в формате 'd.m.Y H:i'"),
     *                     @OA\Property(property="end_date", type="string", description="Дата окончания бронирования в формате 'd.m.Y H:i'"),
     *                     @OA\Property(property="car_brand", type="string", description="Марка автомобиля"),
     *                     @OA\Property(property="car_model", type="string", description="Модель автомобиля"),
     *                     @OA\Property(property="car_images", type="array", @OA\Items(type="string"), description="Ссылки на изображения"),
     *                     @OA\Property(
     *                         property="rent_term",
     *                         type="object",
     *                         description="Условия аренды",
     *                         @OA\Property(property="deposit_amount_daily", type="string"),
     *                         @OA\Property(property="deposit_amount_total", type="string"),
     *                         @OA\Property(property="minimum_period_days", type="integer"),
     *                         @OA\Property(property="is_buyout_possible", type="integer"),
     *                         @OA\Property(
     *                             property="schemas",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="daily_amount", type="integer"),
     *                                 @OA\Property(property="non_working_days", type="integer"),
     *                                 @OA\Property(property="working_days", type="integer")
     *                             )
     *                         )
     *                     ),
     *                     @OA\Property(
     *                         property="car",
     *                         type="object",
     *                         description="Информация об автомобиле",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="fuel_type", type="string", description="Тип топлива",ref="#/components/schemas/FuelType"),
     *                         @OA\Property(property="transmission_type", type="string", description="Тип трансмиссии",ref="#/components/schemas/TransmissionType"),
     *                         @OA\Property(property="сar_class", type="string", description="Класс тарифа", ref="#/components/schemas/CarClass"),
     *                         @OA\Property(property="brand", type="string"),
     *                         @OA\Property(property="model", type="string"),
     *                         @OA\Property(property="year_produced", type="integer"),
     *                         @OA\Property(property="images", type="string"),
     *                         @OA\Property(
     *                             property="division",
     *                             type="object",
     *                             description="Информация о дивизионе",
     *                             @OA\Property(property="address", type="string"),
     *                             @OA\Property(property="coords", type="string"),
     *                             @OA\Property(
     *                                 property="park",
     *                                 type="object",
     *                                 description="Информация о парке",
     *                                 @OA\Property(property="phone", type="string"),
     *                                 @OA\Property(property="url", type="string"),
     *                                 @OA\Property(property="commission", type="integer"),
     *                                 @OA\Property(property="self_employed", type="integer"),
     *                                 @OA\Property(property="park_name", type="string"),
     *                                 @OA\Property(property="about", type="string"),
     *                 @OA\Property(
     *                     property="working_hours",
     *                     type="array",
     *                     description="Расписание работы парка",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="start", type="string", description="Время начала"),
     *                         @OA\Property(property="end", type="string", description="Время окончания"),
     *                         @OA\Property(property="day", type="string", description="День недели на русском")
     *                     )
     *                 )
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * ),
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
                'url' => $value  ? asset('uploads') . '/' . $value : null,
            ];
        }
        if (!$driver->city) {
            $user->city_name = 'Москва';
        } else {
            $user->city_name = $driver->city->name;
        }
        $bookings =  $driver->booking;
        if ($bookings) {
            foreach ($bookings as $booking) {
                $booking->status = BookingStatus::from($booking->status)->name;
                $booking->start_date = Carbon::parse($booking->booking_at)->format('d.m.Y H:i');
                $booking->end_date = Carbon::parse($booking->booking_until)->format('d.m.Y H:i');
                $booking->car->сar_class = CarClass::from($booking->car->tariff->class)->name;
                $booking->car->division = Division::where('id', $booking->car->division_id)->with('park')->select('address', 'park_id', 'coords')->first();
                $booking->rent_term = RentTerm::where('id', $booking->car->rent_term_id)->with('schemas')->select('deposit_amount_daily', 'deposit_amount_total', 'minimum_period_days', 'is_buyout_possible', 'id')->first();
                $workingHours = json_decode($booking->car->division->park->working_hours, true);

                $daysOfWeek = [
                    'понедельник' => 'monday',
                    'вторник' => 'tuesday',
                    'среда' => 'wednesday',
                    'четверг' => 'thursday',
                    'пятница' => 'friday',
                    'суббота' => 'saturday',
                    'воскресенье' => 'sunday'
                ];

                $hoursToArray = [];

                foreach ($daysOfWeek as $rusDay => $engDay) {
                    if (isset($workingHours[$engDay])) {
                        foreach ($workingHours[$engDay] as $timeRange) {
                            $hoursToArray[] = [
                                'start' => $timeRange['start'],
                                'end' => $timeRange['end'],
                                'day' => $rusDay
                            ];
                        }
                    }
                }
                $booking->car->division->park->working_hours = $hoursToArray;
                unset(
                    $booking->created_at,
                    $booking->updated_at,
                    $booking->booking_at,
                    $booking->booking_until,
                    $booking->park_id,
                    $booking->driver_id,
                    $booking->car->booking,
                    $booking->car->created_at,
                    $booking->car->updated_at,
                    $booking->car->status,
                    $booking->car->car_id,
                    $booking->car->division_id,
                    $booking->car->park_id,
                    $booking->car->tariff_id,
                    $booking->car->tariff,
                    $booking->car->rent_term_id,
                    $booking->car->division->id,
                    $booking->car->division->park_id,
                    $booking->car->division->city_id,
                    $booking->car->division->created_at,
                    $booking->car->division->updated_at,
                    $booking->car->division->name,
                    $booking->car->division->park->id,
                    $booking->car->division->park->id,
                    $booking->car->division->park->API_key,
                    $booking->car->division->park->created_at,
                    $booking->car->division->park->updated_at,
                    $booking->car->division->park->created_at,
                    $booking->rent_term->id
                );
                foreach ($booking->rent_term->schemas as $schema) {
                    unset($schema->created_at, $schema->updated_at, $schema->id, $schema->rent_term_id);
                }
            }
        }


        $user->docs = $docs;
        $user->bookings = $bookings ? $bookings : null;
        unset($user->id, $user->code, $user->role_id, $user->avatar, $user->email_verified_at, $user->settings, $user->created_at, $user->updated_at);
        return response()->json(['user' => $user]);
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
     *     @OA\MediaType(
     *         mediaType="text/plain",
     *         @OA\Schema(
     *             type="string",
     *             example="Success"
     *         ))
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
        $user = $this->phoneCodeAuthentication($request->phone, $request->code);
        if ($user) {
            if ($user->user_status === null) {
                $user->user_status = UserStatus::DocumentsNotUploaded->value;
                $user->avatar = "users/default.png";
                $user->user_type = UserType::Driver->value;
            }
            $user->code = null;
            $user->save();
            $driver = Driver::firstOrCreate(['user_id' => $user->id]);
            $driverSpecification = DriverSpecification::firstOrCreate(['driver_id' => $driver->id]);
            $driverDocs = DriverDoc::firstOrCreate(['driver_id' => $driver->id]);
            $token = $user->createToken('auth_token')->plainTextToken;
            return $token;
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
