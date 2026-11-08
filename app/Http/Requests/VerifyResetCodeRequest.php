<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Same email and code rules as ResetPasswordWithCodeRequest, without the password. */
class VerifyResetCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        if (is_string($this->input('email'))) {
            $merge['email'] = strtolower(trim($this->input('email')));
        }
        if (is_string($this->input('code'))) {
            $merge['code'] = preg_replace('/\s+/', '', $this->input('code')); // "123 456" -> "123456"
        }

        $this->merge($merge);
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'code' => ['required', 'digits:'.config('password_reset.code_length')],
        ];
    }
}
