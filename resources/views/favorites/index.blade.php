@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 class="mb-4">My favorites</h2>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

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
                <p class="text-muted">You haven't saved any ads yet.</p>
            @endforelse
        </div>

        {{ $ads->links() }}
    </div>
@endsection
