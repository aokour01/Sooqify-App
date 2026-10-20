@extends('layouts.app')

@section('content')
    <div class="container" style="max-width: 760px;">
        <h2 class="mb-4">Edit ad</h2>

        @if ($ad->status === 'rejected' && $ad->rejection_reason)
            <div class="alert alert-danger">Rejected: {{ $ad->rejection_reason }}</div>
        @endif

        @if ($ad->status === 'active')
            <div class="alert alert-info">
                This ad is live. If you change it, it will be hidden until a moderator reviews it again.
            </div>
        @endif

        <form method="POST" action="{{ route('my-ads.update', $ad) }}" enctype="multipart/form-data" novalidate>
            @csrf
            @method('PUT')

            @include('ads._fields')

            {{-- Current images --}}
            @if ($ad->images->isNotEmpty())
                <label class="form-label">Current images (tick to remove)</label>
                <div class="row mb-3">
                    @foreach ($ad->images as $image)
                        <div class="col-lg-3 mb-2">
                            <img src="{{ asset('storage/' . $image->path) }}" class="img-fluid rounded mb-1"
                                 style="height: 90px; width: 100%; object-fit: cover;" alt="">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="remove_images[]"
                                       value="{{ $image->id }}" id="rm{{ $image->id }}"
                                    @checked(in_array($image->id, old('remove_images', [])))>
                                <label class="form-check-label small" for="rm{{ $image->id }}">Remove</label>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- New images --}}
            <div class="mb-4">
                <label for="images" class="form-label">Add images (up to 8 in total, max 2 MB each)</label>
                <input type="file" id="images" name="images[]" multiple accept="image/jpeg,image/png,image/webp"
                       class="form-control @error('images') is-invalid @enderror @error('images.*') is-invalid @enderror">
                @error('images') <div class="invalid-feedback">{{ $message }}</div> @enderror
                @error('images.*') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn btn-primary">Save changes</button>
            <a href="{{ route('my-ads.index') }}" class="btn btn-link">Cancel</a>
        </form>
    </div>
@endsection
