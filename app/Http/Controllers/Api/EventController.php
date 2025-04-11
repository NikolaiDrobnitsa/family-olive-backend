<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    // Получить все активные события
    public function index()
    {
        $events = Event::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($event) {
                // Добавляем полный URL к изображению
                if ($event->image) {
                    $event->image_url = url('storage/events/' . $event->image);
                }
                return $event;
            });

        return response()->json(['success' => true, 'events' => $events]);
    }

    // Получить события по категории
    public function getByCategory($category)
    {
        $validCategories = ['20ha', '5ha_cottage'];

        if (!in_array($category, $validCategories)) {
            return response()->json(['success' => false, 'message' => 'Некорректная категория'], 400);
        }

        $events = Event::where('is_active', true)
            ->where(function($query) use ($category) {
                $query->where('category', $category)
                    ->orWhereNull('category');
            })
            ->orderBy('sort_order')
            ->get()
            ->map(function ($event) {
                // Добавляем полный URL к изображению
                if ($event->image) {
                    $event->image_url = url('storage/events/' . $event->image);
                }
                return $event;
            });

        return response()->json(['success' => true, 'events' => $events]);
    }
}
