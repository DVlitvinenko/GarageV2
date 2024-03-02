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
     *             @OA\Property(property="car_vin", type="string", description="VIN авто"),
     *             @OA\Property(property="Schemas", type="object", description="Данные о сроке аренды",
     *                 @OA\Property(property="non_working_days", type="integer", description="Количество нерабочих дней"),
     *                 @OA\Property(property="working_days", type="integer", description="Количество рабочих дней"),
     *             ),
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
     *                 @OA\Property(property="variants", type="array", @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="images", type="array", @OA\Items(type="string"), description="Ссылки на изображения")
     *                     )),
     *                 @OA\Property(
     *                     property="working_hours",
     *                     type="array",
     *                     description="Расписание работы парка",
     *     @OA\Items(
     *         type="object",
     *         @OA\Property(property="day", type="string", description="День недели на русском"),
     *         @OA\Property(property="start", type="object", description="Время начала",
     *             @OA\Property(property="hours", type="integer", description="Часы (0-23)"),
     *             @OA\Property(property="minutes", type="integer", description="Минуты (0-59)")
     *         ),
     *         @OA\Property(property="end", type="object", description="Время окончания",
     *             @OA\Property(property="hours", type="integer", description="Часы (0-23)"),
     *             @OA\Property(property="minutes", type="integer", description="Минуты (0-59)")
     *         )
     *     )
     * ),
     *                 @OA\Property(property="about", type="string", description="Описание парка"),
     *                 @OA\Property(property="phone", type="string", description="Телефон парка"),
     *                 @OA\Property(property="commission", type="number", description="Комиссия"),
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
     *                         @OA\Property(property="id", type="integer"),
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
        $fuelType = $request->fuel_type ? FuelType::{$request->fuel_type}->value : null;
        $transmissionType = $request->transmission_type ? TransmissionType::{$request->transmission_type}->value : null;
        $city = City::where('name', $request->city)->first();
        $search = $request->search;
        $carVin = $request->car_vin;
        $cityId = $city->id;
        $rentTerm = $request->Schemas;
        if (!$city) {
            return response()->json(['error' => 'Город не найден'], 404);
        }

        $brand = $request->brand;
        $model = $request->model;

        $carClassValues = $request->car_class ? $request->car_class : [];
        $translatedValues = [];

        if (count($carClassValues) > 0) {
            foreach ($carClassValues as $key) {
                $keyNew = CarClass::{$key}->value;
                $translatedValues[$key] = $keyNew;
            }
        }
        $translatedValues = array_values($translatedValues);
        $carClass = $translatedValues;
        $isBuyoutPossible = $request->is_buyout_possible;
        $commission = $request->commission;

        $carsQuery = Car::query()->where('status', '!=', 0)
            ->where('rent_term_id', '!=', null)->where('status', CarStatus::AvailableForBooking->value)
            ->whereHas('division', function ($query) use ($cityId) {
                $query->where('city_id', $cityId);
            });

        // отключена проверка по спецификации!
        // if ($user && $user->user_status == UserStatus::Verified->value) {
        //     $driverSpecifications = $user->driver->driverSpecification;
        //     if ($driverSpecifications) {
        //         $carsQuery->whereHas('tariff', function ($query) use ($driverSpecifications) {
        //             $criminalIds = explode(',', $driverSpecifications->criminal_ids);
        //             $criminalIds = array_map('intval', $criminalIds);
        //             if (!empty(array_filter($criminalIds, 'is_numeric'))) {
        //                 $query->whereNotIn('criminal_ids', $criminalIds);
        //             }
        //             if ($driverSpecifications->is_north_caucasus) {
        //                 $query->where('is_north_caucasus', 0);
        //             }
        //             $query->where('experience', '<=', $driverSpecifications->experience);
        //             $query->where('max_fine_count', '>=', $driverSpecifications->fine_count);
        //             $query->where('min_scoring', '<=', $driverSpecifications->scoring);
        //             if ($driverSpecifications->has_caused_accident == 1) {
        //                 $query->where('has_caused_accident', 0);
        //             }
        //             if ($driverSpecifications->abandoned_car == 1) {
        //                 $query->where('abandoned_car', 0);
        //             }
        //             if ($driverSpecifications->has_caused_accident == 1) {
        //                 $query->where('has_caused_accident', 0);
        //             }
        //             if ($driverSpecifications->alcohol == 1) {
        //                 $query->where('alcohol', 0);
        //             }
        //         });
        //     }
        // }
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
        if ($carVin) {
            $carsQuery->where(function ($query) use ($carVin) {
                $query->where('car_id', 'like', '%' . $carVin . '%');
            });
        }
        if (count($carClass) > 0) {
            $carsQuery->whereHas('tariff', function ($query) use ($carClass) {
                $query->whereIn('class', $carClass);
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
                $query->select('id', 'park_name', 'commission', 'phone', 'about');
            },
            'division' => function ($query) {
                $query->select('id', 'coords', 'address', 'name', 'park_id', 'city_id', 'working_hours');
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
        $uniqueCars = $cars->unique(function ($item) {
            return $item->division_id . $item->park_id . $item->tariff_id . $item->rent_term_id .
                $item->fuel_type . $item->transmission_type . $item->brand . $item->model . $item->year_produced;
        });

        $similarCars = Car::where(function ($query) use ($uniqueCars) {
            foreach ($uniqueCars as $uniqueCar) {
                $query->orWhere(function ($subQuery) use ($uniqueCar) {
                    $subQuery->where('division_id', $uniqueCar->division_id)
                        ->where('park_id', $uniqueCar->park_id)
                        ->where('tariff_id', $uniqueCar->tariff_id)
                        ->where('rent_term_id', $uniqueCar->rent_term_id)
                        ->where('fuel_type', $uniqueCar->fuel_type)
                        ->where('transmission_type', $uniqueCar->transmission_type)
                        ->where('brand', $uniqueCar->brand)
                        ->where('model', $uniqueCar->model)
                        ->where('year_produced', $uniqueCar->year_produced);
                });
            }
        })->get();

        foreach ($uniqueCars as $car) {
            $car['variants'] = $similarCars->filter(function ($similarCar) use ($car) {
                return $similarCar->division_id == $car->division_id &&
                       $similarCar->park_id == $car->park_id &&
                       $similarCar->tariff_id == $car->tariff_id &&
                       $similarCar->rent_term_id == $car->rent_term_id &&
                       $similarCar->fuel_type == $car->fuel_type &&
                       $similarCar->transmission_type == $car->transmission_type &&
                       $similarCar->brand == $car->brand &&
                       $similarCar->model == $car->model &&
                       $similarCar->year_produced == $car->year_produced;
            })->map(function ($similarCar) {
                return [
                    'id' => $similarCar->id,
                    'images' => json_decode($similarCar->images)
                ];
            })->values()->all();}
            $formattedCars = [];
            foreach ($uniqueCars as $car) {
                $formattedCar = $car;
                $formattedCar['images'] = json_decode($car['images']);
                $formattedCar['fuel_type'] = FuelType::from($car['fuel_type'])->name;
                $formattedCar['transmission_type'] = TransmissionType::from($car['transmission_type'])->name;
                $classCar = $car['tariff']['class'];
                $end = CarClass::from($classCar)->name;
                $commission = $car['division']['park']['commission'];
                $phone = $car['division']['park']['phone'];
                $about = $car['division']['park']['about'];
                $workingHours = json_decode($car['division']['working_hours'], true);
                $parkName = $car['division']['park']['park_name'] ?? 'Не удалось получить название парка';
                $city = $car['division']['city']['name'];
                $formattedCar['city'] = $city;
                $formattedCar['CarClass'] = $end;
                $formattedCar['park_name'] = $parkName;
                $formattedCar['working_hours'] = $workingHours;
                $formattedCar['phone'] = $phone;
                $formattedCar['about'] = $about;
                $commissionFormatted = number_format($commission, 2);
                $formattedCar['commission'] = rtrim(rtrim($commissionFormatted, '0'), '.');

                $formattedCars[] = $formattedCar;
            }

        foreach ($formattedCars as $car) {
            unset(
                $car['division']['park'],
                $car['division']['id'],
                $car['tariff'],
                $car['division']['city'],
                $car['division']['name'],
                $car['division']['city_id'],
                $car['division']['working_hours'],
                $car['division_id'],
                $car['park_id'],
                $car['tariff_id'],
                $car['rent_term_id'],
                $car['car_id'],
                $car['division']['park_id'],
            );
        }
        return response()->json(['cars' => $formattedCars]);
    }



    /**
     * Бронирование автомобиля
     *
     * @OA\Post(
     *     path="/auth/cars/booking",
     *     operationId="Book",
     *     summary="Бронирование автомобиля",
     *     tags={"Cars"},
     *     security={{"bearerAuth": {}}},
     * @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *         @OA\Property(property="id", type="integer", description="Идентификатор машины"),
     *         @OA\Property(property="schema_id", type="integer", description="Идентификатор схемы аренды"),
     *     ),
     * ),
     * @OA\Response(
     *     response="200",
     *     description="Успешное бронирование",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             @OA\Property(property="booking", type="object",
     *                 @OA\Property(property="status", type="string",ref="#/components/schemas/BookingStatus"),
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="start_date", type="string"),
     *                 @OA\Property(property="end_date", type="string"),
     *                 @OA\Property(property="car", type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="fuel_type", type="string", description="Тип топлива",ref="#/components/schemas/FuelType"),
     *                     @OA\Property(property="transmission_type", type="string", description="Тип трансмиссии",ref="#/components/schemas/TransmissionType"),
     *                     @OA\Property(property="brand", type="string"),
     *                     @OA\Property(property="model", type="string"),
     *                     @OA\Property(property="year_produced", type="integer"),
     *                     @OA\Property(property="images", type="array", @OA\Items(type="string"), description="Ссылки на изображения"),
     *                     @OA\Property(property="сar_class", type="string", description="Класс тарифа", ref="#/components/schemas/CarClass"),
     *                     @OA\Property(property="division", type="object",
     *                         @OA\Property(property="coords", type="string"),
     *                         @OA\Property(property="address", type="string"),
     *             @OA\Property(
     *                 property="working_hours",
     *                 type="array",
     *                 description="Расписание работы парка",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="day", type="string", description="День недели на английском",ref="#/components/schemas/DayOfWeek"),
     *                     @OA\Property(
     *                         property="start",
     *                         type="object",
     *                         description="Время начала работы",
     *                         @OA\Property(property="hours", type="integer", description="Часы (0-23)"),
     *                         @OA\Property(property="minutes", type="integer", description="Минуты (0-59)")
     *                     ),
     *                     @OA\Property(
     *                         property="end",
     *                         type="object",
     *                         description="Время окончания работы",
     *                         @OA\Property(property="hours", type="integer", description="Часы (0-23)"),
     *                         @OA\Property(property="minutes", type="integer", description="Минуты (0-59)")
     *                     )
     *                 )
     *             ),
     *                         @OA\Property(property="park", type="object",
     *                             @OA\Property(property="phone", type="string"),
     *                             @OA\Property(property="url", type="string"),
     *                             @OA\Property(property="commission", type="integer"),
     *                             @OA\Property(property="park_name", type="string"),
     *                             @OA\Property(property="about", type="string")
     *                         ),
     *                     ),
     *                 ),
     *                 @OA\Property(property="rent_term", type="object",
     *                     @OA\Property(property="deposit_amount_daily", type="number"),
     *                     @OA\Property(property="deposit_amount_total", type="number"),
     *                     @OA\Property(property="minimum_period_days", type="integer"),
     *                     @OA\Property(property="is_buyout_possible", type="integer"),
     *                     @OA\Property(property="schemas", type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="daily_amount", type="integer"),
     *                             @OA\Property(property="non_working_days", type="integer"),
     *                             @OA\Property(property="working_days", type="integer"),
     *                         ),
     *                     ),
     *                 ),
     *             ),
     *         ),
     *     ),
     * ),
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
    public function Book(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'schema_id' => 'required|integer'
        ]);
        $rent_time = 3;
        $user = Auth::guard('sanctum')->user();

        // Проверка статуса пользователя
        // if ($user->user_status !== UserStatus::Verified->value) {
        //     return response()->json(['message' => 'Пользователь не зарегистрирован или не верифицирован'], 403);
        // }

$schema = Schema::where('id', $request->schema_id)->first();
if(!$schema){ return response()->json(['message' => 'Схема аренды не найдена'], 404);
}
        $car = Car::where('id', $request->id)->with('booking', 'division', 'division.park')->first();

        if (!$car) {
            return response()->json(['message' => 'Машина не найдена'], 404);
        }

        if ($car->status !== CarStatus::AvailableForBooking->value) {
            return response()->json(['message' => 'Машина уже забронирована'], 409);
        }

        $checkBook = Booking::where('driver_id', $user->driver->id)
            ->where('status', BookingStatus::Booked->value)
            ->first();

        if ($checkBook) {
            return response()->json(['message' => 'У пользователя уже есть активная бронь!'], 409);
        }

        $division = $car->division;
        $driver = $user->driver;

        //date_default_timezone_set('UTC');
        $workingHours = json_decode($division->working_hours, true);
        $divisionTimezone = $division->timezone_difference;
        $currentDayOfWeek = Carbon::now()->format('l');
        $currentTime = Carbon::now();
        $isNonWorkingDayToday = false;
        $todayWorkingHours = null;

        foreach ($workingHours as $workingDay) {
            if ($workingDay['day'] === $currentDayOfWeek) {
                $todayWorkingHours = $workingDay;
                break;
            }
        }
        if(!$todayWorkingHours) {
            $isNonWorkingDayToday = true;
        }
        if($todayWorkingHours) {
            $endTimeOfWorkDayToday = Carbon::createFromTime($todayWorkingHours['end']['hours'], $todayWorkingHours['end']['minutes'], 0)->addHours(-$rent_time-$divisionTimezone);
            $startTimeOfWorkDayToday = Carbon::createFromTime($todayWorkingHours['start']['hours'], $todayWorkingHours['start']['minutes'], 0)->addHours(-$rent_time-$divisionTimezone);
        }else{$endTimeOfWorkDayToday=null;$startTimeOfWorkDayToday=null;}

        if ($endTimeOfWorkDayToday && $startTimeOfWorkDayToday && (($endTimeOfWorkDayToday < $currentTime && $currentTime > $startTimeOfWorkDayToday) || $isNonWorkingDayToday)) {

            $nextWorkingDayInfo = $this->findNextWorkingDay(Carbon::now()->format('l'), $workingHours);
            $nextWorkingDay = Carbon::now()->next($nextWorkingDayInfo['day']);

            $startTimeOfWorkDayTomorrow = Carbon::create($nextWorkingDay->year, $nextWorkingDay->month, $nextWorkingDay->day, $nextWorkingDayInfo['start']['hours'], $nextWorkingDayInfo['start']['minutes'], 0);
            $newEndTime = $startTimeOfWorkDayTomorrow->addHours($rent_time);
        } elseif ($currentTime < $startTimeOfWorkDayToday) {
            $newEndTime = $startTimeOfWorkDayToday->addHours($rent_time)->addHours($rent_time);
        } else {
            $newEndTime = $currentTime->addHours($rent_time);
        }
        $booking = new Booking();
        $booking->car_id = $request->id;
        $booking->park_id = $division->park_id;
        $booking->schema_id = $schema->id;
        $booking->booked_at = Carbon::now()->toIso8601ZuluString();
        $booking->booked_until = Carbon::parse($newEndTime)->toIso8601ZuluString();
        $booking->status = BookingStatus::Booked->value;
        $booking->driver_id = $driver->id;
        $booking->save();

        $car->status = CarStatus::Booked->value;
        $car->save();

        $workingHours = json_decode($car->division->working_hours, true);


        $car->division->working_hours = $workingHours;
        $booked = $booking;
        $booked->status = BookingStatus::from($booked->status)->name;
        $booked->start_date = $booked->booked_at;
        $booked->end_date = $booked->booked_until;
        $booked->car = $car;
        $booked->car->сar_class = CarClass::from($car->tariff->class)->name;
        $booked->car->transmission_type = TransmissionType::from($booked->car->transmission_type)->name;
        $booked->car->images = json_decode($booked->car->images);
        $booked->car->fuel_type = FuelType::from($booked->car->fuel_type)->name;
        $booked->rent_term = RentTerm::where('id', $car->rent_term_id)
    ->with(['schemas' => function ($query) use ($booked) {
        $query->where('id', $booked->schema_id);
    }])
    ->select('deposit_amount_daily', 'deposit_amount_total', 'minimum_period_days', 'is_buyout_possible', 'id')
    ->first();
        unset(
            $booked->created_at,
            $booked->updated_at,
            $booked->booked_at,
            $booked->booked_until,
            $booked->park_id,
            $booked->driver_id,
            $car->booking,
            $car->created_at,
            $car->updated_at,
            $car->status,
            $car->car_id,
            $car->division_id,
            $car->park_id,
            $car->tariff_id,
            $car->rent_term_id,
            $car->division->id,
            $car->division->park_id,
            $car->division->city_id,
            $car->division->created_at,
            $car->division->updated_at,
            $car->division->name,
            $car->division->park->id,
            $car->division->park->id,
            $car->division->park->API_key,
            $car->division->park->created_at,
            $car->division->park->updated_at,
            $car->tariff,
            $car->division->park->created_at,
            $booked->rent_term->id
        );
        foreach ($booked->rent_term->schemas as $schema) {
            unset($schema->created_at, $schema->updated_at, $schema->id, $schema->rent_term_id);
        }

        $api = new APIController;
        $api->notifyParkOnBookingStatusChanged($booking->id, true,$schema);

        return response()->json(['booking' => $booking], 200);
    }

    private function findNextWorkingDay($currentDay, $workingHours)
    {
        $nextDay = Carbon::now()->addDay();
        for ($i = 0; $i < 7; $i++) {
            $nextDayName = $nextDay->format('l');
            $nextDayInfo = collect($workingHours)->firstWhere('day', $nextDayName);
            if ($nextDayInfo) {
                return $nextDayInfo;
            }
            $nextDay->addDay();
        }
        return null;
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
        $booking = Booking::where('id', $request->id)->with('car')->first();
        if (!$booking) {
            return response()->json(['message' => 'Бронь не найдена'], 404);
        }
        if ($booking->status !== BookingStatus::Booked->value) {
            return response()->json(['message' => 'Статус не "забронирован"'], 409);
        }
        //date_default_timezone_set('UTC');
        $car = $booking->car;
        $booking->booked_until = Carbon::now()->toIso8601ZuluString();
        $booking->status = BookingStatus::UnBooked->value;
        $booking->save();
        $car->status = CarStatus::AvailableForBooking->value;
        $car->save();
        $api = new APIController;
        $api->notifyParkOnBookingStatusChanged($booking->id, false);
        return response()->json(['message' => 'Бронирование автомобиля успешно отменено'], 200);
    }

    /**
     * Показать список брендов
     *
     * @OA\Post(
     *     path="/cars/brand-list",
     *     operationId="GetBrandList",
     *     summary="Показать список брендов",
     *     tags={"Cars"},
     *     @OA\Response(
     *         response="200",
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     * @OA\Property(property="brands", type="array",@OA\Items(type="string"), description="Список брендов"),
     *     )),
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
    public function GetBrandList()
    {
        $brandList = Car::select('brand')->distinct()->orderBy('brand', 'asc')->get()->pluck('brand')->toArray();
        return response()->json(['brands' => $brandList]);
    }
}

