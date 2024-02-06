<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Park;
use App\Division;
use App\User;
use Illuminate\Support\Facades\Validator;
use App\Car;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
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
     * @OA\Post(
     *     path="/push-cars",
     *     summary="Добавить несколько автомобилей",
     *     tags={"API"},
     *     security={{"api_key": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="cars", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="string", maxLength=17, description="VIN-номер"),
     *                 @OA\Property(property="city", type="string", maxLength=50, description="Город колонны"),
     *                 @OA\Property(property="division_name", type="string", maxLength=250, description="Название колонны"),
     *                 @OA\Property(property="fuel_type", type="integer", description="Вид топлива (1 - газ, 0 - бензин)"),
     *                 @OA\Property(property="transmission_type", type="integer", description="КПП ТС (1 - автомат, 0 - механика)"),
     *                 @OA\Property(property="brand", type="string", maxLength=50, description="Бренд авто"),
     *                 @OA\Property(property="model", type="string", maxLength=80, description="Модель авто"),
     *                 @OA\Property(property="class", type="integer", description="Тариф авто (1 - эконом, 2 - комфорт, 3 - комфорт+, 4 - бизнес)"),
     *                 @OA\Property(property="year_produced", type="integer", description="Год выпуска авто"),
     *                 @OA\Property(property="images", type="array", @OA\Items(type="string"), description="Фото авто"),
     *             ))
     *         )
     *     ),
     *     @OA\Response(response="200", description="Успешный ответ", @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Автомобили успешно добавлены")
     *     )),
     *     @OA\Response(response="401", description="Ошибка аутентификации", @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Ошибка аутентификации")
     *     )),
     *     @OA\Response(response="500", description="Ошибка сервера", @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Ошибка сервера")
     *     )),
     *     @OA\Response(response="400", description="Ошибки валидации", @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Неверные или недостающие параметры в запросе"),
     *         @OA\Property(property="errors", type="object", nullable=true, description="Список ошибок валидации")
     *     ))
     * )
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
            'cars.*.id' => 'required|string|max:20',
            'cars.*.images' => 'required|array',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        $cars = $request->input('cars');
        foreach ($cars as $index => $carData) {
            $city = $carData['division_name'];
            $columns = $carData['division_name'];
            $parksCity = Division::where('city', $city)->where('colInputName', $columns)->where('user_id', $park->user_id)->first();
            if (!$parksCity) {
                $parksCity = new Division;
                $parksCity->user_id = $park->user_id;
                $parksCity->city = $city;
                $parksCity->colInputName = $columns;
                $parksCity->save();
            }
            $Cars = new Car;
            $Cars->park_city = $parksCity->id;
            $Cars->petrol_car = $carData['fuel_type'];
            $Cars->rate_car = null;
            $Cars->kpp_car = $carData['transmission_type'];
            $Cars->brand_car = $carData['brand'];
            $Cars->model_car = $carData['model'];
            $Cars->conditions = $carData['class'];
            $Cars->year_car = $carData['year_produced'];
            $Cars->commission_car = null;
            $Cars->seller_id = $park->user_id;
            $Cars->titleyear = null;
            $Cars->slug = Str::slug($carData['id']);
            $Cars->image = $carData['images'][0];
            $Cars->image_gallery = json_encode($carData['images']);
            $Cars->status = 1;
            $Cars->price = null;
            $Cars->address = $parksCity['colInputAdress'];
            $Cars->revision = 0;
            $Cars->tax = 0;
            $Cars->view = 0;
            $Cars->sold_count = 0;
            $Cars->featured = 0;
            $Cars->deposit = null;
            $Cars->booking_id = null;
            $Cars->city = $city;
            $Cars->renta = $park->user_id;
            $Cars->is_Car_on = 0;
            $Cars->columns = $columns;
            $Cars->self_employed = null;
            $Cars->save();
        }
        return response()->json(['message' => 'Автомобили успешно добавлены'], 200);
    }


    /**
     * @OA\Put(
     *     path="/cars",
     *     summary="Обновление информации о машине",
     *     tags={"API"},
     *     security={{"api_key": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", maxLength=20, description="VIN-номер"),
     *             @OA\Property(property="city", type="string", maxLength=50, nullable=true, description="Город машины"),
     *             @OA\Property(property="division_name", type="string", maxLength=250, nullable=true, description="Подразделение машины"),
     *             @OA\Property(property="class", type="integer", nullable=true, description="Тариф авто (1 - эконом, 2 - комфорт, 3 - комфорт+, 4 - бизнес)"),
     *             @OA\Property(property="images", type="array", @OA\Items(type="string"), nullable=true, description="Изображения машины"),
     *         )
     *     ),
     *     @OA\Response(response="200", description="Успешный ответ", @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Машина успешно обновлена")
     *     )),
     *     @OA\Response(response="401", description="Ошибка аутентификации", @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Ошибка аутентификации")
     *     )),
     *     @OA\Response(response="500", description="Ошибка сервера", @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Ошибка сервера")
     *     )),
     *     @OA\Response(response="400", description="Ошибки валидации", @OA\JsonContent(
     *         @OA\Property(property="errors", type="object", example={
     *             "id": {"Поле id обязательно для заполнения."},
     *             "city": {"Поле city должно быть строкой."},
     *         })
     *     ))
     * )
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
        $slug = $request->input('id');
        $city = $request->input('city');
        $columns = $request->input('division_name');

        $Car = Car::where('slug', $slug)->first();
        if (!$Car) {
            return response()->json(['message' => 'Автомобиль не найден'], 404);
        }

        $parksCity = Division::where('city', $city)->where('colInputName', $columns)->where('user_id', $park->user_id)->first();

        if (!$parksCity) {
            // Создание новой записи в таблице Parks_city
            $parksCity = new Division;
            $parksCity->user_id = $park->user_id;
            $parksCity->city = $city;
            $parksCity->colInputName = $columns;
            $parksCity->save();
            $Car->park_city = $parksCity->id;
        } else {
            if ($Car->park_city !== $parksCity->id) {
                $Car->park_city = $parksCity->id;
            }
        }


        if ($request->has('fuel_type')) {
            $Car->petrol_car = $request->input('fuel_type');
        }
        // if ($request->has('kpp_car'))
        // {
        //     $Car->kpp_car = $request->input('kpp_car');
        // }
        // if ($request->has('brand_car'))
        // {
        //     $Car->brand_car = $request->input('brand_car');
        // }
        // if ($request->has('model_car'))
        // {
        //     $Car->model_car = $request->input('model_car');
        // }
        // if ($request->has('status'))
        // {
        //     $Car->status = $request->input('status');
        // }
        if ($request->has('class')) {
            $Car->conditions = $request->input('class');
        }
        // if ($request->has('year_car'))
        // {
        //     $Car->year_car = $request->input('year_car');
        // }
        // if ($request->has('description'))
        // {
        //     $Car->description = $request->input('description');
        // }
        if ($request->has('images')) {
            $Car->image = $request->input('images')[0];
            $Car->image_gallery = json_encode($request->input('image'));
        }
        // if ($request->has('price'))
        // {
        //     $Car->price = $request->input('price');
        // }
        $Car->save();

        return response()->json(['message' => 'Автомобиль успешно изменен'], 200);
    }

    /**
     * @OA\Put(
     *     path="/cars/status",
     *     summary="Обновление статуса допуска к бронированию",
     *     tags={"API"},
     *     security={{"api_key": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", maxLength=20, description="VIN-номер"),
     *             @OA\Property(property="status", type="integer", description="Допуск машины к бронированию, где 1 - допущена, 0 - заблокирована")
     *         )
     *     ),
     *     @OA\Response(response="200", description="Успешный ответ", @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Машина успешно обновлена")
     *     )),
     *     @OA\Response(response="401", description="Ошибка аутентификации", @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Ошибка аутентификации")
     *     )),
     *     @OA\Response(response="500", description="Ошибка сервера", @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Ошибка сервера")
     *     )),
     *     @OA\Response(response="400", description="Ошибки валидации", @OA\JsonContent(
     *         @OA\Property(property="errors", type="object", example={
     *             "id": {"Поле id обязательно для заполнения."},
     *             "status": {"Поле status должно быть числом."}
     *         })
     *     )),
     *         @OA\Response(response="409", description="Конфликт", @OA\JsonContent(
     *         @OA\Property(property="errors", type="object", example="Авто сейчас забронировано, изменить статус невозможно")
     *     ))
     * )
     */

    public function updateCarStatus(Request $request)
    {
        $apiKey = $request->header('X-API-Key');
        // Проверка ключа авторизации
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
        $slug = $request->input('id');
        $Car = Car::where('slug', $slug)->first();
        if (!$Car) {
            return response()->json(['message' => 'Автомобиль не найден'], 404);
        }
        $booking = $Car->booking_id;
        if ($booking === null || $booking === 'block') {
            $Car->status = $request->input('status');
            $Car->save();
        } else {
            return response()->json(['message' => 'Авто сейчас забронировано, изменить статус невозможно'], 409);
        }


        return response()->json(['message' => 'Автомобиль успешно изменен'], 200);
    }



    /**
     * @OA\Put(
     *     path="URL_АДРЕС_ПАРКА/cars/outbound/status",
     *     summary="Изменить статус бронирования автомобиля, пример метода, который будет использоваться для передачи данных ОТ МОЙ ГАРАЖ",
     *     tags={"API"},
     *     @OA\Response(response="204", description="Успешное изменение"),
     *     @OA\Response(response="400", description="Некорректный запрос"),
     *     security={{"api_key": {}}},

     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", description="VIN-номер"),
     *             @OA\Property(property="is_booked", type="integer", description="Статус бронирования, где 1 - забронировано, 0 - бронь отменена")
     *         )
     *     )
     * )
     */

    public function changedBookingStatus($Car)
    {
        $booking = $Car->booking_id != null ? false : true;
        $park = Park::where('user_id', $Car->seller_id)->first();
        $API_key = $park->API_key;
        $url = $park->url;
        $slug = $Car->slug;
        if ($url != null) {

            $client = new Client();
            $response = $client->put($url->url, [
                'headers' => [
                    'X-API-Key' => $API_key,
                ],
                'json' => ['is_booked' => $booking, 'id' => $slug],
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
     * @OA\Post(
     *     path="/parks/rent-terms",
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
     *             @OA\Property(property="schemas", type="array", @OA\Items(
     *                     @OA\Property(property="daily_amount", type="number", format="float", description="Стоимость аренды авто"),
     *                     @OA\Property(property="non_working_days", type="integer", description="Количество нерабочих дней"),
     *                     @OA\Property(property="working_days", type="integer", description="Количество рабочих дней")
     *                 ), description="Схемы аренды"),
     *         )
     *     ),
     *     @OA\Response(response="200", description="Успешный ответ", @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Условие аренды успешно создано или изменено"),
     *         @OA\Property(property="id", type="integer", example="Идентификатор условия аренды")
     *     )),
     *     @OA\Response(response="401", description="Ошибка аутентификации", @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Ошибка аутентификации")
     *     )),
     *     @OA\Response(response="422", description="Ошибки валидации", @OA\JsonContent(
     *         @OA\Property(property="errors", type="object", example={
     *             "deposit_amount_daily": {"Поле deposit_amount_daily обязательно для заполнения и должно быть числом."},
     *             "deposit_amount_total": {"Поле deposit_amount_total обязательно для заполнения и должно быть числом."},
     *             "is_buyout_possible": {"Поле is_buyout_possible обязательно для заполнения и должно быть булевым значением."},
     *             "minimum_period_days": {"Поле minimum_period_days обязательно для заполнения и должно быть целым числом."},
     *             "name": {"Поле name обязательно для заполнения и должно быть строкой."},
     *             "schemas": {"Поле schemas обязательно для заполнения и должно быть массивом строк."},
     *         })
     *     )),
     *     @OA\Response(response="404", description="Парк с указанным API ключом не найден", @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Парк не найден")
     *     )),
     *     @OA\Response(response="500", description="Ошибка сервера", @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Ошибка сервера")
     *     ))
     * )
     */


    public function createOrUpdateDivision(Request $request)
    {
        $apiKey = $request->header('X-API-Key');
        $park = Park::where('API_key', $apiKey)->firstOrFail();

        $validator = $this->validateDivision($request);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $DivisionId = $request->input('rent_term_id');
        $data = [
            'deposit_amount_daily' => $request->input('deposit_amount_daily'),
            'deposit_amount_total' => $request->input('deposit_amount_total'),
            'is_buyout_possible' => $request->input('is_buyout_possible'),
            'minimum_period_days' => $request->input('minimum_period_days'),
            'name' => $request->input('name'),
            'park_id' => $park->id,
            'schemas' => json_encode($request->input('schemas')),
        ];

        if ($DivisionId) {
            $Division = Division::where('id', $DivisionId)
                ->where('park_id', $park->id)
                ->first();
            if ($Division) {
                $Division->update($data);
                return response()->json(['message' => 'Условие аренды успешно изменено'], 200);
            }
        }
        $Division = Division::create($data);
        $Division->save();
        return response()->json([
            'message' => 'Условие аренды успешно создано.',
            'id' => $Division->id
        ], 200);
    }

    private function validateDivision(Request $request)
    {
        $rules = [
            'deposit_amount_daily' => 'required|numeric',
            'deposit_amount_total' => 'required|numeric',
            'is_buyout_possible' => 'required|boolean',
            'minimum_period_days' => 'required|integer',
            'rent_term_id' => 'integer',
            'name' => 'required|string',
            'schemas' => 'required|array',
            'schemas.*.daily_amount' => 'required|numeric',
            'schemas.*.non_working_days' => 'required|integer',
            'schemas.*.working_days' => 'required|integer',
        ];

        $messages = [
            'required' => 'Поле :attribute обязательно для заполнения.',
            'numeric' => 'Поле :attribute должно быть числовым.',
            'boolean' => 'Поле :attribute должно быть булевым значением.',
            'integer' => 'Поле :attribute должно быть целым числом.',
            'string' => 'Поле :attribute должно быть строкой.',
            'array' => 'Поле :attribute должно быть массивом.',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }

    /**
     * @OA\Put(
     *     path="/cars/rent-term",
     *     summary="Обновление условия аренды для автомобиля",
     *     tags={"API"},
     *     security={{"api_key": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", description="VIN-номер"),
     *             @OA\Property(property="rent_term_id", type="integer", description="Идентификатор условия аренды"),
     *         )
     *     ),
     *     @OA\Response(response="200", description="Успешный ответ", @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Условие аренды успешно обновлено для автомобиля")
     *     )),
     *     @OA\Response(response="401", description="Ошибка аутентификации", @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Ошибка аутентификации")
     *     )),
     *     @OA\Response(response="400", description="Ошибки валидации", @OA\JsonContent(
     *         @OA\Property(property="errors", type="object", example={
     *             "rent_term_id": {"Поле rent_term_id обязательно для заполнения и должно быть целым числом."},
     *             "id": {"Поле id обязательно для заполнения и должно быть целым числом."},
     *         })
     *     )),
     *     @OA\Response(response="404", description="Условие аренды или автомобиль не найдены", @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Условие аренды или автомобиль не найдены")
     *     )),
     *     @OA\Response(response="500", description="Ошибка сервера", @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Ошибка сервера")
     *     ))
     * )
     */

    public function UpdateCarDivision(Request $request)
    {
        $apiKey = $request->header('X-API-Key');
        $park = Park::where('API_key', $apiKey)->firstOrFail();
        $validator = Validator::make($request->all(), [
            'rent_term_id' => 'required|integer',
            'id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $DivisionId = $request->input('rent_term_id');
        $id = $request->input('id');

        $Division = Division::where('id', $DivisionId)
            ->where('park_id', $park->id)
            ->first();

        if (!$Division) {
            return response()->json(['message' => 'Условие аренды не найдено'], 404);
        }

        $Car = Car::where('slug', $id)->where('seller_id', $park->user_id)->first();

        if (!$Car) {
            return response()->json(['message' => 'Автомобиль не найден'], 404);
        }

        $Car->rent_term_id = $DivisionId;
        $Car->save();

        return response()->json(['message' => 'Условие аренды успешно обновлено для автомобиля'], 200);
    }
}
