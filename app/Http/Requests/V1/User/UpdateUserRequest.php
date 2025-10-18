<?php

namespace App\Http\Requests\V1\User;

use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = auth()->id();

        return [
            'first_name'    => ['sometimes', 'string', 'max:255'],
            'last_name'     => ['sometimes', 'string', 'max:255'],
            'email'         => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'mobile'        => ['sometimes', 'string', 'max:20', Rule::unique('users', 'mobile')->ignore($userId)],
            'address'       => ['sometimes', 'string', 'max:500'],
            'profile_image' => ['sometimes', 'file', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'],
            'password'      => ['nullable', 'string', 'min:8', 'confirmed'],
        ];
    }

    /** @inheritDoc*/
    public function validated($key = null, $default = null): array
    {
        $data = parent::validated();

        if (!empty($data['password'])) {
            Arr::set($data, 'password', Hash::make($data['password']));
        } else {
            Arr::forget($data, 'password');
        }

        return $data;
    }
}
