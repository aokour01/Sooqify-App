@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">My ads</h2>
        </div>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @php
            $badges = [
                'pending'  => 'warning',
                'active'   => 'success',
                'sold'     => 'secondary',
                'rejected' => 'danger',
                'expired'  => 'dark',
            ];
        @endphp

        @forelse ($ads as $ad)
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-lg-2">
                            @if ($ad->images->isNotEmpty())
                                <img src="{{ asset('storage/' . $ad->images->first()->path) }}"
                                     class="img-fluid rounded" style="height: 90px; object-fit: cover;" alt="">
                            @endif
                        </div>

                        <div class="col-lg-6">
                            <h5 class="mb-1">
                                <a href="{{ route('ads.show', $ad) }}" class="text-decoration-none">{{ $ad->title }}</a>
                            </h5>
                            <span class="badge bg-{{ $badges[$ad->status] ?? 'light' }}">{{ ucfirst($ad->status) }}</span>
                            <small class="text-muted ms-2">
                                {{ $ad->city->name }} · {{ $ad->views }} views · {{ $ad->created_at->diffForHumans() }}
                            </small>

                            @if ($ad->status === 'rejected' && $ad->rejection_reason)
                                <div class="small text-danger mt-1">Reason: {{ $ad->rejection_reason }}</div>
                            @endif
                        </div>

                        <div class="col-lg-4 text-md-end mt-2">
                            @if (in_array($ad->status, ['pending', 'active', 'rejected']))
                                <a href="{{ route('my-ads.edit', $ad) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            @endif

                            @if ($ad->status === 'active')
                                <form method="POST" action="{{ route('my-ads.sold', $ad) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-outline-success">Mark as sold</button>
                                </form>
                            @endif

                            @if ($ad->canBeRenewed())
                                <form method="POST" action="{{ route('my-ads.renew', $ad) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-outline-primary">Renew</button>
                                </form>
                            @endif

                            @if ($ad->status === 'active' && $ad->expires_at)
                                <div class="small text-muted mt-1">Expires {{ $ad->expires_at->diffForHumans() }}</div>
                            @endif
                            <form method="POST" action="{{ route('my-ads.destroy', $ad) }}" class="d-inline"
                                  onsubmit="return confirm('Delete this ad?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted">You haven't posted any ads yet.</p>
        @endforelse

        {{ $ads->links() }}
    </div>
@endsection
