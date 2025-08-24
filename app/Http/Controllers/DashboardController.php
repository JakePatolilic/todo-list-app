<?php

namespace App\Http\Controllers;
use App\Models\Task;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $completedCount = Task::where('user_id', $userId)
            ->where('is_completed', 1)
            ->count();

        $pendingCount = Task::where('user_id', $userId)
            ->where('is_completed', 0)
            ->count();

        $overdueCount = Task::where('user_id', $userId)
            ->where('is_completed', 0)
            ->whereDate('due_date', '<', now())
            ->count();

        return Inertia::render('Dashboard', [
            'completedCount' => $completedCount,
            'pendingCount' => $pendingCount,
            'overdueCount' => $overdueCount,
        ]);
    }
}
