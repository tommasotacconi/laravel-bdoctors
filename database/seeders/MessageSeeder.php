<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\Message;
use App\Models\Profile;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Generator as Faker;


class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(Faker $faker): void
    {
        $profileIds = Profile::all()->pluck("id");

        foreach ($profileIds as $profileId) {
            for ($i = 0; $i < ($faker->numberBetween(20, 36)); $i++) {
                $newMessage = new Message();
                $newMessage->profile_id = $profileId;
                $newMessage->content = $faker->realText(rand(50, 200));
                $newMessage->email = $faker->email();
                $newMessage->first_name = $faker->firstName();
                $newMessage->last_name = $faker->lastName();
                // Generate random date, excluding non existing hour due to DST
                $DST_from = Carbon::create(2024, 3, 31, 2, 0, 0);
                $DST_to = Carbon::create(2024, 3, 31, 3, 0, 0);
                do {
                    $startDate = Carbon::create(2024, 1, 1, 0, 0, 0);
                    $endDate = Carbon::create(2024, 12, 31, 23, 59, 59);
                    $randomDate = $faker->dateTimeBetween($startDate, $endDate);
                } while (Carbon::parse($randomDate)->isBetween($DST_from, $DST_to));
                $newMessage->created_at = $randomDate;
                $newMessage->updated_at = $randomDate;

                $newMessage->save();
            }
        }

        // foreach($profileIds as $profileId) {
        //     for($i = 0; $i < ($faker->numberBetween(1,3)); $i++) {
        //         $newMessage = new Message();
        //         $newMessage->profile_id = $profileId;
        //         $newMessage->content = $faker->realText(rand(50,200));
        //         $newMessage->email = $faker->email();
        //         $newMessage->first_name = $faker->firstName();
        //         $newMessage->last_name = $faker->lastName();
        //         $newMessage->save();
        //     }
        // }
    }
}
