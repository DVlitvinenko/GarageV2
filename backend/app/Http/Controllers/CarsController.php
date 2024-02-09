<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use App\Models\Car;
use App\Models\Tariff;
use App\Models\City;
use App\Models\Driver;
use App\Models\DriverSpecification;
use App\Models\Division;
use App\Models\RentTerm;
use App\Models\Schema;
use Illuminate\Support\Facades\Auth;

class CarsController extends Controller
{
    /**
     * Получение списка автомобилей с учетом фильтров
     *
     * @OA\Get(
     *     path="/cars",
     *     operationId="AuthGetCars",
     *     summary="Получение списка автомобилей с учетом фильтров",
     *     tags={"Cars"},
     *     @OA\RequestBody(
     *         required=false,
     *         description="Тело запроса",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="city",
     *                     type="string",
     *                     description="Название города"
     *                 ),
     *                 @OA\Property(
     *                     property="fuel_type",
     *                     type="string",
     *                     description="Тип топлива"
     *                 ),
     *                 @OA\Property(
     *                     property="transmission_type",
     *                     type="string",
     *                     description="Тип трансмиссии"
     *                 ),
     *                 @OA\Property(
     *                     property="brand",
     *                     type="string",
     *                     description="Марка автомобиля"
     *                 ),
     *                 @OA\Property(
     *                     property="model",
     *                     type="string",
     *                     description="Модель автомобиля"
     *                 ),
     *                 @OA\Property(
     *                     property="class",
     *                     type="integer",
     *                     description="Класс автомобиля (1 - Эконом, 2 - Комфорт, 3 - Комфорт+, 4 - Бизнес)"
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="city",
     *         in="query",
     *         description="Название города",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="fuel_type",
     *         in="query",
     *         description="Тип топлива",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="transmission_type",
     *         in="query",
     *         description="Тип трансмиссии",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="brand",
     *         in="query",
     *         description="Марка автомобиля",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="model",
     *         in="query",
     *         description="Модель автомобиля",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="class",
     *         in="query",
     *         description="Класс автомобиля (1 - Эконом, 2 - Комфорт, 3 - Комфорт+, 4 - Бизнес)",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             @OA\Property(property="cars", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", description="Идентификатор автомобиля"),
     *                 @OA\Property(property="division_id", type="integer", description="Индекс"),
     *                 @OA\Property(property="tariff_id", type="integer", description="Индекс"),
     *                 @OA\Property(property="rent_term_id", type="integer", description="Индекс"),
     *                 @OA\Property(property="fuel_type", type="integer", description="Тип топлива"),
     *                 @OA\Property(property="transmission_type", type="integer", description="Тип трансмиссии"),
     *                 @OA\Property(property="brand", type="string", description="Марка автомобиля"),
     *                 @OA\Property(property="model", type="string", description="Модель автомобиля"),
     *                 @OA\Property(property="year_produced", type="integer", description="Год производства"),
     *                 @OA\Property(property="id_car", type="string", description="Идентификатор автомобиля"),
     *                 @OA\Property(property="images", type="string", description="Ссылки на изображения"),
     *                 @OA\Property(property="booking_time", type="string", format="date-time", description="Время бронирования"),
     *                 @OA\Property(property="user_booked_id", type="integer", description="Идентификатор пользователя, который забронировал"),
     *                 @OA\Property(property="show_status", type="integer", description="Статус отображения"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", description="Дата создания записи"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", description="Дата обновления записи"),
     *                 @OA\Property(property="division", type="object", description="Данные о подразделении",
     *                     @OA\Property(property="id", type="integer", description="Идентификатор подразделения"),
     *                     @OA\Property(property="park", type="object", description="Данные о парке",
     *                         @OA\Property(property="id", type="integer", description="Идентификатор парка"),
     *                         @OA\Property(property="API_key", type="string", description="API ключ"),
     *                         @OA\Property(property="url", type="string", description="URL"),
     *                         @OA\Property(property="comission", type="number", format="double", description="Комиссия"),
     *                         @OA\Property(property="park_name", type="string", description="Название парка"),
     *                         @OA\Property(property="about", type="string", description="Описание"),
     *                         @OA\Property(property="working_hours", type="string", description="Рабочие часы"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", description="Дата создания записи"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", description="Дата обновления записи"),
     *                     ),
     *                     @OA\Property(property="city", type="object", description="Данные о городе",
     *                         @OA\Property(property="id", type="integer", description="Идентификатор города"),
     *                         @OA\Property(property="name", type="string", description="Название города"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", description="Дата создания записи"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", description="Дата обновления записи"),
     *                     ),
     *                     @OA\Property(property="coords", type="string", description="Координаты"),
     *                     @OA\Property(property="address", type="string", description="Адрес"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", description="Дата создания записи"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", description="Дата обновления записи"),
     *                 ),
     *                 @OA\Property(property="tariff", type="object", description="Данные о тарифе",
     *                     @OA\Property(property="id", type="integer", description="Идентификатор тарифа"),
     *                     @OA\Property(property="class", type="string", description="Класс тарифа"),
     *                     @OA\Property(property="park_id", type="integer", description="Идентификатор парка"),
     *                     @OA\Property(property="city_id", type="integer", description="Идентификатор города"),
     *                     @OA\Property(property="criminal_ids", type="string", description="Идентификаторы преступлений"),
     *                     @OA\Property(property="participation_accident", type="boolean", description="Участие в авариях"),
     *                     @OA\Property(property="experience", type="integer", description="Опыт"),
     *                     @OA\Property(property="max_cont_seams", type="integer", description="Максимальное количество швов"),
     *                     @OA\Property(property="abandoned_car", type="boolean", description="Брошенный автомобиль"),
     *                     @OA\Property(property="min_scoring", type="integer", description="Минимальный балл"),
     *                     @OA\Property(property="forbidden_republic_ids", type="string", description="Идентификаторы запрещенных республик"),
     *                     @OA\Property(property="alcohol", type="boolean", description="Наличие алкоголя"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", description="Дата создания записи"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", description="Дата обновления записи"),
     *                 ),
     *                 @OA\Property(property="rent_term", type="object", description="Данные о сроке аренды",
     *                     @OA\Property(property="id", type="integer", description="Идентификатор срока аренды"),
     *                     @OA\Property(property="park_id", type="integer", description="Идентификатор парка"),
     *                     @OA\Property(property="deposit_amount_daily", type="number", description="Сумма депозита за день"),
     *                     @OA\Property(property="deposit_amount_total", type="number", description="Общая сумма депозита"),
     *                     @OA\Property(property="minimum_period_days", type="integer", description="Минимальный период в днях"),
     *                     @OA\Property(property="name", type="string", description="Название срока аренды"),
     *                     @OA\Property(property="is_buyout_possible", type="boolean", description="Возможность выкупа"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", description="Дата создания записи"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", description="Дата обновления записи"),
     *                 ),
     *                 @OA\Property(property="schema", type="object", description="Данные о схеме аренды",
     *                     @OA\Property(property="id", type="integer", description="Идентификатор схемы аренды"),
     *                     @OA\Property(property="rent_term_id", type="integer", description="Идентификатор срока аренды"),
     *                     @OA\Property(property="daily_amount", type="integer", description="Суточная стоимость"),
     *                     @OA\Property(property="non_working_days", type="integer", description="Количество нерабочих дней"),
     *                     @OA\Property(property="working_days", type="integer", description="Количество рабочих дней"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", description="Дата создания записи"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", description="Дата обновления записи"),
     *                 ),
     *             )),
     *         ),
     *     ),
     *     @OA\Response(response="401", description="Ошибка аутентификации"),
     *     @OA\Response(response="422", description="Ошибки валидации"),
     *     @OA\Response(response="500", description="Ошибка сервера"),
     * )
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */




