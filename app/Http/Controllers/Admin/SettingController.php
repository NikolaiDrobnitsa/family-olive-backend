<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class SettingController extends Controller
{
    public function index()
    {
        // Получаем все настройки
        $settings = Setting::all()->pluck('value', 'key')->toArray();

        // Получаем список администраторов для супер-админа
        $admins = User::where('is_admin', true)->get();

        return view('admin.settings.index', compact('settings', 'admins'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
        ]);

        foreach ($request->settings as $key => $value) {
            // Если это настройка видимости секции, обрабатываем как bool
            if (strpos($key, 'show_section_') === 0) {
                $value = isset($value) && $value == '1' ? true : false;
            }

            Setting::setValue($key, $value);
        }

        return redirect()->route('admin.settings.index')->with('success', 'Настройки успешно обновлены');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();

        // Проверка текущего пароля
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Текущий пароль неверен']);
        }

        // Обновление пароля
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return redirect()->route('admin.settings.index')->with('success', 'Пароль успешно изменен');
    }

    // Методы для супер-админа
    public function createAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_admin' => true,
            'is_verified' => true,
        ]);

        return redirect()->route('admin.settings.index')->with('success', 'Администратор успешно создан');
    }

    public function deleteAdmin($id)
    {
        // Проверка, что удаляемый админ не текущий пользователь и не супер-админ
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return redirect()->route('admin.settings.index')->with('error', 'Нельзя удалить свою учетную запись');
        }

        // Проверка на супер-админа может быть добавлена здесь

        $user->delete();

        return redirect()->route('admin.settings.index')->with('success', 'Администратор успешно удален');
    }
}
