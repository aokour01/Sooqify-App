<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name') }}</title>

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-lg">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">

                    <ul class="navbar-nav ms-auto align-items-md-center">
                        <li class="nav-item me-lg-2 my-1">
                            <a href="{{ route('ads.create') }}" class="btn btn-warning">Post an ad</a>
                        </li>
                    </ul>


                    <ul class="navbar-nav ms-auto align-items-md-center">


                        @canany(['ads.approve', 'ads.reject'])
                            <li class="nav-item me-lg-2 my-1">
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Admin</a>
                            </li>
                        @endcanany

                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('my-ads.index') }}">
                                    My ads
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('favorites.index') }}">
                                    <i class="bi bi-heart"></i>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('notifications.index') }}" title="Notifications">
                                    <i class="bi bi-bell"></i>
                                    @if ($unreadNotifications > 0)
                                        <span class="badge rounded-pill bg-danger">{{ $unreadNotifications }}</span>
                                    @endif
                                </a>
                            </li>

                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                                   data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>
    <footer>
        <div class="container border-top">
            <div class="row">
                <div class="col">
                    <h4 class="ms-2 mt-2 fw-bold ">Sooqify <small>Ltd &copy; </small></h4>
                    <h6 class=" d-inline ms-2">version 1.0.0</h6>
                </div>
                <div class="col">
                    <h4 class="ms-2 mt-2 fw-bold">About OpenSooq</h4>
                    <div class="row">
                        <div class="col-12">
                            <a href="" class="text-decoration-none"><p class="text-dark ms-2"> About us</p></a>
                        </div>
                        <div class="col-12">
                            <a href="" class="text-decoration-none"><p class="text-dark ms-2">Our Products</p></a>
                        </div>
                        <div class="col-12">
                            <a href="" class="text-decoration-none"><p class="text-dark ms-2">Sitemap</p></a>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <h4 class="ms-2 mt-2 fw-bold">Support</h4>
                    <div class="row">
                        <div class="col-12">
                            <a href="" class="text-decoration-none"><p class="text-dark ms-2">Help</p></a>
                        </div>
                        <div class="col-12">
                            <a href="" class="text-decoration-none"><p class="text-dark ms-2">Sales Team</p></a>
                        </div>
                        <div class="col-12">
                            <a href="" class="text-decoration-none"><p class="text-dark ms-2">Terms Of Use</p></a>
                        </div>
                        <div class="col-12">
                            <a href="" class="text-decoration-none"><p class="text-dark ms-2">Privacy Policy</p></a>
                        </div>
                    </div>

                </div>
                <div class="col">
                    <h4 class="ms-2 mt-2 fw-bold">Others</h4>
                    <div class="row">
                        <div class="col-12">
                            <a href="" class="text-decoration-none"><p class="text-dark ms-2">Sell Anything</p></a>
                        </div>
                        <div class="col-12">
                            <a href="" class="text-decoration-none"><p class="text-dark ms-2">Top Sold Cars On Site</p></a>
                        </div>
                        <div class="col-12">
                            <a href="" class="text-decoration-none"><p class="text-dark ms-2">Tags</p></a>
                        </div>
                        <div class="col-12">
                            <a href="" class="text-decoration-none"><p class="text-dark ms-2">Area Guide</p></a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </footer>
</body>
</html>
