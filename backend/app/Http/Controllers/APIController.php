<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Park;
use App\Models\Division;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Models\Car;
use App\Models\City;
use App\Models\Tariff;
use App\Models\RentTerm;
use App\Models\Booking;
use App\Enums\BookingStatus;
use App\Enums\SuitEnum;
use App\Enums\CarStatus;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
use App\Http\Controllers\Enums;
use Carbon\Carbon;
use App\Http\Controllers\ParserController;
use App\Models\Schema;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

/**
 * @OA\Info(
 *      title="API Мой гараж",
 *      version="1.0.2",
 *      description="Это список методов для интеграции с Мой гараж, в заголовках всех запросов должен присутствоваться API-ключ, который будет предоставлен администратором на почку клиента после регистрации клиента в сервисе администратором",
 * )
 */


class APIController extends Controller
{


    /**
     * Добавить несколько автомобилей
     *
     * @OA\Post(
     *     path="/cars",
     *     operationId="pushCars",
     *     summary="Добавить несколько автомобилей, все добавленные автомобили будут доступны к бронированию сразу после привязки к ним Условий бронирования (метод: /cars/rent-term)",
     *     tags={"API"},
     *     security={{"api_key": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="cars",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", maxLength=17, description="VIN-номер автомобиля"),
     *                     @OA\Property(property="division_id", type="integer", maxLength=250, description="id подразделения"),
     *                     @OA\Property(property="fuel_type", type="integer", description="Вид топлива (1 - газ, 0 - бензин)"),
     *                     @OA\Property(property="transmission_type", type="integer", description="КПП ТС (1 - автомат, 0 - механика)"),
     *                     @OA\Property(property="brand", type="string", maxLength=50, description="Бренд автомобиля"),
     *                     @OA\Property(property="model", type="string", maxLength=80, description="Модель автомобиля"),
     *                     @OA\Property(property="class", type="integer", description="Тариф автомобиля (1 - эконом, 2 - комфорт, 3 - комфорт+, 4 - бизнес)"),
     *                     @OA\Property(property="year_produced", type="integer", description="Год выпуска автомобиля"),
     *                     @OA\Property(property="images", type="array", @OA\Items(type="string"), description="Ссылки на фотографии автомобиля"),
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешное добавление автомобилей",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Автомобили успешно добавлены")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Ошибка аутентификации",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ошибка аутентификации")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Ошибка сервера",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ошибка сервера")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Ошибки валидации",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Неверные или недостающие параметры в запросе"),
     *             @OA\Property(property="errors", type="object", nullable=true, description="Список ошибок валидации")
     *         )
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request Объект запроса, содержащий информацию о добавляемых автомобилях
     * @return \Illuminate\Http\JsonResponse JSON-ответ с результатом добавления автомобилей
     */


