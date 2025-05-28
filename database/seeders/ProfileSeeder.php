<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\Http;

class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(Faker $faker): void
    {
        // Retrieve example images
        $client_id = 'B3PAC4WHUXxSshbT-Pi2VrB5NlBLiArGtoNofU4Tk94';
        $response = Http::get("https://api.unsplash.com/collections/4UOO-NbHEt0/photos?client_id=$client_id");
        $imgUrls = [];
        foreach ($response->json() as $img) {
            $imgUrls[] = $img['urls']['small'];
        }

        $userIds = User::all()->pluck("id");

        foreach ($userIds as $userId) {
            $newProfile = new Profile();
            $newProfile->user_id = $userId;
            $newProfile->curriculum = $faker->realTextBetween(200, 1000);
            if (in_array($userId, [10, 50, 200, 250])) {
                $imgUrlsLastIndex = count($imgUrls) - 1;
                $newProfile->photo = $imgUrls[rand(0, $imgUrlsLastIndex)];
            }
            $newProfile->office_address = $faker->streetAddress();
            $newProfile->phone = $faker->phoneNumber();
            $newProfile->services = $faker->realTextBetween(30, 100);
            $newProfile->save();
        }
    }
}
