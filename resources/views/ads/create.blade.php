@extends('layouts.app')

@section('content')
    <div class="container" style="max-width: 760px;">
        <h2 class="mb-4">Post an ad</h2>

        <form method="POST" action="{{ route('ads.store') }}" enctype="multipart/form-data" novalidate>
            @csrf

            @include('ads._fields')

            <div class="mb-4">
                <label for="images" class="form-label">Images (up to 8, max 2 MB each)</label>
                <input type="file" id="images" name="images[]" multiple accept="image/jpeg,image/png,image/webp"
                       class="form-control @error('images') is-invalid @enderror @error('images.*') is-invalid @enderror">
                @error('images') <div class="invalid-feedback">{{ $message }}</div> @enderror
                @error('images.*') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn btn-primary">Submit ad</button>
        </form>
    </div>
@endsection
