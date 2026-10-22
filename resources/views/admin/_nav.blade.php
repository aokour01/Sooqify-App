<ul class="nav nav-pills mb-4">
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
           href="{{ route('admin.dashboard') }}">Overview</a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.ads.index') ? 'active' : '' }}"
           href="{{ route('admin.ads.index') }}">Pending ads</a>
    </li>

    @can('ads.delete-any')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.ads.all') ? 'active' : '' }}"
               href="{{ route('admin.ads.all') }}">All ads</a>
        </li>
    @endcan

    @can('reports.view')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}"
               href="{{ route('admin.reports.index') }}">Reports</a>
        </li>
    @endcan

    @can('users.manage')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
               href="{{ route('admin.users.index') }}">Users</a>
        </li>
    @endcan
</ul>
