<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BookCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'=>['required','string'],
            'author'=>['required','string'],
            'category'=>['required','string'],
            'isbn'=>['required','string','unique:books,isbn'],
            'stock_total'=>['required','integer','min:1'],
        ];
    }
}
