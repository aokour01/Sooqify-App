@extends('layouts.app')

@section('content')
    <div class="container">

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

            <div class="col-lg-4">

                <aside class="mb-4">
                    @php $keep = request()->except(['category', 'page']); @endphp

                    <button class="btn btn-outline-secondary w-100 d-lg-none mb-3" type="button"
                            data-bs-toggle="collapse" data-bs-target="#categorySidebar">
                        <i class="bi bi-list me-1"></i> Categories
                    </button>

                    <div class="collapse d-lg-block" id="categorySidebar">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white fw-bold">Categories</div>

                            <div class="card-body py-2">
                                <a href="{{ route('ads.index', $keep) }}"
                                   class="d-block py-1 text-decoration-none {{ $category ? 'text-muted' : 'fw-bold text-dark' }}">
                                    All categories
                                </a>

                                @foreach ($sidebar['chain'] as $node)
                                    <a href="{{ route('ads.index', array_merge($keep, ['category' => $node->slug])) }}"
                                       class="d-block py-1 text-decoration-none {{ $category && $node->id === $category->id ? 'fw-bold text-dark' : 'text-muted' }}"
                                       style="padding-left: {{ $loop->iteration * 14 }}px;">
                                        {{ $node->name }}
                                    </a>
                                @endforeach

                                @php $depth = count($sidebar['chain']) + 1; @endphp

                                @foreach ($sidebar['items'] as $item)
                                    <a href="{{ route('ads.index', array_merge($keep, ['category' => $item->slug])) }}"
                                       class="d-flex justify-content-between py-1 text-decoration-none {{ $category && $item->id === $category->id ? 'fw-bold text-dark' : 'text-muted' }}"
                                       style="padding-left: {{ $depth * 14 }}px;">
                                        <span>{{ $item->name }}</span>
                                        <span class="small">{{ number_format($sidebar['counts'][$item->id] ?? 0) }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </aside>


            </div>

            <div class="col-lg-8">
                @if ($category)
                    @php
                        $trail = array_merge($category->ancestors(), [$category]);
                        $keep  = request()->except(['category', 'page']);
                    @endphp

                    <div class="card mb-3">
                        <div class="card-body py-2 d-flex flex-wrap align-items-center gap-2">
                            <a href="{{ route('ads.index') }}" class="text-decoration-none me-2">Reset</a>

                            @foreach ($trail as $crumb)
                                @php
                                    $parentSlug = $loop->first ? null : $trail[$loop->index - 1]->slug;
                                    $removeUrl  = route('ads.index', $parentSlug
                                        ? array_merge($keep, ['category' => $parentSlug])
                                        : $keep);
                                @endphp

                                <span class="filter-chip">
                    <a href="{{ route('ads.index', array_merge($keep, ['category' => $crumb->slug])) }}">{{ $crumb->name }}</a>
                    <a href="{{ $removeUrl }}" class="remove" title="Remove {{ $crumb->name }}"
                       aria-label="Remove {{ $crumb->name }}"><i class="bi bi-x-circle-fill"></i></a>
                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                 @forelse ($ads as $ad)


                    <a href="{{ route('ads.show', $ad) }}" class="text-decoration-none text-reset">

                        <div class="card mb-3 shadow">
                            <div class="row">
                               <div class="col-lg-4">
                                   <small class="m-2">{{$ad->publishSince()}}</small>

                               @if ($ad->images->isNotEmpty())
                            <img src="{{ asset('storage/' . $ad->images->first()->path) }}"
                                 class="card-img-top m-2" style="height: 180px; object-fit: cover;" alt="{{ $ad->title }}">
                        @endif
                               </div>

                               <div class="col-lg-8">

                                  <div class="card-body">
                                    <h4 class="mb-2 pt-3 fw-bold text-danger">{{ $ad->price ? number_format($ad->price) . ' JOD' : 'Contact for price' }}</h4>
                                    <h5 class="card-title">{{ $ad->title }}</h5>
                                    <h6><span class="badge bg-secondary">{{$ad->condition}}</span></h6>

                                    <p class="text-muted border-top">{{ $ad->city->name }}</p>
                                  </div>
                               </div>
                            </div>
                        </div>
                    </a>


                 @empty
                     <p class="text-muted">No ads found.</p>
                 @endforelse
            </div>

        </div>

        {{ $ads->links() }}
    </div>
@endsection
