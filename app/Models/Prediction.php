<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Enums\AccessLevel;
use App\Enums\WesternZodiacSign;
use App\Enums\ChineseZodiacAnimal;

use App\Enums\Language;

class Prediction extends Model
{
    protected $fillable = [
        'western_zodiac_sign',
        'chinese_zodiac_animal',
        'prediction_date',
        'language',
        'access_level',
        'title',
        'prediction_text',
    ];

    protected function casts(): array
    {
        return [
            'western_zodiac_sign' => WesternZodiacSign::class,
            'chinese_zodiac_animal' => ChineseZodiacAnimal::class,
            'language' => Language::class,
            'access_level' => AccessLevel::class,
            'prediction_date' => 'date',
        ];
    }
}