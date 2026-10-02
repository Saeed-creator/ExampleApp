<?php

namespace App\Models;
use App\Enums\WesternZodiacSign;
use App\Enums\ChineseZodiacAnimal;
use App\Enums\AccessLevel;
use App\Enums\Language;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'birth_date',
        'western_zodiac_sign',
        'chinese_zodiac_animal',
        'language',
        'access_level',
        'timezone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'western_zodiac_sign' => WesternZodiacSign::class,
            'chinese_zodiac_animal' => ChineseZodiacAnimal::class,
            'language' => Language::class,
            'access_level' => AccessLevel::class,
            'birth_date' => 'date',
        ];
    }
}
