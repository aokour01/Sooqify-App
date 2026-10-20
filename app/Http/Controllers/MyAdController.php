<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UpdateAdRequest;
use App\Models\Category;
use App\Models\City;
use App\Models\Ad;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


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

            // Add new images after the existing ones
            $next = ($ad->images()->max('sort_order') ?? -1) + 1;
            foreach ($request->file('images', []) as $file) {
                $ad->images()->create([
                    'path'       => $file->store('ads', 'public'),
                    'sort_order' => $next++,
                ]);
                $needsReview = true;
            }

            // Changes to an approved or rejected ad go back to moderation
            if ($needsReview && $ad->status !== Ad::STATUS_PENDING) {
                $ad->status = Ad::STATUS_PENDING;
                $ad->rejection_reason = null;
            }

            $ad->save();
        });

        // Delete files only after the database changes succeeded
        Storage::disk('public')->delete($filesToDelete);

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
}