    public function pushCars(Request $request)
    {
        $apiKey = $request->header('X-API-Key');

        $park = Park::where('API_key', $apiKey)->first();
        if (!$park) {
            return response()->json(['message' => 'Неверный ключ авторизации'], 401);
        }

        $validator = Validator::make($request->all(), [
            'cars' => 'required|array',
            'cars.*.division_id' => 'required|integer',
            'cars.*.fuel_type' => 'required|integer|max:1',
            'cars.*.transmission_type' => 'required|integer|max:1',
            'cars.*.brand' => [
                'required',
                'string',
                'max:50',
                function ($attribute, $value, $fail) {
                    $parser = new ParserController();
                    if (!$parser->parseBrand($value)) {
                        $fail('Некорректный бренд.');
                    }
                },
            ],
            'cars.*.model' => [
                'required',
                'string',
                'max:80',
                function ($attribute, $value, $fail) {
                    $parser = new ParserController();
                    if (!$parser->parseModel($value)) {
                        $fail('Некорректная модель.');
                    }
                },
            ],
            'cars.*.class' => 'required|integer|between:0,4',
            'cars.*.year_produced' => 'nullable|integer',
            'cars.*.id' => 'required|string|max:20|unique:cars,car_id',
            'cars.*.images' => 'required|array',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        $cars = $request->input('cars');
        foreach ($cars as $index => $carData) {
            $division = Division::where('id',$request->division_id);
            $car = new Car;
            $car->division_id = $division->id;
            $car->fuel_type = $carData['fuel_type'];
            $car->transmission_type = $carData['transmission_type'];
            $car->brand = $carData['brand'];
            $car->model = $carData['model'];
            $car->tariff_id = $this->GetTariffId($park->id, $division->city_id, $carData['class']);
            $car->year_produced = $carData['year_produced'];
            $car->car_id = $carData['id'];
            $car->images = json_encode($carData['images']);
            $car->status = 1;
            $car->park_id = $park->id;
            $car->save();
        }
        return response()->json(['message' => 'Автомобили успешно добавлены'], 200);
    }


    /**
     * Обновление информации о машине
     *
     * @OA\Put(
     *     path="/cars",
     *     operationId="updateCar",
     *     summary="Обновление информации о машине",
     *     tags={"API"},
     *     security={{"api_key": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", maxLength=20, description="VIN-номер машины"),
     *             @OA\Property(property="city", type="string", maxLength=50, nullable=true, description="Город машины"),
     *             @OA\Property(property="division_name", type="string", maxLength=250, nullable=true, description="Подразделение машины"),
     *             @OA\Property(property="class", type="integer", nullable=true, description="Тариф машины (1 - эконом, 2 - комфорт, 3 - комфорт+, 4 - бизнес)"),
     *             @OA\Property(property="images", type="array", @OA\Items(type="string"), nullable=true, description="Изображения машины"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешное обновление информации о машине",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Машина успешно обновлена")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Ошибка аутентификации",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ошибка аутентификации")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Ошибка сервера",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ошибка сервера")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Ошибки валидации",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object", example={
     *                 "id": {"Поле id обязательно для заполнения."},
     *                 "city": {"Поле city должно быть строкой."}
     *             })
     *         )
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request Объект запроса, содержащий информацию об обновляемой машине
     * @return \Illuminate\Http\JsonResponse JSON-ответ с результатом обновления информации о машине
     */

    public function updateCar(Request $request)
    {
        $apiKey = $request->header('X-API-Key');
        // Проверка ключа авторизации
        $park = Park::where('API_key', $apiKey)->first();
        if (!$park) {
            return response()->json(['message' => 'Неверный ключ авторизации'], 401);
        }
        $validator = Validator::make($request->all(), [
            'id' => 'required|string|max:20',
            'city' => ['nullable', 'string', 'max:50', 'exists:cities,name'],
            'division_name' => 'nullable|string|max:250',
            'fuel_type' => 'nullable|integer|max:1',
            'transmission_type' => 'nullable|integer|max:1',
            'brand' => [
                'nullable',
                'string',
                'max:50',
                function ($attribute, $value, $fail) {
                    $parser = new ParserController();
                    if (!$parser->parseBrand($value)) {
                        $fail('Некорректный бренд.');
                    }
                },
            ],
            'model' => [
                'nullable',
                'string',
                'max:80',
                function ($attribute, $value, $fail) {
                    $parser = new ParserController();
                    if (!$parser->parseModel($value)) {
                        $fail('Некорректная модель.');
                    }
                },
            ],
            'class' => 'nullable|integer|between:0,4',
            'year_produced' => 'nullable|integer',
            'description' => 'nullable|string|max:500',
            'images' => 'nullable|array',
            'images.*' => 'string',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $carId = $request->input('id');
        $cityName = $request->input('city');
        $divisionName = trim($request->input('division_name'));

        $park = Park::where('API_key', $apiKey)->first();
        if (!$park) {
            return response()->json(['message' => 'Неверный ключ авторизации'], 401);
        }
        $city = City::firstOrCreate(['name' => $cityName]);

        $division = $this->divisionCheck($divisionName, $park->id, $city->id);
        $car = Car::where('car_id', $carId)
            ->where('park_id', $park->id)
            ->first();
        if (!$car) {
            return response()->json(['message' => 'Автомобиль не найден'], 404);
        }
        $car->tariff_id = $this->GetTariffId($park->id, $city->id, $request->input('class'));
        $car->division_id = $division->id;
        $car->park_id = $park->id;
        $car->fuel_type = $request->input('fuel_type');
        $car->transmission_type = $request->input('transmission_type');
        $car->brand = $request->input('brand');
        $car->model = $request->input('model');
        $car->year_produced = $request->input('year_produced');
        $car->images = json_encode($request->input('images'));
        $car->save();

        return response()->json(['message' => 'Автомобиль успешно изменен'], 200);
    }

    /**
     * Обновление статуса допуска к бронированию автомобиля
     *
     * @OA\Put(
     *     path="/cars/status",
     *     operationId="updateCarStatus",
     *     summary="Обновление статуса допуска к бронированию",
     *     tags={"API"},
     *     security={{"api_key": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", maxLength=20, description="VIN-номер автомобиля"),
     *             @OA\Property(property="status", type="integer", description="Допуск автомобиля к бронированию. 1 - допущен, 0 - заблокирован")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешное обновление статуса автомобиля",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Статус автомобиля успешно обновлен")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Ошибка аутентификации",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ошибка аутентификации")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Ошибка сервера",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ошибка сервера")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Ошибки валидации",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object", example={
     *                 "id": {"Поле id обязательно для заполнения."},
     *                 "status": {"Поле status должно быть числом."}
     *             })
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Конфликт",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Автомобиль сейчас забронирован, изменение статуса невозможно")
     *         )
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request Объект запроса, содержащий информацию об обновляемом статусе автомобиля
     * @return \Illuminate\Http\JsonResponse JSON-ответ с результатом обновления статуса автомобиля
     */

    public function updateCarStatus(Request $request)
    {
        $apiKey = $request->header('X-API-Key');
        $park = Park::where('API_key', $apiKey)->first();
        if (!$park) {
            return response()->json(['message' => 'Неверный ключ авторизации'], 401);
        }
        $validator = Validator::make($request->all(), [
            'id' => 'required|string|max:20',
            'status' => 'integer|max:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        $carId = $request->input('id');
        $car = Car::where('car_id', $carId)
            ->where('park_id', $park->id)
            ->first();
        if (!$car) {
            return response()->json(['message' => 'Автомобиль не найден'], 404);
        }
        $booking = $car->booking_id;
        if ($booking === null || $booking === 'block') {
            $car->status = $request->input('status');
            $car->save();
        } else {
            return response()->json(['message' => 'Авто сейчас забронировано, изменить статус невозможно'], 409);
        }

        return response()->json(['message' => 'Автомобиль успешно изменен'], 200);
    }



    /**
     * Изменить статус бронирования автомобиля
     *
     * Этот метод используется для передачи данных ОТ МОЕГО ГАРАЖА.
     *
     * @OA\Put(
     *     path="/URL_АДРЕС_ПАРКА/cars/outbound/status",
     *     summary="Изменить статус бронирования автомобиля, ОТ МОЕГО ГАРАЖА",
     *     tags={"API"},
     *     operationId="notifyParkOnBookingStatusChanged",
     *     security={{"api_key": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", description="VIN-номер автомобиля"),
     *             @OA\Property(property="is_booked", type="integer", description="Статус бронирования. 1 - забронировано, 0 - бронь отменена"),
     *             @OA\Property(property="driver_name", type="string", description="ФИО водителя"),
     *             @OA\Property(property="phone", type="string", description="Телефон водителя")
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Успешное изменение статуса бронирования"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Некорректный запрос"
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request Объект запроса, содержащий информацию об изменении статуса бронирования автомобиля
     * @return \Illuminate\Http\JsonResponse JSON-ответ с результатом изменения статуса бронирования
     */

    public function notifyParkOnBookingStatusChanged($booking_id, $is_booked)
    {
        $booking = Booking::where('id', $booking_id)->first();
        $car = $booking->car;
        $user = $booking->driver->user;
        $park = $car->division->park;
        $apiKey = $park->API_key;
        $url = $park->url;

        // if ($url !== null) {
        //     $client = new Client();
        //     $response = $client->put($url, [
        //         'headers' => [
        //             'X-API-Key' => $apiKey,
        //         ],
        //         'json' => [
        //             'is_booked' => $is_booked,
        //             'car_id' =>  $car->car_id,
        //             'driver_name' => $user->name,
        //             'phone' => $user->phone,
        //         ],
        //         'http_errors' => false,
        //     ]);
        //     $statusCode = $response->getStatusCode();
        //     if ($statusCode === 204) {
        //     } else {
        //     }
        // }
    }

    /**
     * Создание или обновление условий аренды
     *
     * Этот метод позволяет создавать новые или обновлять существующие условия аренды для парков.
     *
     * @OA\Post(
     *     path="/parks/rent-terms",
     *     operationId="createOrUpdateRentTerm",
     *     summary="Создание или обновление условий аренды",
     *     tags={"API"},
     *     security={{"api_key": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="rent_term_id", type="integer", nullable=true, description="Идентификатор существующего условия аренды (для обновления)"),
     *             @OA\Property(property="deposit_amount_daily", type="number", description="Сумма ежедневного залога"),
     *             @OA\Property(property="deposit_amount_total", type="number", description="Общая сумма залога"),
     *             @OA\Property(property="is_buyout_possible", type="boolean", description="Возможность выкупа (true/false)"),
     *             @OA\Property(property="minimum_period_days", type="integer", description="Минимальный период аренды в днях"),
     *             @OA\Property(property="name", type="string", description="Название условия аренды"),
     *             @OA\Property(
     *                 property="schemas",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="daily_amount", type="number", format="float", description="Стоимость аренды авто"),
     *                     @OA\Property(property="non_working_days", type="integer", description="Количество нерабочих дней"),
     *                     @OA\Property(property="working_days", type="integer", description="Количество рабочих дней")
     *                 ),
     *                 description="Схемы аренды"
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешное создание или обновление условий аренды",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Условие аренды успешно создано или изменено"),
     *             @OA\Property(property="id", type="integer", example="Идентификатор условия аренды")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Ошибка аутентификации",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ошибка аутентификации")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибки валидации",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object", example={
     *                 "deposit_amount_daily": {"Поле deposit_amount_daily обязательно для заполнения и должно быть числом."},
     *                 "deposit_amount_total": {"Поле deposit_amount_total обязательно для заполнения и должно быть числом."},
     *                 "is_buyout_possible": {"Поле is_buyout_possible обязательно для заполнения и должно быть булевым значением."},
     *                 "minimum_period_days": {"Поле minimum_period_days обязательно для заполнения и должно быть целым числом."},
     *                 "name": {"Поле name обязательно для заполнения и должно быть строкой."},
     *                 "schemas": {"Поле schemas обязательно для заполнения и должно быть массивом строк."},
     *             })
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Парк с указанным API ключом не найден",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Парк не найден")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Ошибка сервера",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ошибка сервера")
     *         )
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request Объект запроса с данными для создания или обновления условий аренды
     * @return \Illuminate\Http\JsonResponse JSON-ответ с результатом операции
     */


    public function createOrUpdateRentTerm(Request $request)
    {
        $apiKey = $request->header('X-API-Key');
        $park = Park::where('API_key', $apiKey)->firstOrFail();

        $validator = $this->validateRentTerm($request);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        if ($validator->fails()) {
            return response()->json(['message' => 'Ошибка валидации', 'errors' => $validator->errors()], 400);
        }
        $rentTermId = $request->input('rent_term_id');
        $data = [
            'park_id' => $park->id,
            'deposit_amount_daily' => $request->input('deposit_amount_daily'),
            'deposit_amount_total' => $request->input('deposit_amount_total'),
            'minimum_period_days' => $request->input('minimum_period_days'),
            'name' => $request->input('name'),
            'is_buyout_possible' => $request->input('is_buyout_possible'),
        ];

        if ($rentTermId) {
            $rentTerm = RentTerm::where('id', $rentTermId)
                ->where('park_id', $park->id)
                ->first();
            if ($rentTerm) {
                $rentTerm->update($data);
                return response()->json(['message' => 'Условие аренды успешно изменено'], 200);
            }
        }

        $rentTerm = new RentTerm($data);
        $rentTerm->save();
        return response()->json([
            'message' => 'Условие аренды успешно создано.',
            'id' => $rentTerm->id
        ], 200);
    }

    private function validateRentTerm(Request $request)
    {
        $rules = [
            'deposit_amount_daily' => 'required|numeric',
            'deposit_amount_total' => 'required|numeric',
            'minimum_period_days' => 'required|integer',
            'name' => 'required|string',
            'is_buyout_possible' => 'required|boolean',
            'rent_term_id' => 'nullable|integer',
        ];

        $messages = [
            'required' => 'Поле :attribute обязательно для заполнения.',
            'numeric' => 'Поле :attribute должно быть числовым.',
            'integer' => 'Поле :attribute должно быть целым числом.',
            'string' => 'Поле :attribute должно быть строкой.',
            'boolean' => 'Поле :attribute должно быть булевым значением.',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }

    /**
     * Обновление условия аренды для автомобиля
     *
     * Этот метод позволяет обновлять условие аренды для конкретного автомобиля по его VIN-номеру.
     *
     * @OA\Put(
     *     path="/cars/rent-term",
     *     operationId="updateCarRentTerm",
     *     summary="Обновление условия аренды для автомобиля",
     *     tags={"API"},
     *     security={{"api_key": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", description="VIN-номер автомобиля"),
     *             @OA\Property(property="rent_term_id", type="integer", description="Идентификатор условия аренды")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешное обновление условия аренды для автомобиля",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Условие аренды успешно обновлено для автомобиля")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Ошибка аутентификации",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ошибка аутентификации")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Ошибки валидации",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object", example={
     *                 "rent_term_id": {"Поле rent_term_id обязательно для заполнения и должно быть целым числом."},
     *                 "id": {"Поле id обязательно для заполнения и должно быть целым числом."}
     *             })
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Условие аренды или автомобиль не найдены",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Условие аренды или автомобиль не найдены")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Ошибка сервера",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ошибка сервера")
     *         )
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request Объект запроса с данными для обновления условия аренды для автомобиля
     * @return \Illuminate\Http\JsonResponse JSON-ответ с результатом операции
     */

    public function updateCarRentTerm(Request $request)
    {
        $apiKey = $request->header('X-API-Key');
        $park = Park::where('API_key', $apiKey)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'rent_term_id' => 'required|integer',
            'id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $rentTermId = $request->input('rent_term_id');
        $carId = $request->input('id');
        $rentTerm = RentTerm::where('id', $rentTermId)
            ->where('park_id', $park->id)
            ->first();
        $schema = Schema::where('rent_term_id', $rentTerm->id)->orderBy('daily_amount', 'asc')->select('daily_amount')->first();
        if (!$rentTerm) {
            return response()->json(['message' => 'Условие аренды не найдено'], 404);
        }
        $car = Car::where('car_id', $carId)
            ->where('park_id', $park->id)
            ->first();

        if (!$car) {
            return response()->json(['message' => 'Автомобиль не найден'], 404);
        }

        $car->rent_term_id = $rentTermId;
        $car->price = $schema->daily_amount;
        $car->save();

        return response()->json(['message' => 'Условие аренды успешно привязано к автомобилю'], 200);
    }
    private function GetTariffId($park_id, $city_id, $classNum)
    {
        $class = '';
        $tariffId = Tariff::where('class', $class)
            ->where('park_id', $park_id)
            ->where('city_id', $city_id)
            ->value('id');
        if (!$tariffId) {
            $newTariff = Tariff::create([
                'class' => $classNum,
                'park_id' => $park_id,
                'city_id' => $city_id,
            ]);
            $tariffId = $newTariff->id;
        }
        return $tariffId;
    }

    private function divisionCheck($divisionName, $park_id, $city_id)
    {
        $division = Division::where('park_id', $park_id)->where('city_id', $city_id)->where('name', $divisionName)->first();
        if (!$division) {
            $division = Division::create([
                'park_id' => $park_id,
                'city_id' => $city_id,
            ]);
            $division->name = $divisionName;
            $division->save();
        }
        return  $division;
    }

    /**
     * Обновление условия аренды для автомобиля
     *
     * Этот метод позволяет обновлять статус брони для конкретного автомобиля по его VIN-номеру.
     *
     * @OA\Put(
     *     path="/cars/booking",
     *     operationId="updateCarBookingStatus",
     *     summary="Обновление статуса брони автомобиля",
     *     tags={"API"},
     *     security={{"api_key": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", description="VIN-номер автомобиля"),
     *             @OA\Property(property="status", type="string", description="Статус бронирования: Booked - авто забронировано, UnBooked - бронь снята и авто может быть доступно к бронированию, RentStart - автомобиль выдан водителю в аренду, RentOver - аренда авто закончена и авто может быть доступно к бронированию", ref="#/components/schemas/BookingStatus"),
     *             @OA\Property(property="driver_name", type="string", description="ФИО водителя"),
     *             @OA\Property(property="phone", type="string", description="Телефон водителя")
     * )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешное обновление статуса брони автомобиля",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="статуса брони автомобиля")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Ошибка аутентификации",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ошибка аутентификации")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Ошибки валидации",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object", example={
     *                 "status": {"Поле status обязательно для заполнения и должно быть строкой."},
     *                 "id": {"Поле id обязательно для заполнения и должно быть строкой."}
     *             })
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Автомобиль не найден",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Автомобиль не найден или бронирование не найдено")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Ошибка сервера",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ошибка сервера")
     *         )
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request Объект запроса с данными для обновления условия аренды для автомобиля
     * @return \Illuminate\Http\JsonResponse JSON-ответ с результатом операции
     */
    public function updateCarBookingStatus(Request $request)
    {
        $apiKey = $request->header('X-API-Key');
        $park = Park::where('API_key', $apiKey)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'status' => 'required|string',
            'id' => 'required|string',
            'driver_name' => 'required|string',
            'phone' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Ошибка валидации', 'errors' => $validator->errors()], 400);
        }
        $status = BookingStatus::{$request->input('status')}->value;
        $carId = $request->input('id');
        $driverName = $request->input('driver_name');
        $phone = $request->input('phone');

        $car = Car::where('car_id', $carId)
                ->where('park_id', $park->id)
                ->with('booking')
                ->first();

        $user = User::where('phone', $phone)->where('name', $driverName)->first();

        if (!$car) {
            return response()->json([
                'message' => 'Автомобиль не найден',
            ], 404);
        }
        if ($status == BookingStatus::Booked->value) {
        $rent_time = 3;
        $car = Car::where('car_id', $request->id)
        ->with('booking')
        ->first();
        if (!$car) {
            return response()->json(['message' => 'Машина не найдена'], 404);
        }
        if ($car->status!==CarStatus::AvailableForBooking->value) {
            return response()->json(['message' => 'Машина уже забронирована'], 409);
        }
        $division = Division::where('id', $car->division_id)->with('park')->first();
        $driver = Driver::where('user_id', $user->id)->first();
        $workingHours = json_decode($division->park->working_hours, true);
        $currentDayOfWeek = Carbon::now()->format('l');

        $currentTime = Carbon::now()->timestamp;

        $endTimeOfWorkDayToday = Carbon::createFromFormat('H:i', $workingHours[strtolower($currentDayOfWeek)][0]['end'], $division->park->timezone)->timestamp;
        $endTimeOfWorkDayToday -= $rent_time * 3600;

     if ($endTimeOfWorkDayToday < $currentTime) {
        $nextWorkingDay = Carbon::now()->addDay()->format('l');
        $startTimeOfWorkDayTomorrow = Carbon::createFromFormat('H:i', $workingHours[strtolower($nextWorkingDay)][0]['start'], $division->park->timezone)->timestamp;
        $newEndTime = $startTimeOfWorkDayTomorrow + $rent_time * 3600;
     } else {
        $remainingTime = $rent_time * 3600;
        $newEndTime = $currentTime + $remainingTime;
    }

        $booking = new Booking();
        $booking->car_id = $car->id;
        $booking->park_id = $division->park_id;
        $booking->booked_at = $currentTime;
        $booking->booked_until = $newEndTime;
        $booking->status = BookingStatus::Booked->value;
        $booking->driver_id = $driver->id;
        $booking->save();
        $car->status = CarStatus::Booked->value;
        $car->save();
        return response()->json($newEndTime, 200);
} elseif($status === BookingStatus::UnBooked->value) {
    $booking = $car->booking()
    ->where('status', BookingStatus::Booked)
    ->first();
    if (!$booking) {
        return response()->json([
            'message' => 'Бронирование не найдено для данного автомобиля',
        ], 404);
    }
    $booking->status = $status;
    $booking->save();
        $car->status = CarStatus::AvailableForBooking->value;
        $car->save();
    return response()->json(['message' => 'Статус бронирования успешно изменен, авто доступно для брони'], 200);
}
elseif($status === BookingStatus::RentOver->value) {
    $booking = $car->booking()
    ->Where('status', BookingStatus::RentStart)
    ->first();
    if (!$booking) {
        return response()->json([
            'message' => 'Аренда не найдена для данного автомобиля',
        ], 404);
    }
    $booking->status = $status;
    $booking->save();
        $car->status = CarStatus::AvailableForBooking->value;
        $car->save();
    return response()->json(['message' => 'Статус бронирования успешно изменен, аренда закончена'], 200);
}
else{ $booking = $car->booking->where('status', BookingStatus::Booked->value)->first();
    if (!$booking) {
        return response()->json([
            'message' => 'Бронирование не найдено для данного автомобиля',
        ], 404);
    }
    $booking->status = $status;
    $booking->save();
    return response()->json(['message' => 'Статус бронирования успешно изменен, аренда начата'], 200);}
    }

/**
 * Обновление информации о парке
 *
 * Этот метод позволяет обновлять информацию о парке.
 *
 * @OA\Put(
 *     path="/parks",
 *     operationId="updateParkInfo",
 *     summary="Обновление информации о парке",
 *     tags={"API"},
 *     security={{"api_key": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="url", type="string", description="URL парка"),
 *             @OA\Property(property="commission", type="number", description="Комиссия"),
 *             @OA\Property(property="self_employed", type="boolean", description="Работает ли парк с самозанятыми, true - если работает"),
 *             @OA\Property(property="park_name", type="string", description="Название парка"),
 *             @OA\Property(property="about", type="string", description="Описание парка"),
* @OA\Property(
 *     property="working_hours",
 *     type="object",
 *     description="Время работы",
 *     @OA\Property(property="monday", type="array", description="Время работы в понедельник"),
 *     @OA\Property(property="tuesday", type="array", description="Время работы во вторник"),
 *     @OA\Property(property="wednesday", type="array", description="Время работы в среду"),
 *     @OA\Property(property="thursday", type="array", description="Время работы в четверг"),
 *     @OA\Property(property="friday", type="array", description="Время работы в пятницу"),
 *     @OA\Property(property="saturday", type="array", description="Время работы в субботу"),
 *     @OA\Property(property="sunday", type="array", description="Время работы в воскресенье"),
 *     @OA\Items(
 *         type="object",
 *         @OA\Property(property="start", type="string", description="Время начала работы"),
 *         @OA\Property(property="end", type="string", description="Время окончания работы")
 *     )
 * ),
 *             @OA\Property(property="phone", type="string", description="Телефон парка")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Успешное обновление информации о парке",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Парк обновлен")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Ошибка аутентификации",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Неверный ключ авторизации")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Ошибки валидации",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Ошибка валидации"),
 *             @OA\Property(property="errors", type="object", example={
 *                 "url": {"Поле url должно быть строкой."},
 *                 "commission": {"Поле commission должно быть числом."},
 *                 "self_employed": {"Поле self_employed должно быть булевым значением."},
 *                 "park_name": {"Поле park_name должно быть строкой."},
 *                 "about": {"Поле about должно быть строкой."},
 *                 "working_hours": {"Поле working_hours должно быть в формате JSON."},
 *                 "phone": {"Поле phone должно быть строкой."},
 *             })
 *         )
 *     )
 * )
 *
 * @param \Illuminate\Http\Request $request Объект запроса с данными для обновления информации о парке
 * @return \Illuminate\Http\JsonResponse JSON-ответ с результатом операции
 */
    public function updateParkInfo(Request $request)
{
    $apiKey = $request->header('X-API-Key');
    $park = Park::where('API_key', $apiKey)->first();
    if (!$park) {
        return response()->json(['message' => 'Неверный ключ авторизации'], 401);
    }
    $validator = Validator::make($request->all(), [
        'url' => 'string',
        'commission' => 'numeric',
        'self_employed' => 'boolean',
        'park_name' => 'string',
        'about' => 'string',
        'working_hours' => [
            'required',
            'json',
            function ($attribute, $value, $fail) {
                $daysOfWeek = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                $decodedValue = json_decode($value, true);
                if ($decodedValue === null) {
                    $fail('The '.$attribute.' field must be a valid JSON object.');
                    return;
                }
                foreach ($daysOfWeek as $day) {
                    if (!array_key_exists($day, $decodedValue)) {
                        $fail('The '.$attribute.' field must contain '.$day.' working hours.');
                        return;
                    }
                }
            },
        ],
        'phone' => 'string',
    ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Ошибка валидации', 'errors' => $validator->errors()], 400);
        }
    if ($request->url) {
        $park->url = $request->url;
    }if ($request->commission) {
        $park->commission = $request->commission;
    }if ($request->self_employed) {
        $park->self_employed = $request->self_employed;
    }if ($request->park_name) {
        $park->park_name = $request->park_name;
    }if ($request->about) {
        $park->about = $request->about;
    }if ($request->working_hours) {
        $park->working_hours = $request->working_hours;
    }if ($request->phone) {
        $park->phone = $request->phone;
    }
    $park->save();
        return response()->json(['message' => 'Парк обновлен'], 200);
}
/**
 * Создание подразделения парка
 *
 * Этот метод позволяет создавать подразделение в парке.
 *
 * @OA\Post(
 *     path="/parks/division",
 *     operationId="createParkDivision",
 *     summary="Создание подразделения парка",
 *     tags={"API"},
 *     security={{"api_key": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="city", type="string", description="Город подразделения"),
 *             @OA\Property(property="coords", type="string", description="Координаты подразделения"),
 *             @OA\Property(property="address", type="string", description="Адрес подразделения"),
 *             @OA\Property(property="name", type="string", description="Название подразделения")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Успешное создание подразделения",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Подразделение создано"),
 *             @OA\Property(property="id", type="integer", example="Идентификатор созданного подразделения")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Ошибка аутентификации",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Неверный ключ авторизации")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Ошибки валидации",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Ошибка валидации"),
 *             @OA\Property(property="errors", type="object", example={
 *                 "city": {"Поле city обязательно для заполнения и должно быть строкой."},
 *                 "coords": {"Поле coords обязательно для заполнения и должно быть строкой."},
 *                 "address": {"Поле address обязательно для заполнения и должно быть строкой."},
 *                 "name": {"Поле name обязательно для заполнения и должно быть строкой."},
 *             })
 *         )
 *     )
 * )
 *
 * @param \Illuminate\Http\Request $request Объект запроса с данными для создания подразделения
 * @return \Illuminate\Http\JsonResponse JSON-ответ с результатом операции
 */
public function createParkDivision(Request $request)
{
    $apiKey = $request->header('X-API-Key');
    $park = Park::where('API_key', $apiKey)->first();
    if (!$park) {
        return response()->json(['message' => 'Неверный ключ авторизации'], 401);
    }
    $validator = Validator::make($request->all(), [
        'city' => 'required|string|max:250|exists:cities,name',
        'coords' => 'required|string',
        'address' => 'required|string',
        'name' => [
            'required',
            'string',
            Rule::unique('divisions', 'name')->where(function ($query) use ($park) {
                return $query->where('park_id', $park->id);
            }),
        ],
    ]);

    if ($validator->fails()) {
        return response()->json(['message' => 'Ошибка валидации', 'errors' => $validator->errors()], 400);
    }
    $city = City::where("name",$request->city)->first();
    $division = new Division;
    $division->city_id = $city->id;
    $division->park_id = $park->id;
    $division->coords = $request->coords;
    $division->address = $request->address;
    $division->name = $request->name;
    $division->save();
    return response()->json(['message' => 'Подразделение создано', 'id'=>$division->id], 200);
}
/**
 * Обновление подразделения парка
 *
 * Этот метод позволяет обновлять подразделение в парке.
 *
 * @OA\Put(
 *     path="/parks/division",
 *     operationId="updateParkDivision",
 *     summary="Обновление подразделения парка",
 *     tags={"API"},
 *     security={{"api_key": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="id", type="integer", description="Идентификатор подразделения"),
 *             @OA\Property(property="coords", type="string", description="Координаты подразделения"),
 *             @OA\Property(property="address", type="string", description="Адрес подразделения"),
 *             @OA\Property(property="name", type="string", description="Название подразделения")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Успешное обновление подразделения",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Подразделение успешно обновлено")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Ошибка аутентификации",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Неверный ключ авторизации")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Ошибки валидации",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Ошибка валидации"),
 *             @OA\Property(property="errors", type="object", example={
 *                 "coords": {"Поле coords должно быть строкой."},
 *                 "address": {"Поле address должно быть строкой."},
 *                 "name": {"Поле name должно быть строкой и уникальным в пределах парка."},
 *             })
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Подразделение не найдено",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Подразделение не найдено")
 *         )
 *     )
 * )
 *
 * @param \Illuminate\Http\Request $request Объект запроса с данными для обновления подразделения
 * @return \Illuminate\Http\JsonResponse JSON-ответ с результатом операции
 */
public function updateParkDivision(Request $request)
{
    $apiKey = $request->header('X-API-Key');
    $park = Park::where('API_key', $apiKey)->first();
    if (!$park) {
        return response()->json(['message' => 'Неверный ключ авторизации'], 401);
    }
    $validator = Validator::make($request->all(), [
        'id' => 'required|integer',
        'coords' => 'string',
        'address' => 'string',
        'name' => [
            'required',
            'string',
            Rule::unique('divisions', 'name')->where(function ($query) use ($park) {
                return $query->where('park_id', $park->id);
            })->ignore($request->id),
        ],
    ]);

    if ($validator->fails()) {
        return response()->json(['message' => 'Validation error', 'errors' => $validator->errors()], 400);
    }

    // Update the division with the provided data
    $division = Division::findOrFail($request->id);
    if ($request->coords) {
        $division->coords = $request->coords;
    }
    if ($request->address) {
        $division->address = $request->address;
    }
    if ($request->name) {
        $division->name = $request->name;
    }
    $division->save();
    return response()->json(['message' => 'Подразделение обновлено'], 200);
}
}
