<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Child;
use App\Models\Event;
use App\Models\FollowUp;
use App\Models\QuestionnaireResponse;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'children' => Child::query()->count(),
            'active_children' => Child::query()->where('status', 'active')->count(),
            'users' => User::query()->where('status', 'active')->count(),
            'events' => Event::query()->count(),
            'upcoming_events' => Event::query()
                ->whereDate('event_date', '>=', today())
                ->whereNotIn('status', ['cancelled', 'completed'])
                ->count(),
            'attendance' => Attendance::query()->whereDate('attendance_time', today())->count(),
            'pending_reviews' => QuestionnaireResponse::query()->where('review_status', 'pending')->count(),
            'follow_ups' => FollowUp::query()->whereNotIn('status', ['completed', 'cancelled'])->count(),
        ];

        $activity = AuditLog::query()->latest('id')->limit(8)->get();
        $names = User::query()
            ->whereIn('id', $activity->pluck('user_id')->filter()->unique())
            ->pluck('name', 'id');

        $actions = collect([
            ['label' => 'Add Child', 'route' => 'children.create', 'icon' => 'heart'],
            ['label' => 'Create Event', 'route' => 'events.create', 'icon' => 'calendar'],
            ['label' => 'Add User', 'route' => 'users.create', 'icon' => 'user'],
            ['label' => 'Review Submissions', 'route' => 'admin_submissions.index', 'icon' => 'check', 'params' => ['review_status' => 'pending']],
            ['label' => 'New Follow-up', 'route' => 'admin_follow_ups.create', 'icon' => 'file'],
        ])
            ->filter(fn (array $action) => Route::has($action['route']))
            ->map(fn (array $action) => $action + ['url' => route($action['route'], $action['params'] ?? [])])
            ->values();

        return view('admin.dashboard', compact('stats', 'activity', 'names', 'actions'));
    }
}