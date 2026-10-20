@extends('layouts.app')

@section('content')
    <div class="container">
        @include('admin._nav')
        <h2 class="mb-4">Pending ads</h2>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        @forelse ($ads as $ad)
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-3">
                            @if ($ad->images->isNotEmpty())
                                <img src="{{ asset('storage/' . $ad->images->first()->path) }}"
                                     class="img-fluid rounded" style="max-height: 160px; object-fit: cover;" alt="">
                            @endif
                        </div>

                        <div class="col-lg-6">
                            <h5>{{ $ad->title }}</h5>
                            <p class="mb-1 text-muted">
                                {{ $ad->category->full_path }} · {{ $ad->city->name }}
                            </p>
                            <p class="mb-1 fw-bold">{{ $ad->price ? number_format($ad->price) . ' JOD' : 'No price' }}</p>
                            <p class="small">{{ \Illuminate\Support\Str::limit($ad->description, 200) }}</p>
                            <small class="text-muted">By {{ $ad->user->name }} · {{ $ad->created_at->diffForHumans() }}</small>
                        </div>

                        <div class="col-lg-3">
                            @can('ads.approve')
                                <form method="POST" action="{{ route('admin.ads.approve', $ad) }}" class="mb-2">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-success w-100">Approve</button>
                                </form>
                            @endcan

                            @can('ads.reject')
                                <form method="POST" action="{{ route('admin.ads.reject', $ad) }}">
                                    @csrf @method('PATCH')
                                    <input type="text" name="rejection_reason" class="form-control mb-2"
                                           placeholder="Reason for rejection" required maxlength="255">
                                    <button class="btn btn-outline-danger w-100">Reject</button>
                                </form>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted">No ads are waiting for approval.</p>
        @endforelse

        {{ $ads->links() }}
    </div>
@endsection
