@extends('layouts.app')

@section('content')
    <div class="container">

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif


        <div class="bg-white rounded shadow-sm p-4 mb-5">
            <h2 class="mb-3">Find what you need</h2>
            <form method="GET" action="{{ route('ads.index') }}" class="row g-2">
                <div class="col-lg-6">
                    <input type="text" name="q" class="form-control form-control-lg" placeholder="What are you looking for?">
                </div>
                <div class="col-lg-4">
                    <select name="city" class="form-select form-select-lg">
                        <option value="">All cities</option>
                        @foreach ($cities as $city)
                            <option value="{{ $city->id }}">{{ $city->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2">
                    <button class="btn btn-primary btn-lg w-100">Search</button>
                </div>
            </form>
        </div>


        <h4 class="mb-3">Browse by category</h4>
        <div class="row row-cols-lg-6 g-3 mb-5">
            @foreach ($categories as $category)
                <div class="col">
                    <a href="{{ route('ads.index', ['category' => $category->slug]) }}" class="category-tile">
                        <span class="title">{{ $category->name }}</span>
                        <span class="icon"><i class="bi {{ $category->icon ?: 'bi-grid' }}"></i></span>
                    </a>
                </div>
            @endforeach

            <div class="col">
                <a href="{{ route('ads.index') }}" class="category-tile">
                    <span class="title">All Categories</span>
                    <span class="icon"><i class="bi bi-three-dots"></i></span>
                </a>
            </div>
        </div>



        <div class="d-flex justify-content-between align-items-center border-top mb-3">
            <h4 class="mb-0 mt-3">Latest ads</h4>
            <a href="{{ route('ads.index') }}">See all</a>
        </div>



        <div class="row">
            @forelse ($latestAds as $ad)
                <div class="col-lg-3 mb-4">
                    <a href="{{ route('ads.show', $ad) }}" class="text-decoration-none text-reset">
                        <div class="card h-100 shadow-sm">
                            @if ($ad->images->isNotEmpty())
                                <img src="{{ asset('storage/' . $ad->images->first()->path) }}"
                                     class="card-img-top p-2" style="height: 160px; object-fit: cover;"
                                     loading="lazy" alt="{{ $ad->title }}">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center text-muted"
                                     style="height: 160px;">No image</div>
                            @endif
                            <div class="card-body">
                                <h4 class="mb-2 fw-bold text-danger">{{ $ad->price ? number_format($ad->price) . ' JOD' : 'Contact for price' }}</h4>
                                <h5 class="card-title">{{ $ad->title }}</h5>
                                <small class="text-muted">{{ $ad->city->name }}</small>
                                <small class="ms-2">{{$ad->publishSince()}}</small>

                            </div>
                        </div>
                    </a>
                </div>
            @empty
                <p class="text-muted">No ads yet. Be the first to post one!</p>
            @endforelse
        </div>


            <div class="d-flex justify-content-between align-items-center border-top mb-3">
                <h4 class="mb-0 mt-3">Top Products</h4>
                <a href="{{ route('ads.index') }}">See all</a>
            </div>

            <div class="row">
                @forelse ($mostViewedAds as $mvad)
                    <div class="col-lg-3 mb-4">
                        <a href="{{ route('ads.show', $mvad) }}" class="text-decoration-none text-reset">
                            <div class="card h-100 shadow-sm">
                                @if ($mvad->images->isNotEmpty())
                                    <img src="{{ asset('storage/' . $mvad->images->first()->path) }}"
                                         class="card-img-top p-2" style="height: 160px; object-fit: cover;"
                                         loading="lazy" alt="{{ $mvad->title }}">
                                @else
                                    <div class="bg-light d-flex align-items-center justify-content-center text-muted"
                                         style="height: 160px;">No image</div>
                                @endif
                                <div class="card-body">
                                    <h4 class="mb-2 fw-bold text-danger">{{ $mvad->price ? number_format($mvad->price) . ' JOD' : 'Contact for price' }}</h4>
                                    <h5 class="card-title">{{ $mvad->title }}</h5>
                                    <small class="text-muted">{{ $mvad->city->name }}</small>
                                    <small class="d-block text-muted mt-1">
                                        <i class="bi bi-eye"></i> {{ $mvad->views }}
                                        <i class="bi bi-heart ms-2"></i> {{ $mvad->favorited_by_count }}
                                    </small>
                                    <small class="ms-2">{{$mvad->publishSince()}}</small>

                                </div>
                            </div>
                        </a>
                    </div>
                @empty
                    <p class="text-muted">No ads yet. Be the first to post one!</p>
                @endforelse
            </div>



    </div>
@endsection
