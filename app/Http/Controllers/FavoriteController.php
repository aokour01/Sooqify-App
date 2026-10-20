<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $ads = $request->user()->favorites()
            ->active()
            ->with(['images', 'city'])
            ->latest('favorites.created_at')
            ->paginate(12);

        return view('favorites.index', compact('ads'));
    }

    public function toggle(Request $request, Ad $ad)
    {
        $user = $request->user();

        if ($user->favorites()->where('ads.id', $ad->id)->exists()) {
            $user->favorites()->detach($ad->id);
            $message = 'Removed from favorites.';
        } else {
            abort_unless($ad->status === Ad::STATUS_ACTIVE, 404);
            $user->favorites()->attach($ad->id);
            $message = 'Saved to favorites.';
        }

        return back()->with('status', $message);
    }
}
