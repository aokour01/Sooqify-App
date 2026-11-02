<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Ad;
use App\Models\City;

class HomeController extends Controller
{

    public function index()
    {
        $categories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->with('children')
            ->orderBy('sort_order')
            ->get();

        $latestAds = Ad::active()
            ->with(['images', 'city'])
            ->latest('published_at')
            ->take(8)
            ->get();
        $mostViewedAds = Ad::active()
            ->with(['images', 'city'])
            ->withCount('favoritedBy')
            ->orderByDesc('views')
            ->orderByDesc('favorited_by_count')
            ->orderByDesc('published_at')
            ->take(8)
            ->get();


        $cities = City::orderBy('name')->get();

        return view('home', compact('categories', 'latestAds', 'cities', 'mostViewedAds'));
    }
}