    public function AuthGetCars(Request $request) //чисто для сваггера
    {
    }
    /**
     * Получение списка автомобилей с учетом фильтров (аутентифицированный запрос)
     *
     * @OA\Get(
     *     path="/auth/cars",
     *     operationId="getCars",
     *     summary="Получение списка автомобилей с учетом фильтров (аутентифицированный запрос)",
     *     tags={"Cars"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=false,
     *         description="Тело запроса",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="city",
     *                     type="string",
     *                     description="Название города"
     *                 ),
     *                 @OA\Property(
     *                     property="fuel_type",
     *                     type="string",
     *                     description="Тип топлива"
     *                 ),
     *                 @OA\Property(
     *                     property="transmission_type",
     *                     type="string",
     *                     description="Тип трансмиссии"
     *                 ),
     *                 @OA\Property(
     *                     property="brand",
     *                     type="string",
     *                     description="Марка автомобиля"
     *                 ),
     *                 @OA\Property(
     *                     property="model",
     *                     type="string",
     *                     description="Модель автомобиля"
     *                 ),
     *                 @OA\Property(
     *                     property="class",
     *                     type="integer",
     *                     description="Класс автомобиля (1 - Эконом, 2 - Комфорт, 3 - Комфорт+, 4 - Бизнес)"
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="city",
     *         in="query",
     *         description="Название города",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="fuel_type",
     *         in="query",
     *         description="Тип топлива",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="transmission_type",
     *         in="query",
     *         description="Тип трансмиссии",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="brand",
     *         in="query",
     *         description="Марка автомобиля",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="model",
     *         in="query",
     *         description="Модель автомобиля",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="class",
     *         in="query",
     *         description="Класс автомобиля (1 - Эконом, 2 - Комфорт, 3 - Комфорт+, 4 - Бизнес)",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             @OA\Property(property="cars", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", description="Идентификатор автомобиля"),
     *                 @OA\Property(property="division_id", type="integer", description="Индекс"),
     *                 @OA\Property(property="tariff_id", type="integer", description="Индекс"),
     *                 @OA\Property(property="rent_term_id", type="integer", description="Индекс"),
     *                 @OA\Property(property="fuel_type", type="integer", description="Тип топлива"),
     *                 @OA\Property(property="transmission_type", type="integer", description="Тип трансмиссии"),
     *                 @OA\Property(property="brand", type="string", description="Марка автомобиля"),
     *                 @OA\Property(property="model", type="string", description="Модель автомобиля"),
     *                 @OA\Property(property="year_produced", type="integer", description="Год производства"),
     *                 @OA\Property(property="id_car", type="string", description="Идентификатор автомобиля"),
     *                 @OA\Property(property="images", type="string", description="Ссылки на изображения"),
     *                 @OA\Property(property="booking_time", type="string", format="date-time", description="Время бронирования"),
     *                 @OA\Property(property="user_booked_id", type="integer", description="Идентификатор пользователя, который забронировал"),
     *                 @OA\Property(property="show_status", type="integer", description="Статус отображения"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", description="Дата создания записи"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", description="Дата обновления записи"),
     *                 @OA\Property(property="division", type="object", description="Данные о подразделении",
     *                     @OA\Property(property="id", type="integer", description="Идентификатор подразделения"),
     *                     @OA\Property(property="park", type="object", description="Данные о парке",
     *                         @OA\Property(property="id", type="integer", description="Идентификатор парка"),
     *                         @OA\Property(property="API_key", type="string", description="API ключ"),
     *                         @OA\Property(property="url", type="string", description="URL"),
     *                         @OA\Property(property="comission", type="number", format="double", description="Комиссия"),
     *                         @OA\Property(property="park_name", type="string", description="Название парка"),
     *                         @OA\Property(property="about", type="string", description="Описание"),
     *                         @OA\Property(property="working_hours", type="string", description="Рабочие часы"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", description="Дата создания записи"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", description="Дата обновления записи"),
     *                     ),
     *                     @OA\Property(property="city", type="object", description="Данные о городе",
     *                         @OA\Property(property="id", type="integer", description="Идентификатор города"),
     *                         @OA\Property(property="name", type="string", description="Название города"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", description="Дата создания записи"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", description="Дата обновления записи"),
     *                     ),
     *                     @OA\Property(property="coords", type="string", description="Координаты"),
     *                     @OA\Property(property="address", type="string", description="Адрес"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", description="Дата создания записи"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", description="Дата обновления записи"),
     *                 ),
     *                 @OA\Property(property="tariff", type="object", description="Данные о тарифе",
     *                     @OA\Property(property="id", type="integer", description="Идентификатор тарифа"),
     *                     @OA\Property(property="class", type="string", description="Класс тарифа"),
     *                     @OA\Property(property="park_id", type="integer", description="Идентификатор парка"),
     *                     @OA\Property(property="city_id", type="integer", description="Идентификатор города"),
     *                     @OA\Property(property="criminal_ids", type="string", description="Идентификаторы преступлений"),
     *                     @OA\Property(property="participation_accident", type="boolean", description="Участие в авариях"),
     *                     @OA\Property(property="experience", type="integer", description="Опыт"),
     *                     @OA\Property(property="max_cont_seams", type="integer", description="Максимальное количество швов"),
     *                     @OA\Property(property="abandoned_car", type="boolean", description="Брошенный автомобиль"),
     *                     @OA\Property(property="min_scoring", type="integer", description="Минимальный балл"),
     *                     @OA\Property(property="forbidden_republic_ids", type="string", description="Идентификаторы запрещенных республик"),
     *                     @OA\Property(property="alcohol", type="boolean", description="Наличие алкоголя"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", description="Дата создания записи"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", description="Дата обновления записи"),
     *                 ),
     *                 @OA\Property(property="rent_term", type="object", description="Данные о сроке аренды",
     *                     @OA\Property(property="id", type="integer", description="Идентификатор срока аренды"),
     *                     @OA\Property(property="park_id", type="integer", description="Идентификатор парка"),
     *                     @OA\Property(property="deposit_amount_daily", type="number", description="Сумма депозита за день"),
     *                     @OA\Property(property="deposit_amount_total", type="number", description="Общая сумма депозита"),
     *                     @OA\Property(property="minimum_period_days", type="integer", description="Минимальный период в днях"),
     *                     @OA\Property(property="name", type="string", description="Название срока аренды"),
     *                     @OA\Property(property="is_buyout_possible", type="boolean", description="Возможность выкупа"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", description="Дата создания записи"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", description="Дата обновления записи"),
     *                 ),
     *                 @OA\Property(property="schema", type="object", description="Данные о схеме аренды",
     *                     @OA\Property(property="id", type="integer", description="Идентификатор схемы аренды"),
     *                     @OA\Property(property="rent_term_id", type="integer", description="Идентификатор срока аренды"),
     *                     @OA\Property(property="daily_amount", type="integer", description="Суточная стоимость"),
     *                     @OA\Property(property="non_working_days", type="integer", description="Количество нерабочих дней"),
     *                     @OA\Property(property="working_days", type="integer", description="Количество рабочих дней"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", description="Дата создания записи"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", description="Дата обновления записи"),
     *                 ),
     *             )),
     *         ),
     *     ),
     *     @OA\Response(response="401", description="Ошибка аутентификации"),
     *     @OA\Response(response="422", description="Ошибки валидации"),
     *     @OA\Response(response="500", description="Ошибка сервера"),
     * )
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */


