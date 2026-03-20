<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\Sponsorship;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
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
            $addedSpons = [];
            for ($i = 0; $i < $sponsorshipsNumber; $i++) {
                $sponsorshipId = $faker->randomKey($sponsorships);
                $sponsorshipDuration = $sponsorships[$sponsorshipId];

                do {
                    $start_date = $faker->dateTimeBetween($profile->created_at, $selectedYearEnd, 'Europe/Rome');
                    $start_date = CarbonImmutable::create($start_date);
                    $end_date = $start_date->addHours($sponsorshipDuration);
                    $created_at = Carbon::now()->subDays(Carbon::now()->diffInDays($selectedYearEnd) + 367);
                    // check if there is another sponsorship active in the computed period
                    $isPossible = true;
                    foreach ($addedSpons as ['start_date' => $start, 'end_date' => $end]) {
                        if ($start_date->between($start, $end) || $end_date->between($start, $end) || $start_date < $start && $end_date > $end) {
                            $isPossible = false;
                            break;
                        }
                    }
                } while ($isPossible === false);

                foreach (['start_date', 'end_date', 'created_at'] as $el) {
                    $sponDetails[$el] = $$el;
                }
                $profile->sponsorships()->attach($sponsorshipId, $sponDetails);
                $addedSpons[] = ['id' => $sponsorshipId, ...$sponDetails];
            }
            // print_r("\n\nprofile_id={$profile->id} sponsorships: " . json_encode($profile->sponsorships->map(fn($el) => $el->name)->all()) . "\n");
        }
    }
}
