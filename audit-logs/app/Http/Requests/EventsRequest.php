<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class EventsRequest extends FormRequest
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
            'actor_id'=>['sometimes','string'],
            'service'=>['required','string'],
            'action'=>['required','string'],
            'resource_type'=>['required','string'],
            'resource_id'=>['required','string'],
            'ip_address'=>['required','string'],
            'metadata'=>['required','array']
        ];
    }
}
