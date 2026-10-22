@extends('layouts.app')

@section('content')
    <div class="container">
        @include('admin._nav')

        <h2 class="mb-4">Users</h2>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <form method="GET" action="{{ route('admin.users.index') }}" class="row g-2 mb-3">
            <div class="col-md-6">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search by name or email">
            </div>
            <div class="col-md-3">
                <select name="role" class="form-select">
                    <option value="">All roles</option>
                    @foreach (['user', 'moderator', 'admin'] as $role)
                        <option value="{{ $role }}" @selected(request('role') === $role)>{{ ucfirst($role) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary">Search</button>
                @if (request()->hasAny(['q', 'role']))
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Reset</a>
                @endif
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Ads</th>
                    <th>Joined</th>
                    <th>Verified</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($users as $user)
                    @php $role = $user->roles->pluck('name')->first(); @endphp
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if ($role === 'admin')
                                <span class="badge bg-dark">Admin</span>
                            @else
                                <form method="POST" action="{{ route('admin.users.role', $user) }}" class="d-flex gap-2">
                                    @csrf @method('PATCH')
                                    <select name="role" class="form-select form-select-sm" style="width: 130px;">
                                        <option value="user" @selected($role === 'user')>User</option>
                                        <option value="moderator" @selected($role === 'moderator')>Moderator</option>
                                    </select>
                                    <button class="btn btn-sm btn-outline-primary">Save</button>
                                </form>
                            @endif
                        </td>
                        <td>{{ $user->ads_count }}</td>
                        <td class="small text-muted">{{ $user->created_at->format('d M Y') }}</td>
                        <td>
                            @if ($user->email_verified_at)
                                <span class="text-success">Verified</span>
                            @else
                                <span class="text-warning">Not yet</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-muted">No users found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{ $users->links() }}
    </div>
@endsection
