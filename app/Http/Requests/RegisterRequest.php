<?php

namespace App\Http\Requests;

use App\Enums\WesternZodiacSign;
use App\Enums\ChineseZodiacAnimal;
use App\Enums\Language;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                Password::min(8),
            ],

            'western_zodiac_sign' => [
                'required',
                new Enum(WesternZodiacSign::class),
            ],

            'chinese_zodiac_animal' => [
                'required',
                new Enum(ChineseZodiacAnimal::class),
            ],

            'language' => [
                'required',
                new Enum(Language::class),
            ],

            'timezone' => [
                'required',
                'timezone',
            ],
        ];
    }
}