<?php

namespace App\Http\Controllers;

use App\Models\Prediction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PredictionController extends Controller
{
    public function getPrediction(Request $request): JsonResponse
{
    $user = $request->user();

    $predictionDate = now($user->timezone)->toDateString();

    $prediction = Prediction::query()
        ->whereDate('prediction_date', $predictionDate)
        ->where(
            'western_zodiac_sign',
            $user->western_zodiac_sign
        )
        ->where(
            'chinese_zodiac_animal',
            $user->chinese_zodiac_animal
        )
        ->where(
            'language',
            $user->language
        )
        ->where(
            'access_level',
            $user->access_level
        )
        ->first();

    if (! $prediction) {
        return response()->json([
            'message' => 'Prediction not found.',
            'date' => $predictionDate,
        ], 404);
    }

    return response()->json([
        'message' => 'Prediction retrieved successfully.',
        'data' => [
            'prediction_date' =>
                $prediction->prediction_date,

            'western_zodiac_sign' =>
                $prediction->western_zodiac_sign,

            'chinese_zodiac_animal' =>
                $prediction->chinese_zodiac_animal,

            'language' =>
                $prediction->language,

            'access_level' =>
                $prediction->access_level,

            'title' =>
                $prediction->title,

            'prediction_text' =>
                $prediction->prediction_text,
        ],
    ]);
}
}