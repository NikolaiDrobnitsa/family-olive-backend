<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SurveyQuestion;
use Illuminate\Http\Request;

class SurveyQuestionController extends Controller
{
    public function index()
    {
        $questions = SurveyQuestion::orderBy('sort_order')->get();
        return view('admin.survey.index', compact('questions'));
    }

    public function create()
    {
        return view('admin.survey.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'type' => 'required|string|in:single,multiple,text',
            'options' => 'nullable|array',
            'options.*' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $questionData = $request->except(['options']);

        // Преобразование опций в JSON
        if ($request->has('options')) {
            $questionData['options'] = $request->options;
        }

        // Установка порядка сортировки
        $maxSortOrder = SurveyQuestion::max('sort_order') ?? 0;
        $questionData['sort_order'] = $maxSortOrder + 1;

        SurveyQuestion::create($questionData);

        return redirect()->route('admin.survey.index')->with('success', 'Вопрос успешно создан');
    }

    public function edit($id)
    {
        $question = SurveyQuestion::findOrFail($id);
        return view('admin.survey.edit', compact('question'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'type' => 'required|string|in:single,multiple,text',
            'options' => 'nullable|array',
            'options.*' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $question = SurveyQuestion::findOrFail($id);
        $questionData = $request->except(['options']);

        // Преобразование опций в JSON
        if ($request->has('options')) {
            $questionData['options'] = $request->options;
        }

        $question->update($questionData);

        return redirect()->route('admin.survey.index')->with('success', 'Вопрос успешно обновлен');
    }

    public function destroy($id)
    {
        $question = SurveyQuestion::findOrFail($id);
        $question->delete();

        return redirect()->route('admin.survey.index')->with('success', 'Вопрос успешно удален');
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'questions' => 'required|array',
            'questions.*.id' => 'required|exists:survey_questions,id',
            'questions.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($request->questions as $questionData) {
            SurveyQuestion::where('id', $questionData['id'])
                ->update(['sort_order' => $questionData['sort_order']]);
        }

        return response()->json(['success' => true]);
    }
}