    public function getCars(Request $request)
    {
        $filters = $request->all();
        $cityName = $filters['city'] ?? null;
        $cityId = City::where('name', $cityName)->value('id');

        $carsQuery = Car::query()->with('tariff', 'rentTerm', 'schema', 'division.park', 'division.city');
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->user_status >= 1) {
                $driver = Driver::where('user_id', $user->id)->first();
                if ($driver) {
                    $driverSpec = DriverSpecification::where('driver_id', $driver->id)->first();
                    $filteredTariffs = Tariff::where('city_id', $cityId)->where(function ($query) use ($driverSpec) {
                        $query
                            ->where(function ($q) use ($driverSpec) {
                                if ($driverSpec->abandoned_car) {
                                    $q->where('abandoned_car', true);
                                } else {
                                    $q->where('abandoned_car', false);
                                }
                            })
                            ->Where(function ($q) use ($driverSpec) {
                                if ($driverSpec->participation_accident) {
                                    $q->where('participation_accident', true);
                                } else {
                                    $q->where('participation_accident', false);
                                }
                            })
                            ->Where(function ($q) use ($driverSpec) {
                                if ($driverSpec->alcohol) {
                                    $q->where('alcohol', true);
                                } else {
                                    $q->where('alcohol', false);
                                }
                            })
                            ->Where(function ($q) use ($driverSpec) {
                                if ($driverSpec->scoring !== null) {
                                    $q->where('min_scoring', '<', $driverSpec->scoring);
                                }
                            })
                            ->Where(function ($q) use ($driverSpec) {
                                if ($driverSpec->experience !== null) {
                                    $q->where('experience', '>=', $driverSpec->experience);
                                }
                            })
                            ->Where(function ($q) use ($driverSpec) {
                                if ($driverSpec->count_seams !== null) {
                                    $q->where('max_cont_seams', '<', $driverSpec->count_seams);
                                }
                            })
                            ->where('criminal_ids', '!=', $driverSpec->criminal_ids)
                            ->whereNotIn('forbidden_republic_ids', [$driverSpec->republick_id]);
                    })->pluck('id');

                    $carsQuery->whereIn('tariff_id', $filteredTariffs);
                    if (isset($filters['fuel_type'])) {
                        $carsQuery->where('fuel_type', $filters['fuel_type']);
                    }
                    if (isset($filters['transmission_type'])) {
                        $carsQuery->where('transmission_type', $filters['transmission_type']);
                    }

                    if (isset($filters['brand'])) {
                        $carsQuery->where('brand', $filters['brand']);
                    }
                    if (isset($filters['model'])) {
                        $carsQuery->where('model', $filters['model']);
                    }
                    $carsQuery->where('show_status', 1);

                    $cars = $carsQuery->get();

                    if (isset($filters['class'])) {
                        switch ($filters['class']) {
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
                        $cars = $cars->filter(function ($car) use ($class) {
                            return $car->tariff->class == $class;
                        });
                    }

                    return response()->json(['cars' => $cars]);
                }
            }
        }

