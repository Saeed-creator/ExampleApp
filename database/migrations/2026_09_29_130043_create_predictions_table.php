<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('predictions', function (Blueprint $table) {
            $table->id();

            $table->string('western_zodiac_sign', 20);
            $table->string('chinese_zodiac_animal', 20);

            $table->date('prediction_date');

            $table->string('language', 5);

            $table->string('access_level', 10);

            $table->string('title')->nullable();
            $table->text('prediction_text');

            $table->timestamps();

            $table->unique(
                [
                    'prediction_date',
                    'western_zodiac_sign',
                    'chinese_zodiac_animal',
                    'language',
                    'access_level',
                ],
                'predictions_lookup_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('predictions');
    }
};
