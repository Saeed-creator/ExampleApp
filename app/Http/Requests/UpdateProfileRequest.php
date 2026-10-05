<?php

namespace App\Http\Requests;

use App\Enums\ChineseZodiacAnimal;
use App\Enums\Language;
use App\Enums\WesternZodiacSign;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'birth_date' => [
                'sometimes',
                'required',
                'date',
                'before:today',
            ],

            'language' => [
                'sometimes',
                'required',
                new Enum(Language::class),
            ],

            'western_zodiac_sign' => [
                'sometimes',
                'required',
                new Enum(WesternZodiacSign::class),
            ],

            'chinese_zodiac_animal' => [
                'sometimes',
                'required',
                new Enum(ChineseZodiacAnimal::class),
            ],
        ];
    }
}