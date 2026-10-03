<?php

namespace Database\Seeders;

use App\Enums\AccessLevel;
use App\Enums\ChineseZodiacAnimal;
use App\Enums\Language;
use App\Enums\WesternZodiacSign;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PredictionSeeder extends Seeder
{
    public function run(): void
    {
        DB::disableQueryLog();

        $westernSigns = WesternZodiacSign::cases();
        $chineseAnimals = ChineseZodiacAnimal::cases();
        $languages = Language::cases();
        $accessLevels = AccessLevel::cases();

        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $batch = [];
        $batchSize = 250;

        $createdAt = now()->toDateTimeString();

        for (
            $date = $startDate->copy();
            $date->lte($endDate);
            $date->addDay()
        ) {
            foreach ($westernSigns as $westernSign) {
                foreach ($chineseAnimals as $chineseAnimal) {
                    foreach ($languages as $language) {
                        foreach ($accessLevels as $accessLevel) {

                            $batch[] = [
                                'prediction_date' =>
                                    $date->toDateString(),

                                'western_zodiac_sign' =>
                                    $westernSign->value,

                                'chinese_zodiac_animal' =>
                                    $chineseAnimal->value,

                                'language' =>
                                    $language->value,

                                'access_level' =>
                                    $accessLevel->value,

                                'title' => sprintf(
                                    '%s %s Prediction',
                                    ucfirst($westernSign->value),
                                    ucfirst($chineseAnimal->value)
                                ),

                                'prediction_text' => sprintf(
                                    'This is the %s prediction for %s and %s on %s.',
                                    $accessLevel->value,
                                    $westernSign->value,
                                    $chineseAnimal->value,
                                    $date->toDateString()
                                ),

                                'created_at' => $createdAt,
                                'updated_at' => $createdAt,
                            ];

                            if (count($batch) >= $batchSize) {
                                $this->saveBatch($batch);

                                $batch = [];

                                gc_collect_cycles();
                            }
                        }
                    }
                }
            }
        }

        if (! empty($batch)) {
            $this->saveBatch($batch);
        }
    }

    private function saveBatch(array $batch): void
    {
        DB::table('predictions')->upsert(
            $batch,
            [
                'prediction_date',
                'western_zodiac_sign',
                'chinese_zodiac_animal',
                'language',
                'access_level',
            ],
            [
                'title',
                'prediction_text',
                'updated_at',
            ]
        );
    }
}