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
use App\Enums\UserStatus;
use App\Enums\FuelType;
use App\Enums\TransmissionType;
use App\Enums\CarStatus;
use App\Http\Controllers\ParserController;
use App\Enums\CarClass;
use Carbon\Carbon;
use App\Http\Controllers\APIController;
use App\Enums\BookingStatus;

class CarsController extends Controller

{

    /**
     * Получение списка автомобилей с учетом фильтров (аутентифицированный запрос)
     *
     * @OA\Post(
     *     path="/cars/search",
     *     operationId="SearchCars",
     *     summary="Получение списка автомобилей с учетом фильтров (аутентифицированный запрос)",
     *     tags={"Cars"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="offset", type="integer", description="Смещение (начальная позиция) для выборки"),
     *             @OA\Property(property="limit", type="integer", description="Максимальное количество записей для выборки"),
     *             @OA\Property(property="city", type="string", description="Название города"),
     *             @OA\Property(property="commission", type="number", description="Комиссия парка"),
     *             @OA\Property(property="fuel_type", type="string", description="Тип топлива",ref="#/components/schemas/FuelType"),
     *             @OA\Property(property="transmission_type", type="string", description="Тип трансмиссии",ref="#/components/schemas/TransmissionType"),
     *             @OA\Property(property="brand", type="array", description="Марка автомобиля",@OA\Items()),
     *             @OA\Property(property="search", type="array", description="Марка или модель автомобиля",@OA\Items()),
     *             @OA\Property(property="sorting", type="string", description="сортировка, asc или desc"),
     *             @OA\Property(property="Schemas", type="object", description="Данные о сроке аренды",
     *                 @OA\Property(property="non_working_days", type="integer", description="Количество нерабочих дней"),
     *                 @OA\Property(property="working_days", type="integer", description="Количество рабочих дней"),
     *             ),
     *             @OA\Property(property="self_employed", type="boolean", description="Работа с самозанятыми"),
     *             @OA\Property(property="is_buyout_possible", type="boolean", description="Возможность выкупа"),
     *             @OA\Property(property="model", type="array", description="Модель автомобиля",@OA\Items()),
     *             @OA\Property(property="car_class", type="array", description="Класс автомобиля (1 - Эконом, 2 - Комфорт, 3 - Комфорт+, 4 - Бизнес)",@OA\Items(ref="#/components/schemas/CarClass"))
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             @OA\Property(property="cars", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", description="Идентификатор автомобиля"),
     *                 @OA\Property(property="fuel_type", type="string", description="Тип топлива",ref="#/components/schemas/FuelType"),
     *                 @OA\Property(property="transmission_type", type="string", description="Тип трансмиссии",ref="#/components/schemas/TransmissionType"),
     *                 @OA\Property(property="brand", type="string", description="Марка автомобиля"),
     *                 @OA\Property(property="model", type="string", description="Модель автомобиля"),
     *                 @OA\Property(property="year_produced", type="integer", description="Год производства"),
     *                 @OA\Property(property="images", type="array", @OA\Items(type="string"), description="Ссылки на изображения"),
     *                 @OA\Property(property="сar_class", type="string", description="Класс тарифа", ref="#/components/schemas/CarClass"),
     *                 @OA\Property(property="park_name", type="string", description="Название парка"),
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
     *                 ),
     *                 @OA\Property(property="about", type="string", description="Описание парка"),
     *                 @OA\Property(property="phone", type="string", description="Телефон парка"),
     *                 @OA\Property(property="commission", type="number", description="Комиссия"),
     *                 @OA\Property(property="self_employed", type="boolean", description="Работа с самозанятыми"),
     *                 @OA\Property(property="city", type="string"),
     *                 @OA\Property(property="division", type="object", description="Данные о подразделении",
     *                     @OA\Property(property="address", type="string", description="Адрес"),
     *                     @OA\Property(property="coords", type="string", description="Координаты подразделения"),
     *                 ),
     *                 @OA\Property(property="rent_term", type="object", description="Данные о сроке аренды",
     *                     @OA\Property(property="deposit_amount_daily", type="number", description="Сумма депозита за день"),
     *                     @OA\Property(property="deposit_amount_total", type="number", description="Общая сумма депозита"),
     *                     @OA\Property(property="minimum_period_days", type="integer", description="Минимальный период в днях"),
     *                     @OA\Property(property="is_buyout_possible", type="boolean", description="Возможность выкупа"),
     *                     @OA\Property(property="schemas", type="array", @OA\Items(
     *                         @OA\Property(property="daily_amount", type="integer", description="Суточная стоимость"),
     *                         @OA\Property(property="non_working_days", type="integer", description="Количество нерабочих дней"),
     *                         @OA\Property(property="working_days", type="integer", description="Количество рабочих дней"),
     *                     )),
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


    public function SearchCars(Request $request)
    {
        $request->validate([
            'offset' => 'required|integer',
            'limit' => 'required|integer',
            'city' => ['required', 'string', 'max:250', 'exists:cities,name'],
        ]);
        $offset = $request->offset;
        $sorting = $request->sorting ?? 'asc';
        $limit = $request->limit;
        $user = Auth::guard('sanctum')->user();
        $fuelType = $request->fuel_type ? FuelType::{$request->fuel_type}()->value : null;
        $transmissionType = $request->transmission_type ? TransmissionType::{$request->transmission_type}()->value : null;
        $city = City::where('name', $request->city)->first();
        $search = $request->search;
        $cityId = $city->id;
        $rentTerm = $request->Schemas;
        if (!$city) {
            return response()->json(['error' => 'Город не найден'], 404);
        }

        $brand = $request->brand;
        $model = $request->model;

        $carClassValues = $request->car_class;
        $translatedValues = [];

        if (count($carClassValues) > 0) {
            foreach ($carClassValues as $key) {
                $keyNew = CarClass::{$key}->value;
                $translatedValues[$key] = $keyNew;
            }
        }
        $translatedValues = array_values($translatedValues);
        $carClass = $translatedValues;
        $selfEmployed = $request->self_employed;
        $isBuyoutPossible = $request->is_buyout_possible;
        $commission = $request->commission;

        $carsQuery = Car::query()->where('status', '!=', 0)
            ->where('rent_term_id', '!=', null)->where('status', CarStatus::AvailableForBooking->value)
            ->whereHas('division', function ($query) use ($cityId) {
                $query->where('city_id', $cityId);
            });
        if ($user && $user->user_status == UserStatus::Verified->value) {
            $driverSpecifications = $user->driver->driverSpecification;
            if ($driverSpecifications) {
                $carsQuery->whereHas('tariff', function ($query) use ($driverSpecifications) {
                    $criminalIds = explode(',', $driverSpecifications->criminal_ids);
                    $criminalIds = array_map('intval', $criminalIds);
                    if (!empty(array_filter($criminalIds, 'is_numeric'))) {
                        $query->whereNotIn('criminal_ids', $criminalIds);
                    }
                    $forbiddenRepublicIds = explode(',', $driverSpecifications->republick_id);
                    $forbiddenRepublicIds = array_map('intval', $forbiddenRepublicIds);
                    if (!empty(array_filter($forbiddenRepublicIds, 'is_numeric'))) {
                        $query->whereNotIn('forbidden_republic_ids', $forbiddenRepublicIds);
                    }
                    $query->where('experience', '<=', $driverSpecifications->experience);
                    $query->where('max_fine_count', '>=', $driverSpecifications->count_seams);
                    $query->where('min_scoring', '<=', $driverSpecifications->scoring);
                    if ($driverSpecifications->has_caused_accident == 1) {
                        $query->where('has_caused_accident', 0);
                    }
                    if ($driverSpecifications->abandoned_car == 1) {
                        $query->where('abandoned_car', 0);
                    }
                    if ($driverSpecifications->has_caused_accident == 1) {
                        $query->where('has_caused_accident', 0);
                    }
                    if ($driverSpecifications->alcohol == 1) {
                        $query->where('alcohol', 0);
                    }
                });
            }
        }
        if ($fuelType) {
            $carsQuery->where('fuel_type', $fuelType);
        }

        if ($transmissionType) {
            $carsQuery->where('transmission_type', $transmissionType);
        }

        if ($brand && count($brand) > 0) {
            $brandArray = is_array($brand) ? $brand : [$brand];
            $carsQuery->whereIn('brand', $brandArray);
        }

        if ($model && count($model) > 0) {
            $modelArray = is_array($model) ? $model : [$model];
            $carsQuery->whereIn('model', $modelArray);
        }
        if ($search) {
            $keywords = explode(' ', $search);
            $carsQuery->where(function ($query) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $query->orWhere('brand', 'like', '%' . str_replace(' ', '%', $keyword) . '%')
                        ->orWhere('model', 'like', '%' . str_replace(' ', '%', $keyword) . '%');
                }
            });
        }
        if (count($carClass) > 0) {
            $carsQuery->whereHas('tariff', function ($query) use ($carClass) {
                $query->whereIn('class', $carClass);
            });
        }
        if ($selfEmployed) {
            $carsQuery->whereHas('division.park', function ($query) use ($selfEmployed) {
                $query->where('self_employed', $selfEmployed);
            });
        }
        if ($commission) {
            $carsQuery->whereHas('division.park', function ($query) use ($commission) {
                $query->where('commission', '<=', $commission);
            });
        }
        if ($isBuyoutPossible) {
            $carsQuery->whereHas('rentTerm', function ($query) use ($isBuyoutPossible) {
                $query->where('is_buyout_possible', $isBuyoutPossible);
            });
        }
        if ($rentTerm) {
            $carsQuery->whereHas('rentTerm.schemas', function ($query) use ($rentTerm) {
                $query->where('non_working_days', $rentTerm['non_working_days'])
                    ->where('working_days', $rentTerm['working_days']);
            });
        }
        $carsQuery->with([
            'division.city' => function ($query) {
                $query->select('id', 'name');
            },
            'tariff' => function ($query) {
                $query->select('id', 'class');
            },
            'rentTerm' => function ($query) {
                $query->select('id', 'deposit_amount_daily', 'deposit_amount_total', 'minimum_period_days', 'is_buyout_possible');
            },
            'rentTerm.schemas' => function ($query) use ($sorting) {
                $query->select('id', 'daily_amount', 'non_working_days', 'working_days', 'rent_term_id')->orderBy('daily_amount', $sorting);
            },
            'division.park' => function ($query) {
                $query->select('id', 'park_name', 'commission', 'self_employed', 'phone', 'about', 'working_hours');
            },
            'division' => function ($query) {
                $query->select('id', 'coords', 'address', 'name', 'park_id', 'city_id');
            }
        ])
            ->select(
                'cars.id',
                'cars.division_id',
                'cars.park_id',
                'cars.tariff_id',
                'cars.rent_term_id',
                'cars.fuel_type',
                'cars.transmission_type',
                'cars.brand',
                'cars.model',
                'cars.year_produced',
                'cars.car_id',
                'cars.images',
            );

        $carsQuery->orderBy(function ($query) use ($sorting) {
            $query->selectRaw('MIN(schemas.daily_amount)')
                ->from('schemas')
                ->whereColumn('schemas.rent_term_id', 'cars.rent_term_id')
                ->orderBy('schemas.daily_amount', $sorting)
                ->limit(1);
        }, $sorting);
        $carsQuery->offset($offset)->limit($limit);
        $cars = $carsQuery->get();

        foreach ($cars as $car) {
            $car['images'] = json_decode($car['images']);
            $car['fuel_type'] = FuelType::from($car['fuel_type'])->name;
            $car['transmission_type'] = TransmissionType::from($car['transmission_type'])->name;
            $classCar = $car['tariff']['class'];
            $end = CarClass::from($classCar)->name;
            $commission = $car['division']['park']['commission'];
            $phone = $car['division']['park']['phone'];
            $about = $car['division']['park']['about'];
            $selfEmployed = $car['division']['park']['self_employed'];
            $workingHours = json_decode($car['division']['park']['working_hours'], true);

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
            if (isset($car['division']['park']['park_name'])) {
                $parkName = $car['division']['park']['park_name'];
            } else {
                $parkName = 'Не удалось получить название парка';
            }
            $city = $car['division']['city']['name'];
            $car['city'] = $city;
            $car['CarClass'] = $end;
            $car['park_name'] = $parkName;
            $car['working_hours'] = $hoursToArray;
            $car['phone'] = $phone;
            $car['self_employed'] = $selfEmployed;
            $car['about'] = $about;
            $car['commission'] = number_format($commission, 2);
        }
        foreach ($cars as $car) {
            unset(
                $car['division']['park'],
                $car['division']['id'],
                $car['tariff'],
                $car['division']['city'],
                $car['division']['name'],
                $car['division']['city_id'],
                $car['division_id'],
                $car['park_id'],
                $car['tariff_id'],
                $car['rent_term_id'],
                $car['car_id'],
                $car['division']['park_id'],
            );
        }
        return response()->json(['cars' => $cars]);
    }



    /**
     * Бронирование автомобиля
     *
     * @OA\Post(
     *     path="/auth/cars/booking",
     *     operationId="booking",
     *     summary="Бронирование автомобиля",
     *     tags={"Cars"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", description="Идентификатор автомобиля, который необходимо забронировать")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Успешное бронирование",
     *         @OA\MediaType(
     *             mediaType="text/plain",
     *             @OA\Schema(
     *                 type="integer",
     *                 example="1708006209"
     *             )
     *         )
     *     ),
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
    public function booking(Request $request)
    {
        $rent_time = 3;
        $user = Auth::guard('sanctum')->user();
        if ($user->user_status !== UserStatus::Verified->value) {
            return response()->json(['message' => 'Пользователь не зарегистрирован или не верифицирован'], 403);
        }
        $car = Car::where('id', $request->id)
            ->with('booking', 'division', 'division.park')->first();
        if (!$car) {
            return response()->json(['message' => 'Машина не найдена'], 404);
        }
        if ($car->status !== CarStatus::AvailableForBooking->value) {
            return response()->json(['message' => 'Машина уже забронирована'], 409);
        }
        $division = $car->division;
        $driver = $user->driver;
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
        $booking->car_id = $request->id;
        $booking->park_id = $division->park_id;
        $booking->booked_at = $currentTime;
        $booking->booked_until = $newEndTime;
        $booking->status = BookingStatus::Booked->value;
        $booking->driver_id = $driver->id;
        $booking->save();
        $car->status = CarStatus::Booked->value;
        $car->save();
        $api = new APIController;
        $api->notifyParkOnBookingStatusChanged($booking->id, true);
        return response()->json($newEndTime, 200);
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
     *             @OA\Property(property="id", type="integer", description="Идентификатор автомобиля, для которого необходимо отменить бронирование")
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
     *         response="409",
     *         description="Машина не забронирована",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Машина не забронирована")
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
        $request->validate([
            'id' => 'required|integer'
        ]);
        $user = Auth::guard('sanctum')->user();
        $car = Car::where('id', $request->id)
            ->with('booking')->first();

        if (!$car) {
            return response()->json(['message' => 'Машина не найдена'], 404);
        }
        if (!$car->status == CarStatus::Booked->value) {
            return response()->json(['message' => 'Машина не забронирована'], 409);
        }
        $booking = $car->booking->first();
        if (!$booking) {
            return response()->json(['message' => 'Машина не забронирована'], 409);
        }

        $booking->status = BookingStatus::UnBooked->value;
        $booking->save();
        $car->status = CarStatus::AvailableForBooking->value;
        $car->save();
        $api = new APIController;
        $api->notifyParkOnBookingStatusChanged($booking->id, false);
        return response()->json(['message' => 'Бронирование автомобиля успешно отменено'], 200);
    }
    // /**
    //  * Получить информацию об автомобиле
    //  *
    //  * @param \Illuminate\Http\Request $request Объект запроса, содержащий идентификатор автомобиля
    //  * @return \Illuminate\Http\JsonResponse JSON-ответ с информацией об автомобиле
    //  *
    //  * @OA\Get(
    //  *     path="/car",
    //  *     operationId="getCar",
    //  *     summary="Получить информацию об автомобиле",
    //  *     tags={"Cars"},
    //  *     @OA\Parameter(
    //  *         name="id",
    //  *         in="query",
    //  *         description="Идентификатор автомобиля",
    //  *         required=true,
    //  *         @OA\Schema(
    //  *             type="integer"
    //  *         )
    //  *     ),
    //  *     @OA\Parameter(
    //  *         name="transmission_type",
    //  *         in="query",
    //  *         description="Тип трансмиссии",
    //  *         required=false,
    //  *         ref="#/components/schemas/TransmissionType"
    //  *     ),
    //  *     @OA\Parameter(
    //  *         name="car_class",
    //  *         in="query",
    //  *         description="Класс автомобиля (1 - Эконом, 2 - Комфорт, 3 - Комфорт+, 4 - Бизнес)",
    //  *         required=false,
    //  *         ref="#/components/schemas/CarClass"),
    //  *     @OA\Response(
    //  *         response="200",
    //  *         description="Успешный ответ",
    //  *         @OA\JsonContent(
    //  *             @OA\Property(
    //  *                 property="car",
    //  *                 type="object",
    //  *                 description="Информация об автомобиле",
    //  *                 @OA\Property(property="id", type="integer"),
    //  *                 @OA\Property(property="fuel_type", type="string", ref="#/components/schemas/FuelType"),
    //  *                 @OA\Property(property="transmission_type", type="string", ref="#/components/schemas/TransmissionType"),
    //  *                 @OA\Property(property="brand", type="string"),
    //  *                 @OA\Property(property="model", type="string"),
    //  *                 @OA\Property(property="year_produced", type="integer"),
    //  *                 @OA\Property(property="images", type="string"),
    //  *                 @OA\Property(property="city", type="string"),
    //  *                 @OA\Property(property="booking_time", type="string", nullable=true),
    //  *                 @OA\Property(property="user_booked_id", type="integer", nullable=true),
    //  *                 @OA\Property(property="CarClass",type="string", ref="#/components/schemas/CarClass"),
    //  *                 @OA\Property(property="rent_term", type="object", description="Данные о сроке аренды",
    //  *                     @OA\Property(property="id", type="integer", description="Идентификатор срока аренды"),
    //  *                     @OA\Property(property="deposit_amount_daily", type="number", description="Сумма депозита за день"),
    //  *                     @OA\Property(property="deposit_amount_total", type="number", description="Общая сумма депозита"),
    //  *                     @OA\Property(property="minimum_period_days", type="integer", description="Минимальный период в днях"),
    //  *                     @OA\Property(property="is_buyout_possible", type="boolean", description="Возможность выкупа"),
    //  *                 ),
    //  *                 @OA\Property(property="schema", type="object", description="Данные о схеме аренды",
    //  *                     @OA\Property(property="id", type="integer", description="Идентификатор схемы аренды"),
    //  *                     @OA\Property(property="rent_term_id", type="integer", description="Идентификатор срока аренды"),
    //  *                     @OA\Property(property="daily_amount", type="integer", description="Суточная стоимость"),
    //  *                     @OA\Property(property="non_working_days", type="integer", description="Количество нерабочих дней"),
    //  *                     @OA\Property(property="working_days", type="integer", description="Количество рабочих дней"),
    //  *                 ),
    //  *                 @OA\Property(
    //  *                     property="division",
    //  *                     type="object",
    //  *                     description="Информация о подразделении",
    //  *                     @OA\Property(property="coords", type="string", nullable=true),
    //  *                     @OA\Property(property="address", type="string", nullable=true),
    //  *                     @OA\Property(property="name", type="string"),
    //  *                     @OA\Property(property="park_name", type="string")
    //  *                 )
    //  *             )
    //  *         )
    //  *     ),
    //  *     @OA\Response(
    //  *         response="404",
    //  *         description="Машина не найдена",
    //  *         @OA\JsonContent(
    //  *             @OA\Property(property="message", type="string", description="Автомобиль с указанным идентификатором не найден")
    //  *         )
    //  *     )
    //  * )
    //  */
    // public function GetCar(Request $request)
    // {
    //     $request->validate([
    //         'id' => 'required|integer',
    //     ]);
    //     $car = Car::where('id', $request->id)->with([
    //         'division.city' => function ($query) {
    //             $query->select('id', 'name');
    //         },
    //         'tariff' => function ($query) {
    //             $query->select('id', 'class');
    //         },
    //         'rentTerm' => function ($query) {
    //             $query->select('id', 'deposit_amount_daily', 'deposit_amount_total', 'minimum_period_days', 'is_buyout_possible');
    //         },
    //         'rentTerm.schemas' => function ($query) {
    //             $query->select('id', 'daily_amount', 'non_working_days', 'working_days', 'rent_term_id');
    //         },
    //         'division.park' => function ($query) {
    //             $query->select('id', 'park_name');
    //         },
    //         'division' => function ($query) {
    //             $query->select('id', 'coords', 'address', 'name', 'park_id', 'city_id');
    //         }
    //     ])
    //         ->select(
    //             'cars.id',
    //             'cars.division_id',
    //             'cars.park_id',
    //             'cars.tariff_id',
    //             'cars.rent_term_id',
    //             'cars.fuel_type',
    //             'cars.transmission_type',
    //             'cars.brand',
    //             'cars.model',
    //             'cars.year_produced',
    //             'cars.car_id',
    //             'cars.images',
    //             'booking_time',
    //             'user_booked_id'
    //         )->first();

