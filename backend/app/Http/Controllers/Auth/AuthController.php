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

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            // 'name' => ['required', 'string', 'max:40', 'regex:/^[А-Яа-яЁё\s]+$/u'],
            'captcha_token' => ['nullable'],
            'phone' => ['required', 'string', 'max:255', 'unique:users'],
            // 'email' => ['string', 'email', 'max:255', 'unique:users'],
            // 'password' => ['required', 'string', 'min:8', 'confirmed'],
            // 'city' => ['required', 'string', 'max:250', function ($attribute, $value, $fail) {
            //     $parser = new ParserController();
            //     if (!$parser->parseCity($value)) {
            //         $fail('Некорректный город.');
            //     }
            // }],
        ], [
            'captcha_token.required' => __('google captcha is required'),
            'name.required' => __('name is required'),
            'name.max' => __('name is must be between 191 character'),
            'name.regex' => __('name should contain only Cyrillic letters'),
            'phone.required' => __('phone is required'),
            'phone.max' => __('phone is must be between 191 character'),
            'phone.unique' => __('phone is already taken'),
            'email.unique' => __('email is already taken'),
            'email.required' => __('Неправильный e-mail'),
            'password.required' => __('password is required'),
            'password.confirmed' => __('both password does not matched'),
        ]);

        $user = User::create([
            // 'name' => $request->name,
            // 'email' => $request->email,
            // 'password' => Hash::make($request->password),
            'user_status' => false,  //водитель
            'phone' => $request->phone,
            'avatar' => 'users/default.png',
            'user_type' => 1 //водитель
        ]);
        // $cityName = $request->city;
        // $city = City::firstOrCreate(['name' => $cityName]);
        $driver = Driver::create(
            [
                'user_id' => $user->id,
                // 'city_id' => $city->id,
            ]
        );
        $driverSpecification = DriverSpecification::create([
            'driver_id ' => $driver->id,
        ]);
        $driverDocs = DriverDoc::create([
            'driver_id ' => $driver->id,
        ]);
        return response()->json(['user' => $user, 'message' => 'User registered successfully']);
    }

    public function login(Request $request)
    {

        $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt(['phone' => $request->phone, 'password' => $request->password])) {
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json(['user' => $user, 'access_token' => $token]);
        }

        throw ValidationException::withMessages([
            'phone' => ['The provided credentials are incorrect.'],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'User logged out successfully']);
    }
}
