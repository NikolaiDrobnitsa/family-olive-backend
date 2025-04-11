<?php
// app/Http/Controllers/SurveyController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    // Получить все активные вопросы опросника
    public function getQuestions()
    {
        $questions = SurveyQuestion::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json(['success' => true, 'questions' => $questions]);
    }

    // Сохранить ответы пользователя
    public function saveResponses(Request $request)
    {
        $request->validate([
            'responses' => 'required|array',
            'responses.*.question_id' => 'required|exists:survey_questions,id',
            'responses.*.answer' => 'required|string',
        ]);

        $user = $request->user();

        foreach ($request->responses as $response) {
            SurveyResponse::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'question_id' => $response['question_id'],
                ],
                [
                    'answer' => $response['answer'],
                ]
            );
        }

        // Определяем интерес пользователя на основе ответов
        $this->determineUserInterest($user, $request->responses);

        return response()->json(['success' => true]);
    }

    // Определение интереса пользователя на основе ответов
    private function determineUserInterest($user, $responses)
    {
        // Находим вопрос, который определяет интерес к типу плантации
        $interest = null;

        foreach ($responses as $response) {
            $question = SurveyQuestion::find($response['question_id']);

            // Предполагаем, что в вопросе есть ключевое слово "плантация" или "интерес"
            if (stripos($question->question, 'плантация') !== false ||
                stripos($question->question, 'интерес') !== false) {

                $answer = strtolower($response['answer']);

                if (stripos($answer, '20') !== false || stripos($answer, 'двадцать') !== false) {
                    $interest = 'Плантация 20 га';
                } elseif (stripos($answer, '5') !== false || stripos($answer, 'пять') !== false ||
                    stripos($answer, 'коттедж') !== false) {
                    $interest = '5 га + коттедж';
                }

                break;
            }
        }

        if ($interest) {
            $user->update(['interest_type' => $interest]);
        }
    }

    // Получить ответы текущего пользователя
    public function getUserResponses(Request $request)
    {
        $user = $request->user();
        $responses = SurveyResponse::with('question')
            ->where('user_id', $user->id)
            ->get();

        return response()->json(['success' => true, 'responses' => $responses]);
    }

    // Проверить, проходил ли пользователь опрос
    public function checkSurveyStatus(Request $request)
    {
        $user = $request->user();
        $hasSurveyResponses = SurveyResponse::where('user_id', $user->id)->exists();

        return response()->json([
            'success' => true,
            'completed' => $hasSurveyResponses,
            'interest_type' => $user->interest_type
        ]);
    }
}