        $divisionIds = Division::where('city_id', $cityId)->pluck('id');
        $carsQuery->whereIn('division_id', $divisionIds);

        if (isset($filters['fuel_type'])) {
            $carsQuery->where('fuel_type', $filters['fuel_type']);
        }
        if (isset($filters['transmission_type'])) {
            $carsQuery->where('transmission_type', $filters['transmission_type']);
        }

        if (isset($filters['brand'])) {
            $carsQuery->where('brand', $filters['brand']);
        }
        if (isset($filters['model'])) {
            $carsQuery->where('model', $filters['model']);
        }
        $carsQuery->where('show_status', 1);

        $cars = $carsQuery->get();

        if (isset($filters['class'])) {
            switch ($filters['class']) {
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
            $cars = $cars->filter(function ($car) use ($class) {
                return $car->tariff->class == $class;
            });
        }


        return response()->json(['cars' => $cars]);
    }

    // private function getTariffsForCars($cars)
    // {
    //     $tariffIds = $cars->pluck('tariff_id')->unique()->toArray();
    //     $tariffs = Tariff::whereIn('id', $tariffIds)->get();
    //     return $tariffs;
    // }
    // private function getRentTermsForCars($cars)
    // {
    //     $rentTermIds  = $cars->pluck('rent_term_id ')->unique()->toArray();
    //     $rentTerms  = RentTerm::whereIn('id', $rentTermIds)->get();
    //     return $rentTerms;
    // }
    // private function getScemasForRentTerms($rentTerms)
    // {
    //     $rentTermIds  = $rentTerms->pluck('id')->unique()->toArray();
    //     $scemas  = Schema::whereIn('rent_term_id', $rentTermIds)->get();
    //     return $scemas;
    // }

