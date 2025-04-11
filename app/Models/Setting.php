<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
    ];

    // Получение значения настройки по ключу
    public static function getValue($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    // Установка значения настройки
    public static function setValue($key, $value)
    {
        $setting = self::updateOrCreate(['key' => $key], ['value' => $value]);
        return $setting;
    }
}
