<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    public function index()
    {
        $questions = SurveyQuestion::orderBy('sort_order')->get();
        return response()->json(['success' => true, 'questions' => $questions]);
    }

    public function show($id)
    {
        $question = SurveyQuestion::findOrFail($id);
        return response()->json(['success' => true, 'question' => $question]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'type' => 'required|string|in:single,multiple,text',
            'options' => 'nullable|array|required_unless:type,text',
            'options.*' => 'string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $questionData = $request->only(['question', 'type', 'is_active']);

        // Преобразование строкового значения is_active в boolean
        if (isset($questionData['is_active'])) {
            $questionData['is_active'] = filter_var($questionData['is_active'], FILTER_VALIDATE_BOOLEAN);
        } else {
            $questionData['is_active'] = true;
        }

        // Добавление опций для вопросов с выбором
        if ($request->type !== 'text' && $request->has('options')) {
            $questionData['options'] = $request->options;
        }

        // Установка порядка сортировки
        $maxSortOrder = SurveyQuestion::max('sort_order') ?? 0;
        $questionData['sort_order'] = $maxSortOrder + 1;

        $question = SurveyQuestion::create($questionData);

        return response()->json([
            'success' => true,
            'message' => 'Вопрос успешно создан',
            'question' => $question
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'type' => 'required|string|in:single,multiple,text',
            'options' => 'nullable|array|required_unless:type,text',
            'options.*' => 'string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $question = SurveyQuestion::findOrFail($id);
        $questionData = $request->only(['question', 'type', 'is_active']);

        // Преобразование строкового значения is_active в boolean
        if (isset($questionData['is_active'])) {
            $questionData['is_active'] = filter_var($questionData['is_active'], FILTER_VALIDATE_BOOLEAN);
        }

        // Добавление опций для вопросов с выбором
        if ($request->type !== 'text' && $request->has('options')) {
            $questionData['options'] = $request->options;
        } else if ($request->type === 'text') {
            $questionData['options'] = null;
        }

        $question->update($questionData);

        return response()->json([
            'success' => true,
            'message' => 'Вопрос успешно обновлен',
            'question' => $question
        ]);
    }

    public function destroy($id)
    {
        $question = SurveyQuestion::findOrFail($id);

        // Удаляем также все ответы на этот вопрос
        SurveyResponse::where('question_id', $id)->delete();

        $question->delete();

        return response()->json(['success' => true, 'message' => 'Вопрос успешно удален']);
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

        return response()->json(['success' => true, 'message' => 'Порядок вопросов успешно обновлен']);
    }
}