    //     $car['images'] = json_decode($car['images']);
    //     $car['fuel_type'] = FuelType::from($car['fuel_type'])->name;
    //     $car['transmission_type'] = TransmissionType::from($car['transmission_type'])->name;
    //     $end = $this->tarifEng($car['tariff']['class']);
    //     $car['tariff']['class'] =  $end;
    //     $parkName = $car['division']['park']['park_name'];
    //     $city = $car['division']['city']['name'];
    //     c
    //         $car['division']['park'],
    //         $car['division']['park_id'],
    //         $car['division']['id'],
    //         $car['tariff'],
    //         $car['division_id'],
    //         $car['park_id'],
    //         $car['tariff_id'],
    //         $car['rent_term_id'],
    //         $car['car_id'],
    //     );
    //     $car->CarClass = $end;
    //     $car->city = $city;
    //     $car->park_name = $parkName;
    //     if (!$car) {
    //         return response()->json(['message' => 'Машина не найдена'], 404);
    //     }

    //     return response()->json(['car' => $car]);
    // }



    //    /**
    //  * Получение списка автомобилей по поиску
    //  *
    //  * @OA\Get(
    //  *     path="/cars/search",
    //  *     operationId="SearchCars",
    //  *     summary="Получение списка автомобилей по поиску",
    //  *     tags={"Cars"},
    //  *     security={{"bearerAuth": {}}},
    //  *     @OA\Parameter(
    //  *         name="limit",
    //  *         in="query",
    //  *         description="Максимальное количество записей для выборки",
    //  *         required=false,
    //  *         @OA\Schema(type="integer")
    //  *     ),
    //  *     @OA\Parameter(
    //  *         name="city",
    //  *         in="query",
    //  *         description="Название города",
    //  *         required=true,
    //  *         @OA\Schema(type="string")
    //  *     ),
    //  *     @OA\Parameter(
    //  *         name="search",
    //  *         in="query",
    //  *         description="Модель или марка автомобиля",
    //  *         required=true,
    //  *         @OA\Schema(type="string")
    //  *     ),
    //  *     @OA\Response(
    //  *         response="200",
    //  *         description="Успешный ответ",
    //  *         @OA\JsonContent(
    //  *             @OA\Property(property="cars", type="array", @OA\Items(
    //  *                 @OA\Property(property="id", type="integer", description="Идентификатор автомобиля"),
    //  *                 @OA\Property(property="fuel_type", type="string", description="Тип топлива",ref="#/components/schemas/FuelType"),
    //  *                 @OA\Property(property="transmission_type", type="string", description="Тип трансмиссии",ref="#/components/schemas/TransmissionType"),
    //  *                 @OA\Property(property="brand", type="string", description="Марка автомобиля"),
    //  *                 @OA\Property(property="model", type="string", description="Модель автомобиля"),
    //  *                 @OA\Property(property="year_produced", type="integer", description="Год производства"),
    //  *                 @OA\Property(property="images", type="array", @OA\Items(type="string"), description="Ссылки на изображения"),
    //  *                 @OA\Property(property="CarClass", type="string", description="Класс тарифа",ref="#/components/schemas/CarClass"),
    //  *                 @OA\Property(property="park_name", type="string", description="Название парка"),
    //  *                 @OA\Property(property="city", type="string"),
    //  *                 @OA\Property(property="division", type="object", description="Данные о подразделении",
    //  *                     @OA\Property(property="name", type="string", description="Название подразделения"),
    //  *                 ),
    //  *                 @OA\Property(property="rent_term", type="object", description="Данные о сроке аренды",
    //  *                     @OA\Property(property="deposit_amount_daily", type="number", description="Сумма депозита за день"),
    //  *                     @OA\Property(property="deposit_amount_total", type="number", description="Общая сумма депозита"),
    //  *                     @OA\Property(property="minimum_period_days", type="integer", description="Минимальный период в днях"),
    //  *                     @OA\Property(property="is_buyout_possible", type="boolean", description="Возможность выкупа"),
    //  *                     @OA\Property(property="schemas", type="array", @OA\Items(
    //  *                         @OA\Property(property="daily_amount", type="integer", description="Суточная стоимость"),
    //  *                         @OA\Property(property="non_working_days", type="integer", description="Количество нерабочих дней"),
    //  *                         @OA\Property(property="working_days", type="integer", description="Количество рабочих дней"),
    //  *                     )),
    //  *                 ),
    //  *             )),
    //  *         ),
    //  *     ),
    //  *     @OA\Response(response="401", description="Ошибка аутентификации"),
    //  *     @OA\Response(response="422", description="Ошибки валидации"),
    //  *     @OA\Response(response="500", description="Ошибка сервера"),
    //  * )
    //  * @param \Illuminate\Http\Request $request
    //  * @return \Illuminate\Http\JsonResponse
    //  */


