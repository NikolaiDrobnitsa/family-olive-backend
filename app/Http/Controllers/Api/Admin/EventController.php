<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::orderBy('sort_order')->get();
        return response()->json(['success' => true, 'events' => $events]);
    }

    public function show($id)
    {
        $event = Event::findOrFail($id);

        // Add full image URL
        if ($event->image) {
            $event->image_url = url('storage/events/' . $event->image);
        }

        return response()->json(['success' => true, 'event' => $event]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'event_date' => 'nullable|date',
            'link' => 'nullable|string|max:255',
            'category' => 'nullable|string|in:20ha,5ha_cottage',
            'is_active' => 'nullable|boolean',
        ]);

        $eventData = $request->except('image');

        // Обработка изображения
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '.' . $image->getClientOriginalExtension();

            // Сохранение оригинального изображения
            $image->storeAs('public/events', $filename);

            // Создание и сохранение миниатюры
            $thumbnail = Image::make($image);
            $thumbnail->fit(300, 200);
            $thumbnail->save(storage_path('app/public/events/thumbnails/' . $filename));

            $eventData['image'] = $filename;
        }

        // Установка порядка сортировки
        $maxSortOrder = Event::max('sort_order') ?? 0;
        $eventData['sort_order'] = $maxSortOrder + 1;

        // Преобразование строкового значения is_active в boolean
        if (isset($eventData['is_active'])) {
            $eventData['is_active'] = filter_var($eventData['is_active'], FILTER_VALIDATE_BOOLEAN);
        }

        $event = Event::create($eventData);

        // Add full image URL
        if ($event->image) {
            $event->image_url = url('storage/events/' . $event->image);
        }

        return response()->json([
            'success' => true,
            'message' => 'Событие успешно создано',
            'event' => $event
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'event_date' => 'nullable|date',
            'link' => 'nullable|string|max:255',
            'category' => 'nullable|string|in:20ha,5ha_cottage',
            'is_active' => 'nullable|boolean',
        ]);

        $event = Event::findOrFail($id);
        $eventData = $request->except(['image', '_method']);

        // Обработка изображения
        if ($request->hasFile('image')) {
            // Удаление старого изображения
            if ($event->image) {
                Storage::delete([
                    'public/events/' . $event->image,
                    'public/events/thumbnails/' . $event->image
                ]);
            }

            $image = $request->file('image');
            $filename = time() . '.' . $image->getClientOriginalExtension();

            // Сохранение оригинального изображения
            $image->storeAs('public/events', $filename);

            // Создание и сохранение миниатюры
            $thumbnail = Image::make($image);
            $thumbnail->fit(300, 200);
            $thumbnail->save(storage_path('app/public/events/thumbnails/' . $filename));

            $eventData['image'] = $filename;
        }

        // Преобразование строкового значения is_active в boolean
        if (isset($eventData['is_active'])) {
            $eventData['is_active'] = filter_var($eventData['is_active'], FILTER_VALIDATE_BOOLEAN);
        }

        $event->update($eventData);

        // Add full image URL
        if ($event->image) {
            $event->image_url = url('storage/events/' . $event->image);
        }

        return response()->json([
            'success' => true,
            'message' => 'Событие успешно обновлено',
            'event' => $event
        ]);
    }

    public function destroy($id)
    {
        $event = Event::findOrFail($id);

        // Удаление изображения
        if ($event->image) {
            Storage::delete([
                'public/events/' . $event->image,
                'public/events/thumbnails/' . $event->image
            ]);
        }

        $event->delete();

        return response()->json(['success' => true, 'message' => 'Событие успешно удалено']);
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'events' => 'required|array',
            'events.*.id' => 'required|exists:events,id',
            'events.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($request->events as $eventData) {
            Event::where('id', $eventData['id'])->update(['sort_order' => $eventData['sort_order']]);
        }

        return response()->json(['success' => true, 'message' => 'Порядок событий успешно обновлен']);
    }
}
