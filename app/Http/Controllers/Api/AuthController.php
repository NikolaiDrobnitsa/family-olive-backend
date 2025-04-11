<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\VerificationCode;

class AuthController extends Controller
{
    // Авторизация/регистрация
    public function login(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'phone' => 'required|string|max:20',
        ]);

        // Проверяем, существует ли пользователь
        $user = User::where('email', $request->email)->first();

        if ($user && $user->is_verified) {
            // Если пользователь уже существует и верифицирован, авторизуем его
            Auth::login($user);
            $this->recordVisit($user, $request);

            // Создаем токен для API
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'verified' => true,
                'token' => $token,
                'user' => $user
            ]);
        } else {
            // Генерируем код верификации
            $verificationCode = mt_rand(100000, 999999);

            // Если пользователь существует, но не верифицирован, обновляем его данные
            if ($user) {
                $user->update([
                    'name' => $request->name,
                    'phone' => $request->phone,
                    'verification_code' => $verificationCode,
                    'ip_address' => $request->ip(),
                    'utm_source' => $request->input('utm_source'),
                    'utm_medium' => $request->input('utm_medium'),
                    'utm_campaign' => $request->input('utm_campaign'),
                    'utm_term' => $request->input('utm_term'),
                    'utm_content' => $request->input('utm_content'),
                ]);
            } else {
                // Создаем нового пользователя
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'password' => Hash::make(Str::random(16)), // Генерируем случайный пароль
                    'verification_code' => $verificationCode,
                    'is_verified' => false,
                    'is_admin' => false,
                    'ip_address' => $request->ip(),
                    'utm_source' => $request->input('utm_source'),
                    'utm_medium' => $request->input('utm_medium'),
                    'utm_campaign' => $request->input('utm_campaign'),
                    'utm_term' => $request->input('utm_term'),
                    'utm_content' => $request->input('utm_content'),
                ]);
            }

            // Отправляем email с кодом верификации
//            Mail::to($user->email)->send(new VerificationCode($verificationCode));

            return response()->json([
                'success' => true,
                'verified' => false,
                'email' => $user->email
            ]);
        }
    }

    // Авторизация администратора
    public function adminLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string',
        ]);

        // Пытаемся аутентифицировать пользователя
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Неверные учетные данные'
            ], 401);
        }

        $user = Auth::user();

        // Проверяем, что пользователь является администратором
        if (!$user->is_admin) {
            // Выходим из системы, если это не администратор
            Auth::logout();

            return response()->json([
                'success' => false,
                'message' => 'У вас нет прав доступа к административной панели'
            ], 403);
        }

        // Регистрируем посещение
        $this->recordVisit($user, $request);

        // Удаляем существующие токены и создаем новый
        $user->tokens()->delete();
        $token = $user->createToken('admin_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => $user
        ]);
    }

    // Верификация кода
    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:255',
            'code' => 'required|string|max:6',
        ]);

        $user = User::where('email', $request->email)
            ->where('verification_code', $request->code)
            ->first();

        if ($user) {
            $user->update([
                'is_verified' => true,
                'verification_code' => null, // Очищаем код после успешной верификации
            ]);

            Auth::login($user);
            $this->recordVisit($user, $request);

            // Создаем токен для API
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => $user
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Неверный код подтверждения'], 422);
    }

    // Запись о посещении пользователя
    private function recordVisit($user, Request $request)
    {
        Visit::create([
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'visited_at' => now(),
        ]);
    }

    // Выход из системы
    public function logout(Request $request)
    {
        // Удаляем текущий токен
        $request->user()->currentAccessToken()->delete();

        return response()->json(['success' => true]);
    }

    // Проверка авторизации
    public function check(Request $request)
    {
        return response()->json([
            'authenticated' => true,
            'user' => $request->user(),
        ]);
    }

    // Повторная отправка кода верификации
    public function resendCode(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:255',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Пользователь не найден'], 404);
        }

        // Генерируем новый код верификации
        $verificationCode = mt_rand(100000, 999999);
        $user->update(['verification_code' => $verificationCode]);

        // Отправляем email с новым кодом
//        Mail::to($user->email)->send(new VerificationCode($verificationCode));

        return response()->json(['success' => true]);
    }
}
