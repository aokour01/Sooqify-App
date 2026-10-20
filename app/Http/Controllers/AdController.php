<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdRequest;
use App\Models\Category;
use App\Models\City;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Ad;
use Illuminate\Http\Request;

class AdController extends Controller
{
    public function index(Request $request)
    { $category = $request->filled('category')
        ? Category::where('slug', $request->category)->where('is_active', true)->firstOrFail()
        : null;

        $breadcrumb = [];
        $subcategories = collect();

        if ($category) {
            $breadcrumb = array_merge($category->ancestors(), [$category]);

            $subcategories = $category->children()->where('is_active', true)->get();

            // A leaf category has no children, so show its siblings with the current one highlighted
            if ($subcategories->isEmpty() && $category->parent) {
                $subcategories = $category->parent->children()->where('is_active', true)->get();
            }
        }

        $ads = Ad::active()
            ->with(['images', 'city', 'category'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%' . $request->q . '%';
                $query->where(fn ($q) => $q->where('title', 'like', $term)
                    ->orWhere('description', 'like', $term));
            })
            ->when($category, fn ($query) => $query->whereIn(
                'category_id', array_merge([$category->id], $category->descendantIds())
            ))
            ->when($request->filled('city'), fn ($query) => $query->where('city_id', $request->city))
            ->when($request->filled('min_price'), fn ($query) => $query->where('price', '>=', $request->min_price))
            ->when($request->filled('max_price'), fn ($query) => $query->where('price', '<=', $request->max_price))
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        return view('ads.index', [
            'ads'           => $ads,
            'category'      => $category,
            'breadcrumb'    => $breadcrumb,
            'subcategories' => $subcategories,
            'cities'        => City::orderBy('name')->get(),
        ]);

    }
    public function create()
    {
        $categories = Category::leaf()
            ->where('is_active', true)
            ->with('parent.parent')
            ->get()
            ->sortBy('full_path');

        $cities = City::orderBy('name')->get();

        return view('ads.create', compact('categories', 'cities'));
    }

    public function store(StoreAdRequest $request)
    {
        $data = $request->safe()->except('images');
        $data['is_negotiable'] = $request->boolean('is_negotiable');
        $data['slug'] = Str::slug($data['title']) . '-' . Str::lower(Str::random(6));

        $ad = DB::transaction(function () use ($request, $data) {
            $ad = $request->user()->ads()->create($data);

            foreach ($request->file('images', []) as $i => $file) {
                $ad->images()->create([
                    'path'       => $file->store('ads', 'public'),
                    'sort_order' => $i,
                ]);
            }

            return $ad;
        });

        return redirect()->route('home')
            ->with('status', 'Your ad was submitted and is waiting for approval.');
    }
    public function show(Request $request, Ad $ad)
    {
        $user = $request->user();

        // Only approved ads are public. The owner and moderators can preview others.
        $canPreview = $user && ($user->id === $ad->user_id || $user->can('ads.approve'));
        abort_unless($ad->isLive() || $canPreview, 404);

        // Count a view once per session, and never the owner's own views
        $key = "viewed_ad_{$ad->id}";
        if ($ad->status === Ad::STATUS_ACTIVE
            && ! $request->session()->has($key)
            && ! ($user && $user->id === $ad->user_id)) {
            $ad->increment('views');
            $request->session()->put($key, true);
        }

        $ad->load(['images', 'user', 'city', 'category.parent.parent']);

        $related = Ad::active()
            ->where('category_id', $ad->category_id)
            ->where('id', '!=', $ad->id)
            ->with(['images', 'city'])
            ->latest('published_at')
            ->take(4)
            ->get();

        $isFavorite = $user
            ? $user->favorites()->where('ads.id', $ad->id)->exists()
            : false;

        abort_unless($ad->isLive() || $canPreview, 404);

        return view('ads.show', compact('ad', 'related', 'isFavorite'));    }
    public function phone(Ad $ad)
    {
        abort_unless($ad->status === Ad::STATUS_ACTIVE, 404);
        abort_unless($ad->contact_phone, 404);


        abort_unless($ad->isLive(), 404);

        return response()->json(['phone' => $ad->contact_phone]);
    }
}
