<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Event;
use App\Models\SurveyResponse;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            // Get total users (excluding admins)
            $totalUsers = User::where('is_admin', false)->count();

            // Get verified users count
            $verifiedUsers = User::where('is_verified', true)
                ->where('is_admin', false)
                ->count();

            // Get total events count
            $totalEvents = Event::count();

            // Get count of users who completed the survey
            $surveyCompleted = User::whereHas('surveyResponses')->count();

            // Get users registered in the last 7 days
            $usersByDate = [];

            // Get data for the last 7 days
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i)->format('Y-m-d');

                $count = User::where('is_admin', false)
                    ->whereDate('created_at', $date)
                    ->count();

                $usersByDate[] = [
                    'date' => $date,
                    'count' => $count
                ];
            }

            // Get recent activities
            $recentActivity = $this->getRecentActivities();

            return response()->json([
                'totalUsers' => $totalUsers,
                'verifiedUsers' => $verifiedUsers,
                'totalEvents' => $totalEvents,
                'surveyCompleted' => $surveyCompleted,
                'usersByDate' => $usersByDate,
                'recentActivity' => $recentActivity
            ]);
        } catch (\Exception $e) {
            Log::error('Dashboard error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'message' => 'Error loading dashboard data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent activities for the dashboard
     */
    private function getRecentActivities()
    {
        // Get recent user registrations (last 3)
        $recentUsers = User::where('is_admin', false)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        $userActivities = [];
        foreach ($recentUsers as $user) {
            $userActivities[] = [
                'title' => 'Регистрация пользователя',
                'subtitle' => Carbon::parse($user->created_at)->format('d.m.Y H:i'),
                'icon' => 'person_add',
                'color' => 'primary',
                'content' => "Пользователь {$user->name} ({$user->email}) зарегистрировался"
            ];
        }

        // Get recent events (last 2)
        $recentEvents = Event::orderBy('created_at', 'desc')
            ->limit(2)
            ->get();

        $eventActivities = [];
        foreach ($recentEvents as $event) {
            $eventActivities[] = [
                'title' => 'Добавлено событие',
                'subtitle' => Carbon::parse($event->created_at)->format('d.m.Y H:i'),
                'icon' => 'event',
                'color' => 'warning',
                'content' => "Событие \"{$event->title}\" было добавлено"
            ];
        }

        // Combine activities
        $allActivities = array_merge($userActivities, $eventActivities);

        // Sort by date (most recent first)
        usort($allActivities, function($a, $b) {
            $dateA = Carbon::createFromFormat('d.m.Y H:i', $a['subtitle']);
            $dateB = Carbon::createFromFormat('d.m.Y H:i', $b['subtitle']);
            return $dateB->timestamp - $dateA->timestamp;
        });

        // Get most recent 5 activities
        return array_slice($allActivities, 0, 5);
    }
}
