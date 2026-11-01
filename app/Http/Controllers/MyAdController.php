<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UpdateAdRequest;
use App\Models\Category;
use App\Models\City;
use App\Models\Ad;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Notifications\AdEvent;
use Illuminate\Support\Facades\Notification;


class MyAdController extends Controller
{
    private const EDITABLE = [Ad::STATUS_PENDING, Ad::STATUS_ACTIVE, Ad::STATUS_REJECTED];

    public function index(Request $request)
    {
        $ads = $request->user()->ads()
            ->with(['images', 'city', 'category'])
            ->latest()
            ->paginate(10);

        return view('my-ads.index', compact('ads'));
    }
    public function edit(Ad $ad)
    {
        $this->authorize('update', $ad);
        abort_unless(in_array($ad->status, self::EDITABLE), 403);

        $ad->load('images');

        $categories = Category::leaf()
            ->where('is_active', true)
            ->with('parent.parent')
            ->get()
            ->sortBy('full_path');

        $cities = City::orderBy('name')->get();

        return view('my-ads.edit', compact('ad', 'categories', 'cities'));
    }

    public function update(UpdateAdRequest $request, Ad $ad)
    {
        $this->authorize('update', $ad);
        abort_unless(in_array($ad->status, self::EDITABLE), 403);

        $previousStatus = $ad->status;
        $data = $request->safe()->except(['images', 'remove_images']);
        $data['is_negotiable'] = $request->boolean('is_negotiable');

        $filesToDelete = [];
        $needsReview = false;

        DB::transaction(function () use ($request, $ad, $data, &$filesToDelete, &$needsReview) {
            $ad->fill($data);
            $needsReview = $ad->isDirty();

            // Remove selected images (only ones that belong to this ad)
            $removed = $ad->images()->whereIn('id', $request->input('remove_images', []))->get();
            foreach ($removed as $image) {
                $filesToDelete[] = $image->path;
                $image->delete();
                $needsReview = true;
            }

            $next = ($ad->images()->max('sort_order') ?? -1) + 1;
            foreach ($request->file('images', []) as $file) {
                $ad->images()->create([
                    'path'       => $file->store('ads', 'public'),
                    'sort_order' => $next++,
                ]);
                $needsReview = true;
            }

            if ($needsReview && $ad->status !== Ad::STATUS_PENDING) {
                $ad->status = Ad::STATUS_PENDING;
                $ad->rejection_reason = null;
            }

            $ad->save();
        });

        Storage::disk('public')->delete($filesToDelete);

        if ($needsReview && $previousStatus !== Ad::STATUS_PENDING) {
            Notification::send(
                User::permission('ads.approve')->where('id', '!=', $request->user()->id)->get(),
                new AdEvent($ad, 'pending', "Updated ad waiting for approval again: \"{$ad->title}\" by {$request->user()->name}.")
            );
        }

        $message = $needsReview
            ? 'Ad updated and sent for review again.'
            : 'No changes were made.';

        return redirect()->route('my-ads.index')->with('status', $message);
    }
    public function markSold(Ad $ad)
    {
        $this->authorize('update', $ad);
        abort_unless($ad->status === Ad::STATUS_ACTIVE, 422);

        $ad->forceFill(['status' => Ad::STATUS_SOLD])->save();

        return back()->with('status', 'Ad marked as sold.');
    }
    public function destroy(Ad $ad)
    {
        $this->authorize('delete', $ad);

        $ad->delete();

        return back()->with('status', 'Ad deleted.');
    }
    public function renew(Ad $ad)
    {
        $this->authorize('update', $ad);
        abort_unless($ad->canBeRenewed(), 422);

        $ad->forceFill([
            'status'       => Ad::STATUS_ACTIVE,
            'published_at' => $ad->status === Ad::STATUS_EXPIRED ? now() : $ad->published_at,
            'expires_at'   => now()->addDays(30),
        ])->save();

        return back()->with('status', 'Ad renewed for 30 days.');
    }

    public function repost(Ad $ad ,Request $request)
    {
        $this->authorize('update', $ad);
        abort_unless($ad->canBeRestored(), 422);

        $ad->forceFill([
            'status'      => Ad::STATUS_PENDING,
            'created_at'  => now(),
            'updated_at'  => now(),
        ])->save();

        Notification::send(
            User::permission('ads.approve')->where('id', '!=', $request->user()->id)->get(),
            new AdEvent($ad, 'pending', "New ad waiting for approval: \"{$ad->title}\" by {$request->user()->name}.")
        );

        return back()->with('status', 'Ad is waiting approval for two days.');
    }
}
