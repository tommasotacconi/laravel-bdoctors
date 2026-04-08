<?php

namespace Database\Seeders;

use App\Helpers\TimeHelper;
use Carbon\Carbon;
use App\Models\Profile;
use App\Models\Review;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Generator as Faker;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(Faker $faker): void
    {
        Profile::all()->each(function ($profile) use ($faker) {
            if ($profile->id > 100 && $profile->id < 240) return;

            [$min, $max] = [1, 5]; // Votes range
            $meanVote =  rand($min, $max);
            $maxDeviation = min(abs($meanVote - $min), abs($meanVote - $max));
            $endDate = TimeHelper::computeAppTime()->endOfYear();
            $reviews = [];

            for ($i = 0; $i < ($faker->numberBetween(20, 40)); $i++) {
                $reviews[] = [
                    'vote' => $meanVote + $faker->randomElement([1, -1]) * $maxDeviation,
                    'content' => $faker->realText(rand(50, 150)),
                    'last_name' => $ln = $faker->lastName(),
                    'first_name' => $fn = $faker->firstName(),
                    'email' => str_replace(' ', '', strtolower("$fn.$ln@")) . $faker->safeEmailDomain(),
                    'created_at' => $faker->dateTimeBetween($profile->created_at, $endDate)
                ];
            }

            $profile->reviews()->createMany($reviews);
        });
    }
}
