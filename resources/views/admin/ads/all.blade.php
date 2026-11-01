@extends('layouts.app')

@section('content')
    <div class="container">
        @include('admin._nav')

        <h2 class="mb-4">All ads</h2>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @php
            $badges = [
                'pending' => 'warning', 'active' => 'success', 'sold' => 'secondary',
                'rejected' => 'danger', 'expired' => 'dark', 'deleted' => 'primary'
            ];
        @endphp

        <form method="GET" action="{{ route('admin.ads.all') }}" class="row g-2 mb-3">
            <div class="col-md-6">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                       placeholder="Search by title, owner name or email">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All statuses</option>
                    @foreach (['pending', 'active', 'sold', 'rejected', 'expired', 'deleted', ] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary">Search</button>
                @if (request()->hasAny(['q', 'status']))
                    <a href="{{ route('admin.ads.all') }}" class="btn btn-outline-secondary">Reset</a>
                @endif
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                <tr>
                    <th>Title</th><th>Owner</th><th>Category</th><th>City</th>
                    <th>Status</th><th>Posted</th><th class="text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($ads as $ad)
                    <tr>
                        <td>
                            @if ($ad->trashed())
                                {{ $ad->title }}
                            @else
                                <a href="{{ route('ads.show', $ad) }}" class="text-decoration-none">{{ $ad->title }}</a>
                            @endif
                        </td>
                        <td>
                            {{ $ad->user->name }}
                            <div class="small text-muted">{{ $ad->user->email }}</div>
                        </td>
                        <td>{{ $ad->category->name }}</td>
                        <td>{{ $ad->city->name }}</td>
                        <td>
                            @if ($ad->trashed())
                                <span class="badge bg-dark">Deleted</span>
                            @else
                                <span class="badge bg-{{ $badges[$ad->status] ?? 'light' }}">{{ ucfirst($ad->status) }}</span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $ad->created_at->format('d M Y') }}</td>
                        <td class="text-end">
                            @if ($ad->trashed())
                                <form method="POST" action="{{ route('admin.ads.restore', $ad->id) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-outline-success">Restore</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.ads.destroy', $ad) }}" class="d-inline"
                                      onsubmit="return confirm('Remove this ad?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Remove</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted">No ads found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{ $ads->links() }}
    </div>
@endsection
