<?php

namespace App\Http\Controllers\Admin;

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
        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        return view('admin.events.create');
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

        Event::create($eventData);

        return redirect()->route('admin.events.index')->with('success', 'Событие успешно создано');
    }

    public function edit($id)
    {
        $event = Event::findOrFail($id);
        return view('admin.events.edit', compact('event'));
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
        $eventData = $request->except('image');

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

        $event->update($eventData);

        return redirect()->route('admin.events.index')->with('success', 'Событие успешно обновлено');
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

        return redirect()->route('admin.events.index')->with('success', 'Событие успешно удалено');
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

        return response()->json(['success' => true]);
    }
}