    /**
     * Бронирование автомобиля
     *
     * @OA\Post(
     *     path="/auth/cars/booking",
     *     operationId="Booking",
     *     summary="Бронирование автомобиля",
     *     tags={"Cars"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="car_id", type="integer", description="Идентификатор автомобиля, который необходимо забронировать")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Успешное бронирование",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Автомобиль успешно забронирован")
     *         )
     *     ),
     *     @OA\Response(
     *         response="403",
     *         description="Пользователь не зарегистрирован или не верифицирован",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Пользователь не зарегистрирован или не верифицирован")
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Машина не найдена",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Машина не найдена")
     *         )
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request Объект запроса, содержащий идентификатор автомобиля для бронирования
     * @return \Illuminate\Http\JsonResponse JSON-ответ с результатом бронирования
     */
    public function Booking(Request $request)
    {

        $user = $user = Auth::user();
        if ($user->user_status >= 1) {

            $car = Car::where('id', $request->car_id)->first();
            if (!$car) {
                return response()->json(['Машина не найдена'], 404);
            }
            if ($car->show_status !== 0 && $car->user_booked_id === null) {
                $driver = Driver::where('user_id', $user->id)->first();
                $car->user_booked_id = $driver->id;
                $currentDateTime = new \DateTime();
                $uts = $currentDateTime->format('U');
                $currentDateTime->modify('+3 hours');
                $utsUntil = $currentDateTime->format('U');
                $car->booking_time = $uts;
                $car->save();
                $division = Division::where('id', $car->division_id)->first();
                $booking = new Booking();
                $booking->car_id = $car->id;
                $booking->park_id = $division->park_id;
                $booking->booked_at = $uts;
                $booking->booked_until = $utsUntil;
                $booking->driver_id  = $driver->id;
                $booking->save();
                return response()->json(['message' => 'Автомобиль успешно забронирован'], 200);
            }
        } else {
            return response()->json(['message' => 'Пользователь не зарегистрирован или не верифицирован'], 403);
        }
    }

