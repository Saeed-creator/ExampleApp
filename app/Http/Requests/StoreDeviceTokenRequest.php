<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDeviceTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => [
                'required',
                'string',
                'max:512',
            ],

            'platform' => [
                'nullable',
                'string',
                Rule::in(['android', 'ios', 'web']),
            ],
        ];
    }
}
