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
        public function scopeForUser(Builder $query, User $user, string $date): Builder
    {
        // Works whether the attribute is a plain string or a backed enum.
        $value = fn ($attribute) => $attribute instanceof \BackedEnum
            ? $attribute->value
            : $attribute;

        return $query
            ->whereDate('prediction_date', $date)
            ->where('western_zodiac_sign', $value($user->western_zodiac_sign))
            ->where('chinese_zodiac_animal', $value($user->chinese_zodiac_animal))
            ->where('language', $value($user->language))
            ->where('access_level', $value($user->access_level));
    }
}