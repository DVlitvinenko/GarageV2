<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Park;
use App\Models\Division;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Models\Car;
use App\Models\City;
use App\Models\Tariff;
use App\Models\RentTerm;
use App\Enums\SuitEnum;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
use App\Http\Controllers\Enums;

use App\Http\Controllers\ParserController;
use Illuminate\Support\Facades\File;


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
     *     summary="Добавить несколько автомобилей",
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
     *                     @OA\Property(property="city", type="string", maxLength=50, description="Город колонны"),
     *                     @OA\Property(property="division_name", type="string", maxLength=250, description="Название колонны"),
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

        // Проверка ключа авторизации
        $park = Park::where('API_key', $apiKey)->first();
        if (!$park) {
            return response()->json(['message' => 'Неверный ключ авторизации'], 401);
        }
        // Валидация данных запроса
        $validator = Validator::make($request->all(), [
            'cars' => 'required|array',
            'cars.*.city' => ['required', 'string', 'max:250', function ($attribute, $value, $fail) {
                $parser = new ParserController();
                if (!$parser->parseCity($value)) {
                    $fail('Некорректный город.');
                }
            }],
            'cars.*.division_name' => 'required|string|max:250',
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
            'cars.*.id' => 'required|string|max:20|unique:cars,id_car',
            'cars.*.images' => 'required|array',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        $cars = $request->input('cars');
        foreach ($cars as $index => $carData) {
            $cityName = $carData['city'];
            $divisionName = $carData['division_name'];
            $city = City::firstOrCreate(['name' => $cityName]);

            $division = $this->divisionCheck($divisionName, $park->id, $city->id);

            $car = new Car;
            $car->division_id = $division->id;
            $car->fuel_type = $carData['fuel_type'];
            $car->transmission_type = $carData['transmission_type'];
            $car->brand = $carData['brand'];
            $car->model = $carData['model'];
            $car->tariff_id = $this->GetTariffId($park->id, $city->id, $carData['class']);
            $car->year_produced = $carData['year_produced'];
            $car->id_car = $carData['id'];
            $car->images = json_encode($carData['images']);
            $car->booking_time = null;
            $car->user_booked_id = null;
            $car->show_status = 1;
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
            'city' => ['nullable', 'string', 'max:50', function ($attribute, $value, $fail) {
                $parser = new ParserController();
                if (!$parser->parseCity($value)) {
                    $fail('Некорректный город.');
                }
            },],
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
        $car = Car::where('id_car', $carId)
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
        $car = Car::where('id_car', $carId)
            ->where('park_id', $park->id)
            ->first();
        if (!$car) {
            return response()->json(['message' => 'Автомобиль не найден'], 404);
        }
        $booking = $car->booking_id;
        if ($booking === null || $booking === 'block') {
            $car->show_status = $request->input('status');
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
     *     operationId="changedBookingStatus",
     *     security={{"api_key": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", description="VIN-номер автомобиля"),
     *             @OA\Property(property="is_booked", type="integer", description="Статус бронирования. 1 - забронировано, 0 - бронь отменена")
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

    public function changedBookingStatus($car)
    {
        $booking = $car->booking_id !== null ? false : true;
        $park = Park::where('user_id', $car->seller_id)->first();
        if (!$park) {
            // Обработка ошибки, если парк не найден
            return;
        }
        $apiKey = $park->API_key;
        $url = $park->url;
        $carId = $car->id;
        if ($url !== null) {
            $client = new Client();
            $response = $client->put($url, [
                'headers' => [
                    'X-API-Key' => $apiKey,
                ],
                'json' => [
                    'is_booked' => $booking,
                    'car_id' => $carId,
                ],
                'http_errors' => false, // Отключаем обработку ошибок
            ]);
            $statusCode = $response->getStatusCode();
            if ($statusCode === 204) {
                // Успешная передача
            } else {
                // Обработка ошибки, если необходимо
            }
        }
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
     *     operationId="updateCarDivision",
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

        if (!$rentTerm) {
            return response()->json(['message' => 'Условие аренды не найдено'], 404);
        }
        $car = Car::where('id_car', $carId)
            ->where('park_id', $park->id)
            ->first();

        if (!$car) {
            return response()->json(['message' => 'Автомобиль не найден'], 404);
        }



        $car->rent_term_id = $rentTermId;
        $car->save();

        return response()->json(['message' => 'Условие аренды успешно привязано к автомобилю'], 200);
    }
    private function GetTariffId($park_id, $city_id, $classNum)
    {
        $class = '';
        switch ($classNum) {
            case 1:
                $class = 'Эконом';
                break;
            case 2:
                $class = 'Комфорт';
                break;
            case 3:
                $class = 'Комфорт+';
                break;
            case 4:
                $class = 'Бизнес';
                break;
            default:
                $class = null;
                break;
        }
        $tariffId = Tariff::where('class', $class)
            ->where('park_id', $park_id)
            ->where('city_id', $city_id)
            ->value('id');
        if (!$tariffId) {
            $newTariff = Tariff::create([
                'class' => $class,
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
}
