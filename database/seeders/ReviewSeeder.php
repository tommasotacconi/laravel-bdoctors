<?php

namespace Database\Seeders;

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

            $meanVote =  rand(0, 5);
            $maxDeviation = min(abs($meanVote - 0), abs($meanVote - 5));
            for ($i = 0; $i < ($faker->numberBetween(20, 40)); $i++) {
                $reviewData = [
                    'vote' => $meanVote + $faker->randomElement([1, -1]) * $maxDeviation,
                    'content' => $faker->realText(rand(50, 150)),
                    'last_name' => $ln = $faker->lastName(),
                    'first_name' => $fn = $faker->firstName(),
                    'email' => str_replace(' ', '', strtolower("$fn.$ln@")) . $faker->safeEmailDomain(),
                ];
                // Generate random date, excluding non existing hour due to DST
                $DST_from = Carbon::create(2024, 3, 31, 2, 0, 0);
                $DST_to = Carbon::create(2024, 3, 31, 3, 0, 0);
                do {
                    $startDate = Carbon::create(2024, 1, 1, 0, 0, 0);
                    $endDate = Carbon::create(2024, 12, 31, 23, 59, 59);
                    $randomDate = $faker->dateTimeBetween($startDate, $endDate);
                    $isNotValid = Carbon::parse($randomDate)->isBetween($DST_from, $DST_to);
                    \Log::info("sponsorship start date is " . ($isNotValid ? 'not' : '') . " valid", []);
                } while ($isNotValid);
                $reviewData['created_at'] = $randomDate;
                $profile->reviews()->save((new Review())->fill($reviewData));
            }
        });
    }
}
