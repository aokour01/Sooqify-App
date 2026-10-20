<?php

namespace App\Http\Requests;

class UpdateAdRequest extends StoreAdRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'remove_images'   => ['nullable', 'array'],
            'remove_images.*' => ['integer'],
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $ad = $this->route('ad');

            $remaining = $ad->images()
                ->whereNotIn('id', $this->input('remove_images', []))
                ->count();

            $new = count($this->file('images', []));

            if ($remaining + $new > 8) {
                $validator->errors()->add('images', 'An ad can have at most 8 images in total.');
            }
        });
    }
}
