<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function __invoke()
    {
        $adCounts = Ad::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats = [
            'users'        => User::count(),
            'new_users'    => User::where('created_at', '>=', now()->subDays(7))->count(),
            'active'       => $adCounts['active'] ?? 0,
            'pending'      => $adCounts['pending'] ?? 0,
            'posted_week'  => Ad::where('created_at', '>=', now()->subDays(7))->count(),
            'open_reports' => Report::where('status', Report::STATUS_OPEN)->count(),
        ];

        $topCategories = Ad::active()
            ->select('category_id', DB::raw('count(*) as total'))
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->with('category.parent.parent')
            ->take(5)
            ->get();

        $latestPending = Ad::where('status', Ad::STATUS_PENDING)
            ->with(['user', 'category'])
            ->oldest()
            ->take(5)
            ->get();

        $latestUsers = User::latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'adCounts', 'topCategories', 'latestPending', 'latestUsers'));
    }
}
