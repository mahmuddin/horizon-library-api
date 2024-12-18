<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() != null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'max:100'],
            'email' => ['nullable', 'email', 'max:100'],
            'username' => ['nullable', 'max:100'],
            'password' => ['nullable', 'min:5', 'max:100'],
        ];
    }
    protected function failedValidation($validator)
    {
        throw new HttpResponseException(response([
            'errors' => $validator->getMessageBag(),
        ], 400));
    }
}
