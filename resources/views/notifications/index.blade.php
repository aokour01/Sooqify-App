@extends('layouts.app')

@section('content')
    <div class="container" style="max-width: 760px;">

        @php
            $icons = [
                'approved' => 'bi-check-circle text-success',
                'rejected' => 'bi-x-circle text-danger',
                'expired'  => 'bi-clock-history text-secondary',
                'report'   => 'bi-flag text-warning',
                'pending'  => 'bi-hourglass-split text-warning',
            ];
        @endphp

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0">Notifications</h2>

            @if ($notifications->isNotEmpty())
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-secondary">Mark all as read</button>
                </form>
            @endif
        </div>

        <div class="list-group shadow-sm">
            @forelse ($notifications as $notification)
                <a href="{{ route('notifications.open', $notification->id) }}"
                   class="list-group-item list-group-item-action d-flex align-items-start {{ $notification->read_at ? '' : 'bg-light fw-semibold' }}">
                    <i class="bi {{ $icons[$notification->data['type']] ?? 'bi-bell' }} fs-4 me-3"></i>
                    <div>
                        <div>{{ $notification->data['message'] }}</div>
                        <small class="text-muted fw-normal">{{ $notification->created_at->diffForHumans() }}</small>
                    </div>
                </a>
            @empty
                <div class="list-group-item text-muted">You have no notifications yet.</div>
            @endforelse
        </div>

        <div class="mt-3">{{ $notifications->links() }}</div>
    </div>
@endsection
