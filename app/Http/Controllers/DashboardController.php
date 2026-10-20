<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        // One query that counts my ads per status
        $counts = $user->ads()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats = [
            'active'    => $counts['active'] ?? 0,
            'pending'   => $counts['pending'] ?? 0,
            'views'     => (int) $user->ads()->sum('views'),
            'favorites' => $user->favorites()->count(),
        ];

        $rejected = $user->ads()
            ->where('status', 'rejected')
            ->latest('updated_at')
            ->take(5)
            ->get();

        // Expired ads, plus active ads that end within 7 days
        $needsRenewal = $user->ads()
            ->where(fn ($q) => $q->where('status', 'expired')
                ->orWhere(fn ($q2) => $q2->where('status', 'active')
                    ->where('expires_at', '<=', now()->addDays(7))))
            ->orderBy('expires_at')
            ->take(5)
            ->get();

        $recent = $user->ads()->with('images')->latest()->take(5)->get();

        return view('dashboard', compact('stats', 'rejected', 'needsRenewal', 'recent'));
    }
}
