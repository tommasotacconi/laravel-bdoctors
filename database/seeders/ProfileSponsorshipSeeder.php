<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\Sponsorship;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Generator as Faker;


class ProfileSponsorshipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(Faker $faker): void
    {
        $profiles = Profile::all();

        $sponsorships = Sponsorship::all()->pluck('duration', 'id')->all();
        // Specify a year end for the demo app profiles
        $selectedYearEnd = Carbon::create(2024, 12, 31, 23, 59, 59);

        foreach ($profiles as $profile) {
            $sponsorshipsNumber = rand(0, 30);
            for ($i = 0; $i < $sponsorshipsNumber; $i++) {
                $sponsorshipId = $faker->randomKey($sponsorships);
                $sponsorshipDuration = $sponsorships[$sponsorshipId];

                do {
                    $randomActivationDate = $faker->dateTimeBetween($profile->created_at, $selectedYearEnd, 'Europe/Rome');
                    $randomActivationDate = CarbonImmutable::create($randomActivationDate);
                    // check if there is another sponsorship active in the computed period
                    $isPossible = true;
                    foreach ($profile->sponsorships as $sponsorship) {
                        $start = $sponsorship->pivot->start_date;
                        $end = $sponsorship->pivot->end_date;
                        if ($randomActivationDate->between($start, $end))
                            $isPossible = false;
                    }
                } while ($isPossible === false);

                $profile->sponsorships()->attach($sponsorshipId, [
                    'start_date' => $randomActivationDate,
                    'end_date' => $randomActivationDate->addHours($sponsorshipDuration),
                    'created_at' => $randomActivationDate
                ]);
            }
        }
    }
}
