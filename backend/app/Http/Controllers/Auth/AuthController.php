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
    // public function register(Request $request)
    // {
    //     $request->validate([
    //         // 'name' => ['required', 'string', 'max:40', 'regex:/^[А-Яа-яЁё\s]+$/u'],
    //         'captcha_token' => ['nullable'],
    //         'phone' => ['required', 'string', 'max:255', 'unique:users'],
    //         // 'email' => ['string', 'email', 'max:255', 'unique:users'],
    //         // 'password' => ['required', 'string', 'min:8', 'confirmed'],
    //         // 'city' => ['required', 'string', 'max:250', function ($attribute, $value, $fail) {
    //         //     $parser = new ParserController();
    //         //     if (!$parser->parseCity($value)) {
    //         //         $fail('Некорректный город.');
    //         //     }
    //         // }],
    //     ], [
    //         'captcha_token.required' => __('google captcha is required'),
    //         'name.required' => __('name is required'),
    //         'name.max' => __('name is must be between 191 character'),
    //         'name.regex' => __('name should contain only Cyrillic letters'),
    //         'phone.required' => __('phone is required'),
    //         'phone.max' => __('phone is must be between 191 character'),
    //         'phone.unique' => __('phone is already taken'),
    //         'email.unique' => __('email is already taken'),
    //         'email.required' => __('Неправильный e-mail'),
    //         'password.required' => __('password is required'),
    //         'password.confirmed' => __('both password does not matched'),
    //     ]);

    //     $user = User::create([
    //         // 'name' => $request->name,
    //         // 'email' => $request->email,
    //         // 'password' => Hash::make($request->password),
    //         'user_status' => 0,  //водитель
    //         'phone' => $request->phone,
    //         'avatar' => 'users/default.png',
    //         'user_type' => 1 //водитель
    //     ]);
    //     // $cityName = $request->city;
    //     // $city = City::firstOrCreate(['name' => $cityName]);
    //     $driver = Driver::create(
    //         [
    //             'user_id' => $user->id,
    //             // 'city_id' => $city->id,
    //         ]
    //     );
    //     $driverSpecification = DriverSpecification::create([
    //         'driver_id ' => $driver->id,
    //     ]);
    //     $driverDocs = DriverDoc::create([
    //         'driver_id ' => $driver->id,
    //     ]);
    //     return response()->json(['user' => $user, 'message' => 'User registered successfully']);
    // }

    // public function login(Request $request)
    // {

    //     $request->validate([
    //         'phone' => 'required|string',
    //         'password' => 'required|string',
    //     ]);

    //     if (Auth::attempt(['phone' => $request->phone, 'password' => $request->password])) {
    //         $user = Auth::user();
    //         $token = $user->createToken('auth_token')->plainTextToken;

    //         return response()->json(['user' => $user, 'access_token' => $token]);
    //     }

    //     throw ValidationException::withMessages([
    //         'phone' => ['The provided credentials are incorrect.'],
    //     ]);
    // }


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
            return response()->json(['user' => $user, 'access_token' => $token]);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'User logged out successfully']);
    }

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
    public function phoneCodeAuthentication($phone, $code)
    {
        // Найдите пользователя по полю "phone"
        $user = User::where('phone', $phone)->first();

        // Проверьте, существует ли пользователь и совпадает ли код
        if ($user && $user->code === $code) {
            // Аутентифицируйте пользователя
            Auth::login($user);
            return true;
        }

        return false;
    }
}
