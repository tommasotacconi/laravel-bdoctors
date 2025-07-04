<?php

namespace Database\Seeders;

use App\Models\Sponsorship;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Generator as Faker;


class SponsorshipSeeder extends Seeder
{

    public function run(Faker $faker): void
    {
        $sponsorships = [
            [
                'name' => 'Bronze',
                'duration' => 24,
                'price' => 2,
                99,
                'description' => 'Sponsorizzazione di base di un giorno'
            ],
            [
                'name' => 'Silver',
                'duration' => 72,
                'price' => 5,
                99,
                'description' => 'Sponsorizzazione intermedia di tre giorni'
            ],
            [
                'name' => 'Gold',
                'duration' => 144,
                'price' => 9,
                99,
                'description' => 'Sponsorizzazione più lunga della duarata di sei giorni'
            ]
        ];

        foreach ($sponsorships as $singlesponsor) {
            $newSponsorship = new Sponsorship();
            $newSponsorship->name = $singlesponsor['name'];
            $newSponsorship->duration = $singlesponsor['duration'];
            $newSponsorship->price = $singlesponsor['price'];
            $newSponsorship->description = $singlesponsor['description'];
            $newSponsorship->save();
        }
    }
}
