<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AuthorUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'website' => ['nullable', 'url'],
            'bio' => ['nullable'],
            'profile_image' => ['nullable'],
            'social_media' => ['nullable', 'json'],
            'nationality' => ['nullable'],
            'birth_date' => ['nullable', 'date'],
            'categories' => ['nullable', 'array'],
        ];
    }


    protected function failedValidation($validator)
    {
        throw new HttpResponseException(response([
            'errors' => $validator->getMessageBag(),
        ], 400));
    }
}
