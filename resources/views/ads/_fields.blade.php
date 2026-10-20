@php($ad ??= null)

<div class="mb-3">
    <label for="title" class="form-label">Title</label>
    <input type="text" id="title" name="title" value="{{ old('title', $ad?->title) }}"
           class="form-control @error('title') is-invalid @enderror" maxlength="120" required>
    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="row">
    <div class="col-lg-6 mb-3">
        <label for="category_id" class="form-label">Category</label>
        <select id="category_id" name="category_id"
                class="form-select @error('category_id') is-invalid @enderror" required>
            <option value="">Choose a category</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $ad?->category_id) == $category->id)>
                    {{ $category->full_path }}
                </option>
            @endforeach
        </select>
        @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-lg-6 mb-3">
        <label for="city_id" class="form-label">City</label>
        <select id="city_id" name="city_id"
                class="form-select @error('city_id') is-invalid @enderror" required>
            <option value="">Choose a city</option>
            @foreach ($cities as $city)
                <option value="{{ $city->id }}" @selected(old('city_id', $ad?->city_id) == $city->id)>
                    {{ $city->name }}
                </option>
            @endforeach
        </select>
        @error('city_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row">
    <div class="col-lg-4 mb-3">
        <label for="price" class="form-label">Price in JOD (optional)</label>
        <input type="number" step="1" min="0" id="price" name="price" value="{{ old('price', $ad?->price) }}"
               class="form-control @error('price') is-invalid @enderror">
        @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
        <div class="form-check mt-2">
            <input type="checkbox" class="form-check-input" id="is_negotiable" name="is_negotiable" value="1"
                @checked(session()->hasOldInput() ? old('is_negotiable') : $ad?->is_negotiable)>
            <label class="form-check-label" for="is_negotiable">Negotiable</label>
        </div>
    </div>

    <div class="col-lg-4 mb-3">
        <label for="condition" class="form-label">Condition</label>
        <select id="condition" name="condition" class="form-select @error('condition') is-invalid @enderror">
            <option value="">Not specified</option>
            <option value="new"  @selected(old('condition', $ad?->condition) === 'new')>New</option>
            <option value="used" @selected(old('condition', $ad?->condition) === 'used')>Used</option>
        </select>
        @error('condition') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-lg-4 mb-3">
        <label for="contact_phone" class="form-label">Phone (optional)</label>
        <input type="text" id="contact_phone" name="contact_phone" value="{{ old('contact_phone', $ad?->contact_phone) }}"
               class="form-control @error('contact_phone') is-invalid @enderror" maxlength="20">
        @error('contact_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mb-3">
    <label for="description" class="form-label">Description</label>
    <textarea id="description" name="description" rows="6"
              class="form-control @error('description') is-invalid @enderror" required>{{ old('description', $ad?->description) }}</textarea>
    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
