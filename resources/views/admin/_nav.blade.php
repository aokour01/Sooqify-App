<ul class="nav nav-pills mb-4">
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
           href="{{ route('admin.dashboard') }}">Overview</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.ads.*') ? 'active' : '' }}"
           href="{{ route('admin.ads.index') }}">Pending ads</a>
    </li>
    @can('reports.view')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}"
               href="{{ route('admin.reports.index') }}">Reports</a>
        </li>
    @endcan
</ul>
