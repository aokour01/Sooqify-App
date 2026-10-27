<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use Illuminate\Http\Request;
use App\Notifications\AdEvent;

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

        $ad->user->notify(new AdEvent(
            $ad, 'approved', "Your ad \"{$ad->title}\" was approved and is now live."
        ));

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

        $ad->user->notify(new AdEvent(
            $ad, 'rejected', "Your ad \"{$ad->title}\" was rejected: {$data['rejection_reason']}"
        ));

        return back()->with('status', "Ad \"{$ad->title}\" rejected.");
    }
    public function all(Request $request)
    {
        $statuses = ['pending', 'active', 'sold', 'rejected', 'expired'];

        $ads = Ad::query()
            ->when($request->status === 'deleted', fn ($q) => $q->onlyTrashed())
            ->when(in_array($request->status, $statuses, true), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%' . $request->q . '%';

                $q->where(fn ($w) => $w->where('title', 'like', $term)
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)));
            })
            ->with(['user', 'category', 'city'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.ads.all', compact('ads'));
    }

    public function destroy(Ad $ad)
    {
        $ad->delete();

        return back()->with('status', "Ad \"{$ad->title}\" removed.");
    }

    public function restore(int $adId)
    {
        $ad = Ad::onlyTrashed()->findOrFail($adId);
        $ad->restore();

        return back()->with('status', "Ad \"{$ad->title}\" restored.");
    }
}
