<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Event;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::where('is_admin', false)->count();
        $verifiedUsers = User::where('is_verified', true)->where('is_admin', false)->count();
        $totalEvents = Event::count();
        $usersByDate = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('is_admin', false)
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(7)
            ->get();

        return response()->json([
            'totalUsers' => $totalUsers,
            'verifiedUsers' => $verifiedUsers,
            'totalEvents' => $totalEvents,
            'usersByDate' => $usersByDate
        ]);
    }
}
