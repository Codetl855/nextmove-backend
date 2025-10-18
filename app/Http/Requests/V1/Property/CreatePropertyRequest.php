<?php

namespace App\Http\Requests\V1\Property;

use Illuminate\Foundation\Http\FormRequest;

class CreatePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            // Basic property info
            'title'           => ['required', 'string', 'max:255'],
            'description'     => ['required', 'string'],
            'property_type'   => ['required', 'string', 'max:100'],
            'listing_type'    => ['required', 'string', 'max:100'], // e.g. rent/sale
            'property_label'  => ['nullable', 'string', 'max:100'], // e.g. Featured, Hot, etc.

            // Physical details
            'size'            => ['required', 'numeric', 'min:0'],
            'land_area'       => ['required', 'numeric', 'min:0'],
            'property_id'     => ['nullable', 'string', 'max:100'], // custom property reference ID
            'rooms'           => ['nullable', 'integer', 'min:0'],
            'bedrooms'        => ['nullable', 'integer', 'min:0'],
            'bathrooms'       => ['nullable', 'integer', 'min:0'],
            'garages'         => ['nullable', 'integer', 'min:0'],
            'garage_size'     => ['nullable', 'numeric', 'min:0'],
            'year_built'      => ['nullable', 'integer', 'min:1800', 'max:' . date('Y')],

            // Location 
            'address'         => ['required', 'string', 'max:500'],
            'zip_code'        => ['nullable', 'string', 'max:20'],
            'city'            => ['required', 'string', 'max:100'],
            'state'           => ['required', 'string', 'max:100'],
            'location'        => ['required', 'string', 'max:255'],

            // Pricing
            'price'           => ['required', 'numeric', 'min:0'],
            'terms'           => ['nullable', 'string', 'max:255'],

            // Media uploads
            'property_images'   => ['nullable', 'array'],
            'property_images.*' => ['file', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'], // 5 MB each

            'amenities'       => ['nullable', 'array'],
            'amenities.*'     => ['string', 'max:500']
        ];
    }

    public function attributes(): array
    {
        return [
            'property_images.*' => 'property image',
        ];
    }
}
