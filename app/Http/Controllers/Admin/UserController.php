<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UsersExport;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('is_admin', false);

        // Применение фильтров
        if ($request->has('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        if ($request->has('phone')) {
            $query->where('phone', 'like', '%' . $request->phone . '%');
        }

        if ($request->has('is_verified')) {
            $query->where('is_verified', $request->is_verified);
        }

        if ($request->has('interest_type')) {
            $query->where('interest_type', $request->interest_type);
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->has('utm_source')) {
            $query->where('utm_source', 'like', '%' . $request->utm_source . '%');
        }

        $users = $query->latest()->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        $surveyResponses = SurveyResponse::with('question')
            ->where('user_id', $id)
            ->get();

        return view('admin.users.show', compact('user', 'surveyResponses'));
    }

    public function export(Request $request)
    {
        return Excel::download(new UsersExport($request), 'users.xlsx');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Пользователь успешно удален');
    }

    public function getSurveyResponses($id)
    {
        $surveyResponses = SurveyResponse::with('question')
            ->where('user_id', $id)
            ->get();

        return response()->json(['success' => true, 'responses' => $surveyResponses]);
    }
}
