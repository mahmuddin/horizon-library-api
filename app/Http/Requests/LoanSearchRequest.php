<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoanSearchRequest extends FormRequest
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
            'page_size' => ['nullable', 'integer'],
            'member_id' => ['nullable', 'exists:users,id'],
            'librarian_id' => ['nullable', 'exists:users,id'],
            'loan_date_start' => ['nullable', 'date_format:Y-m-d H:i:s', 'before_or_equal:loan_date_end'],
            'loan_date_end' => ['required_with:loan_date_start', 'date_format:Y-m-d H:i:s'],
            'return_date_start' => ['nullable', 'date_format:Y-m-d H:i:s', 'before_or_equal:return_date_end'],
            'return_date_end' => ['required_with:return_date_start', 'date_format:Y-m-d H:i:s'],
        ];
    }

    protected function failedValidation($validator)
    {
        throw new HttpResponseException(response([
            'errors' => $validator->getMessageBag(),
        ], 400));
    }
}
