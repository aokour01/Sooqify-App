@extends('layouts.app')

@section('content')
    <div class="container">
        @include('admin._nav')

        <h2 class="mb-4">Admin overview</h2>

        @php
            $totalAds = max($adCounts->sum(), 1);
            $barColors = [
                'active' => 'success', 'pending' => 'warning', 'sold' => 'secondary',
                'rejected' => 'danger', 'expired' => 'dark',
            ];
        @endphp

        {{-- Number cards --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <a href="{{ route('admin.ads.index') }}" class="text-decoration-none text-reset">
                    <div class="card shadow-sm h-100 {{ $stats['pending'] > 0 ? 'border-warning' : '' }}">
                        <div class="card-body">
                            <div class="text-muted small">Waiting for approval</div>
                            <div class="fs-2 fw-bold">{{ $stats['pending'] }}</div>
                        </div>
                    </div>
                </a>
            </div>

            @can('reports.view')
                <div class="col-6 col-lg-3">
                    <a href="{{ route('admin.reports.index') }}" class="text-decoration-none text-reset">
                        <div class="card shadow-sm h-100 {{ $stats['open_reports'] > 0 ? 'border-danger' : '' }}">
                            <div class="card-body">
                                <div class="text-muted small">Open reports</div>
                                <div class="fs-2 fw-bold">{{ $stats['open_reports'] }}</div>
                            </div>
                        </div>
                    </a>
                </div>
            @endcan

            <div class="col-6 col-lg-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small">Active ads</div>
                        <div class="fs-2 fw-bold">{{ $stats['active'] }}</div>
                        <div class="small text-muted">{{ $stats['posted_week'] }} posted in the last 7 days</div>
                    </div>
                </div>
            </div>

            @can('users.manage')
                <div class="col-6 col-lg-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted small">Users</div>
                            <div class="fs-2 fw-bold">{{ $stats['users'] }}</div>
                            <div class="small text-muted">{{ $stats['new_users'] }} joined in the last 7 days</div>
                        </div>
                    </div>
                </div>
            @endcan
        </div>

        <div class="row g-4 mb-4">
            {{-- Ads by status --}}
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white fw-bold">Ads by status</div>
                    <div class="card-body">
                        @foreach (['active', 'pending', 'sold', 'rejected', 'expired'] as $status)
                            @php($count = $adCounts[$status] ?? 0)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small">
                                    <span>{{ ucfirst($status) }}</span>
                                    <span>{{ $count }}</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-{{ $barColors[$status] }}"
                                         style="width: {{ round($count / $totalAds * 100) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Top categories --}}
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white fw-bold">Top categories (active ads)</div>
                    <ul class="list-group list-group-flush">
                        @forelse ($topCategories as $row)
                            <li class="list-group-item d-flex justify-content-between">
                                <span>{{ $row->category?->full_path ?? 'Unknown' }}</span>
                                <span class="badge bg-primary rounded-pill">{{ $row->total }}</span>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">No active ads yet.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <div class="row g-4">
            {{-- Oldest pending ads --}}
            <div class="col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between">
                        <span class="fw-bold">Oldest pending ads</span>
                        <a href="{{ route('admin.ads.index') }}" class="small">Open moderation</a>
                    </div>
                    <ul class="list-group list-group-flush">
                        @forelse ($latestPending as $ad)
                            <li class="list-group-item">
                                <div>{{ $ad->title }}</div>
                                <div class="small text-muted">
                                    {{ $ad->category->name }} · by {{ $ad->user->name }} · {{ $ad->created_at->diffForHumans() }}
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">Nothing is waiting for approval.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            {{-- Newest users --}}
            @can('users.manage')
                <div class="col-lg-5">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white fw-bold">Newest users</div>
                        <ul class="list-group list-group-flush">
                            @foreach ($latestUsers as $user)
                                <li class="list-group-item">
                                    <div>{{ $user->name }}</div>
                                    <div class="small text-muted">{{ $user->email }} · {{ $user->created_at->diffForHumans() }}</div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endcan
        </div>
    </div>
@endsection
