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
use Illuminate\Support\Facades\Auth;

class CarsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/cars",
     *     summary="Получение списка автомобилей с учетом фильтров",
     *     tags={"Cars"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="city", type="string", description="Название города"),
     *             @OA\Property(property="fuel_type", type="string", description="Тип топлива"),
     *             @OA\Property(property="transmission_type", type="string", description="Тип трансмиссии"),
     *             @OA\Property(property="brand", type="string", description="Марка автомобиля"),
     *             @OA\Property(property="model", type="string", description="Модель автомобиля"),
     *             @OA\Property(property="class", type="integer", description="Класс автомобиля (1 - Эконом, 2 - Комфорт, 3 - Комфорт+, 4 - Бизнес)"),
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             @OA\Property(property="cars", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", description="Идентификатор автомобиля"),
     *                 @OA\Property(property="brand", type="string", description="Марка автомобиля"),
     *                 @OA\Property(property="model", type="string", description="Модель автомобиля"),
     *                 @OA\Property(property="tariff_id", type="integer", description="Идентификатор тарифа"),
     *                 @OA\Property(property="fuel_type", type="string", description="Тип топлива"),
     *                 @OA\Property(property="transmission_type", type="string", description="Тип трансмиссии"),
     *                 @OA\Property(property="show_status", type="integer", description="Статус отображения"),
     *             )),
     *         ),
     *     ),
     *     @OA\Response(response="401", description="Ошибка аутентификации"),
     *     @OA\Response(response="422", description="Ошибки валидации"),
     *     @OA\Response(response="500", description="Ошибка сервера"),
     * )
     */
    public function AuthGetCars(Request $request) //чисто для сваггера
    {
    }
    /**
     * @OA\Get(
     *     path="/auth/cars",
     *     summary="Получение списка автомобилей с учетом фильтров (аутентифицированный запрос)",
     *     tags={"Cars"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="city", type="string", description="Название города"),
     *             @OA\Property(property="fuel_type", type="string", description="Тип топлива"),
     *             @OA\Property(property="transmission_type", type="string", description="Тип трансмиссии"),
     *             @OA\Property(property="brand", type="string", description="Марка автомобиля"),
     *             @OA\Property(property="model", type="string", description="Модель автомобиля"),
     *             @OA\Property(property="class", type="integer", description="Класс автомобиля (1 - Эконом, 2 - Комфорт, 3 - Комфорт+, 4 - Бизнес)"),
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             @OA\Property(property="cars", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", description="Идентификатор автомобиля"),
     *                 @OA\Property(property="brand", type="string", description="Марка автомобиля"),
     *                 @OA\Property(property="model", type="string", description="Модель автомобиля"),
     *                 @OA\Property(property="tariff_id", type="integer", description="Идентификатор тарифа"),
     *                 @OA\Property(property="fuel_type", type="string", description="Тип топлива"),
     *                 @OA\Property(property="transmission_type", type="string", description="Тип трансмиссии"),
     *                 @OA\Property(property="show_status", type="integer", description="Статус отображения"),
     *             )),
     *         ),
     *     ),
     *     @OA\Response(response="401", description="Ошибка аутентификации"),
     *     @OA\Response(response="422", description="Ошибки валидации"),
     *     @OA\Response(response="500", description="Ошибка сервера"),
     * )
     */
    public function getCars(Request $request)
    {
        $filters = $request->all();
        $cityName = $filters['city'] ?? null;
        $cityId = City::where('name', $cityName)->value('id');

        $carsQuery = Car::query();
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
    /**
     * @OA\Post(
     *     path="/auth/cars/booking",
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
     * @OA\Post(
     *     path="/auth/cars/cancel-booking",
     *     summary="Отмена бронирования автомобиля (аутентифицированный запрос)",
     *     tags={"Cars"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="car_id", type="integer", description="Идентификатор автомобиля")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Сообщение об успешной отмене бронирования")
     *         ),
     *     ),
     *     @OA\Response(response="401", description="Ошибка аутентификации"),
     *     @OA\Response(response="403", description="Ошибка доступа"),
     *     @OA\Response(response="404", description="Машина не найдена"),
     *     @OA\Response(response="500", description="Ошибка сервера"),
     * )
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