    /**
     * Отмена бронирования автомобиля (аутентифицированный запрос)
     *
     * @OA\Post(
     *     path="/auth/cars/cancel-booking",
     *     operationId="cancelBooking",
     *     summary="Отмена бронирования автомобиля (аутентифицированный запрос)",
     *     tags={"Cars"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="car_id", type="integer", description="Идентификатор автомобиля, для которого необходимо отменить бронирование")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Сообщение об успешной отмене бронирования")
     *         ),
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Ошибка аутентификации",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Требуется аутентификация для выполнения запроса")
     *         )
     *     ),
     *     @OA\Response(
     *         response="403",
     *         description="Ошибка доступа",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="У вас нет разрешения на выполнение этого действия")
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Машина не найдена",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Автомобиль с указанным идентификатором не найден")
     *         )
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Ошибка сервера",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Внутренняя ошибка сервера")
     *         )
     *     )
     * )
     *
     * @param \Illuminate\Http\Request $request Объект запроса, содержащий идентификатор автомобиля для отмены бронирования
     * @return \Illuminate\Http\JsonResponse JSON-ответ с результатом отмены бронирования
     */
    public function cancelBooking(Request $request)
    {
        $user = Auth::user();
        $car = Car::where('id', $request->car_id)->first();

        if (!$car) {
            return response()->json(['message' => 'Машина не найдена'], 404);
        }

        if ($car->user_booked_id !== null && $car->user_booked_id === $user->id) {
            $car->user_booked_id = null;
            $car->booking_time = null;
            $car->save();
            return response()->json(['message' => 'Бронирование автомобиля успешно отменено'], 200);
        } else {
            return response()->json(['message' => 'Вы не можете отменить бронирование данного автомобиля'], 403);
        }
    }
}
