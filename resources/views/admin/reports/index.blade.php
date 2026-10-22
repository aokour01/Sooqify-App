@extends('layouts.app')

@section('content')
    <div class="container">
        @include('admin._nav')
        <h2 class="mb-4">Open reports</h2>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @forelse ($reports as $report)
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-2">
                            @if ($report->ad?->images->isNotEmpty())
                                <img src="{{ asset('storage/' . $report->ad->images->first()->path) }}"
                                     class="img-fluid rounded" style="height: 90px; object-fit: cover;" alt="">
                            @endif
                        </div>

                        <div class="col-lg-7">
                            <h5 class="mb-1">
                                @if ($report->ad)
                                    <a href="{{ route('ads.show', $report->ad) }}" target="_blank" class="text-decoration-none">
                                        {{ $report->ad->title }}
                                    </a>
                                @else
                                    <span class="text-muted">Ad no longer exists</span>
                                @endif
                            </h5>

                            <p class="mb-1">
                                <span class="badge bg-danger">{{ \App\Models\Report::REASONS[$report->reason] ?? $report->reason }}</span>
                            </p>

                            @if ($report->details)
                                <p class="small mb-1">"{{ $report->details }}"</p>
                            @endif

                            <small class="text-muted">
                                Reported by {{ $report->reporter->name }} · {{ $report->created_at->diffForHumans() }}
                            </small>
                        </div>

                        <div class="col-lg-3 mt-2">
                            <form method="POST" action="{{ route('admin.reports.dismiss', $report) }}" class="mb-2">
                                @csrf @method('PATCH')
                                <button class="btn btn-outline-secondary w-100 btn-sm">Dismiss report</button>
                            </form>
                            @can('ads.reject')
                                @if ($report->ad && $report->ad->status !== 'rejected')
                                    <form method="POST" action="{{ route('admin.reports.takedown', $report) }}"
                                          onsubmit="return confirm('Remove this ad?');">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-danger w-100 btn-sm">Remove ad</button>
                                    </form>
                                @endif
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted">No open reports.</p>
        @endforelse

        {{ $reports->links() }}
    </div>
@endsection
