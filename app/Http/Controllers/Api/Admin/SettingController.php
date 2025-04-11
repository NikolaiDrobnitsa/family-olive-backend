<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    public function index()
    {
        // Получаем все настройки
        $settings = Setting::all()->pluck('value', 'key')->toArray();

        // Для супер-админа также возвращаем список администраторов
        $currentUser = Auth::user();

        $admins = [];
        if ($currentUser && $currentUser->is_super_admin) {
            $admins = User::where('is_admin', true)->get();
        }

        return response()->json([
            'settings' => $settings,
            'admins' => $admins
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
        ]);

        foreach ($request->settings as $key => $value) {
            // Если это настройка видимости секции, обрабатываем как bool
            if (strpos($key, 'show_section_') === 0 || strpos($key, 'enable_lang_') === 0) {
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }

            Setting::setValue($key, $value);
        }

        return response()->json(['success' => true, 'message' => 'Настройки успешно обновлены']);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        // Проверка текущего пароля
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Текущий пароль неверен'
            ], 422);
        }

        // Обновление пароля
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json(['success' => true, 'message' => 'Пароль успешно изменен']);
    }

    // Методы для супер-админа
    public function createAdmin(Request $request)
    {
        // Проверка, что текущий пользователь - супер-админ
        $currentUser = Auth::user();
        if (!$currentUser || !$currentUser->is_super_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Недостаточно прав для выполнения операции'
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $admin = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_admin' => true,
            'is_verified' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Администратор успешно создан',
            'admin' => $admin
        ], 201);
    }

    public function deleteAdmin($id)
    {
        // Проверка, что текущий пользователь - супер-админ
        $currentUser = Auth::user();
        if (!$currentUser || !$currentUser->is_super_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Недостаточно прав для выполнения операции'
            ], 403);
        }

        // Проверка, что удаляемый админ не текущий пользователь
        if ((int)$id === $currentUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'Нельзя удалить свою учетную запись'
            ], 422);
        }

        $admin = User::findOrFail($id);

        // Проверка, что удаляемый пользователь - админ
        if (!$admin->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Указанный пользователь не является администратором'
            ], 422);
        }

        $admin->delete();

        return response()->json(['success' => true, 'message' => 'Администратор успешно удален']);
    }
}
