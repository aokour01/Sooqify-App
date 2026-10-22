@extends('layouts.app')

@section('content')
    <div class="container">
        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                @foreach ($ad->category->ancestors() as $parent)
                    <li class="breadcrumb-item">
                        <a href="{{ route('ads.index', ['category' => $parent->slug]) }}">{{ $parent->name }}</a>
                    </li>
                @endforeach
                <li class="breadcrumb-item">
                    <a href="{{ route('ads.index', ['category' => $ad->category->slug]) }}">{{ $ad->category->name }}</a>
                </li>
            </ol>
        </nav>

        @if ($ad->status !== \App\Models\Ad::STATUS_ACTIVE)
            <div class="alert alert-warning">
                This ad is <strong>{{ $ad->status }}</strong> and is not visible to the public.
                @if ($ad->rejection_reason) Reason: {{ $ad->rejection_reason }} @endif
            </div>
        @endif

        <div class="row">
            {{-- Left: images and description --}}
            <div class="col-lg-8 mb-4">
                @if ($ad->images->isNotEmpty())
                    <div id="adCarousel" class="carousel slide mb-3">
                        <div class="carousel-inner">
                            @foreach ($ad->images as $image)
                                <div class="carousel-item @if ($loop->first) active @endif">
                                    <img src="{{ asset('storage/' . $image->path) }}" class="d-block w-100 rounded"
                                         style="max-height: 480px; object-fit: contain; background: #f1f1f1;" alt="{{ $ad->title }}">
                                </div>
                            @endforeach
                        </div>
                        @if ($ad->images->count() > 1)
                            <button class="carousel-control-prev" type="button" data-bs-target="#adCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon"></span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#adCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon"></span>
                            </button>
                        @endif
                    </div>
                @endif

                <h3>{{ $ad->title }}</h3>
                <p class="text-muted">
                    {{ $ad->city->name }} · Posted {{ ($ad->published_at ?? $ad->created_at)->diffForHumans() }}
                    · {{ $ad->views }} views
                </p>

                <h5 class="mt-4">Description</h5>
                <p style="white-space: pre-line;">{{ $ad->description }}</p>
            </div>

            {{-- Right: price and seller --}}
            <div class="col-lg-4 mb-4">
                <div class="card mb-3">
                    <div class="card-body">
                        <h3 class="mb-1">{{ $ad->price ? number_format($ad->price) . ' JOD' : 'Contact for price' }}</h3>
                        @if ($ad->is_negotiable) <span class="badge bg-info">Negotiable</span> @endif
                        @if ($ad->condition) <span class="badge bg-secondary">{{ ucfirst($ad->condition) }}</span> @endif
                        @auth
                            <form method="POST" action="{{ route('favorites.toggle', $ad) }}" class="mt-3">
                                @csrf
                                <button class="btn w-100 {{ $isFavorite ? 'btn-danger' : 'btn-outline-danger' }}">
                                    {{ $isFavorite ? '♥ Remove from favorites' : '♡ Save to favorites' }}
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-outline-danger w-100 mt-3">♡ Log in to save</a>
                        @endauth
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h6>Seller</h6>
                        <p class="mb-2">{{ $ad->user->name }}</p>
                        <p class="small text-muted">Member since {{ $ad->user->created_at->format('M Y') }}</p>

                        @if ($ad->contact_phone)
                            @auth
                                <button id="show-phone" type="button" class="btn btn-success w-100"
                                        data-url="{{ route('ads.phone', $ad) }}">
                                    Show phone number
                                </button>
                                <a id="phone-link" href="#" class="btn btn-success w-100 d-none"></a>
                            @else
                                <a href="{{ route('login') }}" class="btn btn-outline-success w-100">
                                    Log in to see the phone number
                                </a>
                            @endauth
                        @else
                            <p class="text-muted mb-0">The seller did not provide a phone number.</p>
                        @endif
                        @auth
                            @if (auth()->id() !== $ad->user_id && $ad->status === 'active')
                                <div class="mt-3">
                                    <a class="small text-muted" data-bs-toggle="collapse" href="#reportForm" role="button">
                                        Report this ad
                                    </a>

                                    <div class="collapse mt-2 @if ($errors->has('reason') || $errors->has('details')) show @endif" id="reportForm">
                                        <form method="POST" action="{{ route('reports.store', $ad) }}">
                                            @csrf
                                            <select name="reason" class="form-select form-select-sm mb-2 @error('reason') is-invalid @enderror" required>
                                                <option value="">Choose a reason</option>
                                                @foreach (\App\Models\Report::REASONS as $key => $label)
                                                    <option value="{{ $key }}" @selected(old('reason') === $key)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            @error('reason') <div class="invalid-feedback d-block mb-2">{{ $message }}</div> @enderror

                                            <textarea name="details" rows="2" maxlength="500"
                                                      class="form-control form-control-sm mb-2 @error('details') is-invalid @enderror"
                                                      placeholder="Details (optional)">{{ old('details') }}</textarea>
                                            @error('details') <div class="invalid-feedback d-block mb-2">{{ $message }}</div> @enderror

                                            <button class="btn btn-sm btn-outline-danger">Send report</button>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        @endauth

                    </div>
                </div>
            </div>
        </div>

        {{-- Related ads --}}
        @if ($related->isNotEmpty())
            <h5 class="mb-3">Similar ads</h5>
            <div class="row">
                @foreach ($related as $item)
                    <div class="col-lg-3 mb-4">
                        <a href="{{ route('ads.show', $item) }}" class="text-decoration-none text-reset">
                            <div class="card h-100">
                                @if ($item->images->isNotEmpty())
                                    <img src="{{ asset('storage/' . $item->images->first()->path) }}"
                                         class="card-img-top" style="height: 140px; object-fit: cover;" alt="">
                                @endif
                                <div class="card-body">
                                    <h6 class="card-title">{{ $item->title }}</h6>
                                    <small class="text-muted">{{ $item->city->name }}</small>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>


    <script>
        document.getElementById('show-phone')?.addEventListener('click', async function () {
            const btn = this;
            btn.disabled = true;

            try {
                const res = await fetch(btn.dataset.url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                });

                if (res.status === 429) {
                    btn.textContent = 'Too many requests, try again in a minute';
                    btn.disabled = false;
                    return;
                }
                if (!res.ok) throw new Error('Request failed');

                const data = await res.json();
                const link = document.getElementById('phone-link');
                link.href = 'tel:' + data.phone;
                link.textContent = 'Call ' + data.phone;
                link.classList.remove('d-none');
                btn.classList.add('d-none');
            } catch (e) {
                btn.textContent = 'Could not load the number, try again';
                btn.disabled = false;
            }
        });
    </script>
@endsection
