@extends('layouts.app')

@section('content')
    <div class="container">

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0">Hello, {{ auth()->user()->name }}</h2>
                <small class="text-muted">Member since {{ auth()->user()->created_at->format('M Y') }}</small>
            </div>
            <a href="{{ route('ads.create') }}" class="btn btn-warning">Post an ad</a>
        </div>

        @php
            $cards = [
                ['Active ads',      $stats['active'],               route('my-ads.index'),    'bi-check-circle'],
                ['Pending review',  $stats['pending'],              route('my-ads.index'),    'bi-hourglass-split'],
                ['Total views',     number_format($stats['views']), route('my-ads.index'),    'bi-eye'],
                ['Saved favorites', $stats['favorites'],            route('favorites.index'), 'bi-heart'],
            ];

            $badges = [
                'pending' => 'warning', 'active' => 'success', 'sold' => 'secondary',
                'rejected' => 'danger', 'expired' => 'dark',
            ];
        @endphp

        {{-- Number cards --}}
        <div class="row g-3 mb-4">
            @foreach ($cards as $card)
                <div class="col-6 col-md-3">
                    <a href="{{ $card[2] }}" class="text-decoration-none text-reset">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <div class="text-muted small"><i class="bi {{ $card[3] }} me-1"></i>{{ $card[0] }}</div>
                                <div class="fs-2 fw-bold">{{ $card[1] }}</div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        <div class="row g-4">
            {{-- Needs attention --}}
            <div class="col-lg-5">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white fw-bold">Needs your attention</div>
                    <ul class="list-group list-group-flush">

                        @foreach ($rejected as $ad)
                            <li class="list-group-item">
                                <span class="badge bg-danger me-1">Rejected</span>
                                <a href="{{ route('my-ads.edit', $ad) }}" class="text-decoration-none">{{ $ad->title }}</a>
                                @if ($ad->rejection_reason)
                                    <div class="small text-danger mt-1">{{ $ad->rejection_reason }}</div>
                                @endif
                                <div class="small mt-1">
                                    <a href="{{ route('my-ads.edit', $ad) }}">Fix and resubmit</a>
                                </div>
                            </li>
                        @endforeach

                        @foreach ($needsRenewal as $ad)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <a href="{{ route('ads.show', $ad) }}" class="text-decoration-none">{{ $ad->title }}</a>
                                    <div class="small text-muted">
                                        {{ $ad->status === 'expired' ? 'Expired' : 'Expires' }}
                                        {{ $ad->expires_at?->diffForHumans() }}
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('my-ads.renew', $ad) }}">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-outline-primary">Renew</button>
                                </form>
                            </li>
                        @endforeach

                        @if ($rejected->isEmpty() && $needsRenewal->isEmpty())
                            <li class="list-group-item text-muted">Nothing needs your attention right now.</li>
                        @endif
                    </ul>
                </div>

                {{-- Shortcuts --}}
                <div class="d-grid gap-2">
                    <a href="{{ route('my-ads.index') }}" class="btn btn-outline-secondary">Manage my ads</a>
                    <a href="{{ route('favorites.index') }}" class="btn btn-outline-secondary">My favorites</a>
                </div>
            </div>

            {{-- Recent ads --}}
            <div class="col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between">
                        <span class="fw-bold">My latest ads</span>
                        <a href="{{ route('my-ads.index') }}" class="small">See all</a>
                    </div>

                    <ul class="list-group list-group-flush">
                        @forelse ($recent as $ad)
                            <li class="list-group-item d-flex align-items-center">
                                @if ($ad->images->isNotEmpty())
                                    <img src="{{ asset('storage/' . $ad->images->first()->path) }}" class="rounded me-3"
                                         style="width: 60px; height: 45px; object-fit: cover;" alt="">
                                @endif

                                <div class="flex-grow-1">
                                    <a href="{{ route('ads.show', $ad) }}" class="text-decoration-none">{{ $ad->title }}</a>
                                    <div class="small text-muted">{{ $ad->views }} views · {{ $ad->created_at->diffForHumans() }}</div>
                                </div>

                                <span class="badge bg-{{ $badges[$ad->status] ?? 'light' }}">{{ ucfirst($ad->status) }}</span>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">
                                You haven't posted anything yet.
                                <a href="{{ route('ads.create') }}">Post your first ad</a>.
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
