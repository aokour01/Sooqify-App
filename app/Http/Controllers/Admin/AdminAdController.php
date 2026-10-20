<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use Illuminate\Http\Request;

class AdminAdController extends Controller
{
    public function index()
    {
        $ads = Ad::where('status', Ad::STATUS_PENDING)
            ->with(['user', 'category.parent.parent', 'city', 'images'])
            ->oldest()
            ->paginate(15);

        return view('admin.ads.index', compact('ads'));
    }

    public function approve(Ad $ad)
    {
        abort_unless($ad->status === Ad::STATUS_PENDING, 422);

        $ad->forceFill([
            'status'           => Ad::STATUS_ACTIVE,
            'published_at'     => now(),
            'expires_at'       => now()->addDays(30),
            'rejection_reason' => null,
        ])->save();

        return back()->with('status', "Ad \"{$ad->title}\" approved.");
    }

    public function reject(Request $request, Ad $ad)
    {
        abort_unless($ad->status === Ad::STATUS_PENDING, 422);

        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:255'],
        ]);

        $ad->forceFill([
            'status'           => Ad::STATUS_REJECTED,
            'rejection_reason' => $data['rejection_reason'],
        ])->save();

        return back()->with('status', "Ad \"{$ad->title}\" rejected.");
    }
}