    //  public function SearchCars(Request $request)
    //  {
    //      $request->validate([
    //          'search'=>'required|string',
    //          'city' => ['required', 'string', 'max:250', 'exists:cities,name'],
    //          'limit'=>'integer'
    //      ]);
    //      $limit = $request->limit;
    //      $user = Auth::guard('sanctum')->user();
    //      $city = City::where('name',$request->city)->first();
    //      $cityId = $city->id;
    //      if (!$city) {
    //          return response()->json(['error' => 'Город не найден'], 404);
    //      }

    //      $search = $request->search;

    //      $carsQuery = Car::query()->where('status','!=',CarStatus::Hidden->value)->doesntHave('booking')->where('rent_term_id','!=',null)->where('price','!=',null)
    //      ->whereHas('division', function($query) use ($cityId) {
    //          $query->where('city_id', $cityId);
    //      });
    //      if ($user && $user->user_status == UserStatus::Verified->value) {
    //          $driverSpecifications = $user->driver->driverSpecification;
    //          if ($driverSpecifications) {
    //              $carsQuery->whereHas('tariff', function($query) use ($driverSpecifications) {
    //                  $criminalIds = explode(',', $driverSpecifications->criminal_ids);
    //          $criminalIds = array_map('intval', $criminalIds);
    //          if (!empty(array_filter($criminalIds, 'is_numeric'))) {
    //              $query->whereNotIn('criminal_ids', $criminalIds);
    //          }
    //          $forbiddenRepublicIds = explode(',', $driverSpecifications->republick_id);
    //          $forbiddenRepublicIds = array_map('intval', $forbiddenRepublicIds);
    //          if (!empty(array_filter($forbiddenRepublicIds, 'is_numeric'))) {
    //              $query->whereNotIn('forbidden_republic_ids', $forbiddenRepublicIds);
    //          }
    //                  $query->where('experience', '<=', $driverSpecifications->experience);
    //                  $query->where('max_fine_count', '>=', $driverSpecifications->count_seams);
    //                  $query->where('min_scoring', '<=', $driverSpecifications->scoring);
    //                  if ($driverSpecifications->has_caused_accident == 1) {
    //                      $query->where('has_caused_accident', 0);
    //                  }
    //                  if ($driverSpecifications->abandoned_car == 1) {
    //                      $query->where('abandoned_car', 0);
    //                  }
    //                  if ($driverSpecifications->has_caused_accident == 1) {
    //                      $query->where('has_caused_accident', 0);
    //                  }
    //                  if ($driverSpecifications->alcohol == 1) {
    //                      $query->where('alcohol', 0);
    //                  }
    //              });
    //          }
    //      }

