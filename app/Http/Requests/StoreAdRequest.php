<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

class StoreAdRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'title'         => ['required', 'string', 'max:120'],
            'description'   => ['required', 'string', 'min:20', 'max:1000'],
            'category_id'   => [
                'required', 'exists:categories,id',
                function ($attribute, $value, $fail) {
                    if (Category::where('parent_id', $value)->exists()) {
                        $fail('Please choose the most specific category.');
                    }
                },
            ],
            'city_id'       => ['required', 'exists:cities,id'],
            'price'         => ['nullable', 'integer', 'min:0', 'max:9999999'],
            'is_negotiable' => ['nullable', 'boolean'],
            'condition'     => ['required', 'in:new,used'],
            'contact_phone' => ['nullable', 'string', 'max:14'],
            'images'        => ['nullable', 'array', 'max:8'],
            'images.*'      => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
