<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SurveyResponse;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UsersExport;

class UserController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = User::where('is_admin', false);

            // Applying filters
            if ($request->has('email') && !empty($request->email)) {
                $query->where('email', 'like', '%' . $request->email . '%');
            }

            if ($request->has('phone') && !empty($request->phone)) {
                $query->where('phone', 'like', '%' . $request->phone . '%');
            }

            if ($request->has('is_verified') && $request->is_verified !== '' && $request->is_verified !== null) {
                $query->where('is_verified', (int)$request->is_verified);
            }

            if ($request->has('interest_type') && !empty($request->interest_type)) {
                $query->where('interest_type', $request->interest_type);
            }

            if ($request->has('date_from') && !empty($request->date_from)) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to') && !empty($request->date_to)) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            if ($request->has('utm_source') && !empty($request->utm_source)) {
                $query->where('utm_source', 'like', '%' . $request->utm_source . '%');
            }

            // Sorting and pagination
            $sortBy = $request->input('sortBy', 'created_at');
            $descending = $request->input('descending', 1);
            $perPage = (int)$request->input('per_page', 15);

            // Validate sort column
            $allowedSortColumns = ['id', 'name', 'email', 'phone', 'is_verified', 'interest_type', 'created_at'];
            if (!in_array($sortBy, $allowedSortColumns)) {
                $sortBy = 'created_at';
            }

            $users = $query->orderBy($sortBy, $descending ? 'desc' : 'asc')
                ->paginate($perPage);

            return response()->json($users);
        } catch (\Exception $e) {
            // Log detailed error information
            Log::error('User listing error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'message' => 'Error fetching users: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $user = User::findOrFail($id);
            return response()->json(['success' => true, 'user' => $user]);
        } catch (\Exception $e) {
            Log::error('Error showing user: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }
    }

    public function export(Request $request)
    {
        try {
            $filename = 'users_export_' . date('Y-m-d_H-i-s') . '.xlsx';

            // Log the export attempt for debugging
            Log::info('Starting user export with request parameters: ' . json_encode($request->all()));

            return Excel::download(new UsersExport($request), $filename);
        } catch (\Exception $e) {
            Log::error('Export error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            // For API requests, return JSON error
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Export failed: ' . $e->getMessage()], 500);
            }

            // For web requests, redirect back with error
            return back()->withErrors(['export' => 'Export failed: ' . $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();
            return response()->json(['success' => true, 'message' => 'User successfully deleted']);
        } catch (\Exception $e) {
            Log::error('Delete user error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error deleting user'], 500);
        }
    }

    public function getSurveyResponses($id)
    {
        try {
            $surveyResponses = SurveyResponse::with('question')
                ->where('user_id', $id)
                ->get();

            return response()->json(['success' => true, 'responses' => $surveyResponses]);
        } catch (\Exception $e) {
            Log::error('Survey responses error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error retrieving survey responses'], 500);
        }
    }

    public function getVisits($id)
    {
        try {
            $visits = Visit::where('user_id', $id)
                ->orderBy('visited_at', 'desc')
                ->get();

            return response()->json(['success' => true, 'visits' => $visits]);
        } catch (\Exception $e) {
            Log::error('Visit history error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error retrieving visit history'], 500);
        }
    }
}