    //      if ($search) {
    //         $keywords = explode(' ', $search);
    //         $carsQuery->where(function($query) use ($keywords) {
    //             foreach ($keywords as $keyword) {
    //                 $query->orWhere('brand', 'like', '%' . str_replace(' ', '%', $keyword) . '%')
    //                       ->orWhere('model', 'like', '%' . str_replace(' ', '%', $keyword) . '%');
    //             }
    //         });
    //     }

    //      $carsQuery->with([
    //          'division.city' => function($query) {
    //              $query->select('id', 'name');
    //          },
    //          'tariff' => function($query) {
    //              $query->select('id', 'class');
    //          },
    //          'rentTerm' => function($query) {
    //              $query->select('id', 'deposit_amount_daily', 'deposit_amount_total', 'minimum_period_days', 'is_buyout_possible');
    //          },
    //          'rentTerm.schemas' => function($query) {
    //              $query->select('id', 'daily_amount', 'non_working_days', 'working_days','rent_term_id');
    //          },
    //          'division.park' => function($query) {
    //              $query->select('id', 'park_name');
    //          },
    //          'division' => function($query) {
    //              $query->select('id', 'coords', 'address', 'name','park_id','city_id');
    //          }
    //      ])
    //      ->select(
    //          'cars.id',
    //          'cars.division_id',
    //          'cars.park_id',
    //          'cars.tariff_id',
    //          'cars.rent_term_id',
    //          'cars.fuel_type',
    //          'cars.transmission_type',
    //          'cars.brand',
    //          'cars.model',
    //          'cars.year_produced',
    //          'cars.car_id',
    //          'cars.images',
    //      );
    //          $car = $carsQuery->orderBy('price', 'asc')->first();
    // if ($limit) {
    //     $carsQuery->limit($limit);
    // }
    //  $cars = $carsQuery->get();

    //  foreach ($cars as $car) {
    //      $car['images'] = json_decode($car['images']);
    //      $car['fuel_type'] = FuelType::from($car['fuel_type'])->name;
    //      $car['transmission_type'] = TransmissionType::from($car['transmission_type'])->name;
    //      $end =$this->tarifEng($car['tariff']['class']);
    //      $car['tariff']['class'] =  $end;
    //      $parkName = $car['division']['park']['park_name'];
    //      $city = $car['division']['city']['name'];
    //      unset(
    //          $car['division']['park'],
    //          $car['division']['park_id'],
    //          $car['division']['id'],
    //          $car['tariff'],
    //          $car['division']['city'],
    //          $car['division_id'],
    //          $car['park_id'],
    //          $car['tariff_id'],
    //          $car['rent_term_id'],
    //          $car['car_id'],
    //      );
    //      $car->city= $city;
    //      $car->CarClass= $end;
    //      $car->park_name= $parkName;
    //  }


    //  return response()->json(['cars' => $cars]);
    // }
}
