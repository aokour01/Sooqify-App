@extends('layouts.app')

@section('content')
    <div class="container">
        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('ads.index') }}">All ads</a></li>
                @foreach ($breadcrumb as $crumb)
                    @if ($loop->last)
                        <li class="breadcrumb-item active" aria-current="page">{{ $crumb->name }}</li>
                    @else
                        <li class="breadcrumb-item">
                            <a href="{{ route('ads.index', ['category' => $crumb->slug]) }}">{{ $crumb->name }}</a>
                        </li>
                    @endif
                @endforeach
            </ol>
        </nav>

        <h2 class="mb-3">{{ $category?->name ?? 'All ads' }}</h2>

        {{-- Subcategory pills --}}
        @if ($subcategories->isNotEmpty())
            <div class="d-flex flex-wrap gap-2 mb-4">
                @foreach ($subcategories as $sub)
                    <a href="{{ route('ads.index', array_merge(request()->except(['category', 'page']), ['category' => $sub->slug])) }}"
                       class="btn btn-sm rounded-pill {{ $category && $sub->id === $category->id ? 'btn-primary' : 'btn-outline-secondary' }}">
                        {{ $sub->name }}
                    </a>
                @endforeach
            </div>
        @endif
        <form method="GET" action="{{ route('ads.index') }}" class="row g-2 mb-4">
            @if ($category)
                <input type="hidden" name="category" value="{{ $category->slug }}">
            @endif
            <div class="col-lg-4">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search...">
            </div>
            <div class="col-lg-2">
                <select name="city" class="form-select">
                    <option value="">All cities</option>
                    @foreach ($cities as $city)
                        <option value="{{ $city->id }}" @selected(request('city') == $city->id)>{{ $city->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <input type="number" name="min_price" value="{{ request('min_price') }}" class="form-control" placeholder="Min price">
            </div>
            <div class="col-lg-2">
                <input type="number" name="max_price" value="{{ request('max_price') }}" class="form-control" placeholder="Max price">
            </div>
            <div class="col-lg-2 d-flex gap-2">
                <button class="btn btn-primary flex-fill">Filter</button>

                @if (request()->hasAny(['q', 'city', 'min_price', 'max_price']))
                    <a href="{{ route('ads.index', $category ? ['category' => $category->slug] : []) }}"
                       class="btn btn-outline-secondary">Clear</a>
                @endif
            </div>
        </form>

        <div class="row">
            @forelse ($ads as $ad)
                <div class="col-lg-3 mb-4">
                    <a href="{{ route('ads.show', $ad) }}" class="text-decoration-none text-reset">
                        <div class="card h-100">
                        @if ($ad->images->isNotEmpty())
                            <img src="{{ asset('storage/' . $ad->images->first()->path) }}"
                                 class="card-img-top" style="height: 180px; object-fit: cover;" alt="{{ $ad->title }}">
                        @endif
                        <div class="card-body">
                            <h6 class="card-title">{{ $ad->title }}</h6>
                            <p class="mb-1 fw-bold">{{ $ad->price ? number_format($ad->price) . ' JOD' : 'Contact for price' }}</p>
                            <small class="text-muted">{{ $ad->city->name }}</small>
                        </div>
                    </div>
                    </a>

                </div>
            @empty
                <p class="text-muted">No ads found.</p>
            @endforelse
        </div>

        {{ $ads->links() }}
    </div>
@endsection
