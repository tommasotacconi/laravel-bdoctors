<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Specialization;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\DB;

//use Faker\Provider\it_IT as Faker;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(Faker $faker): void
    {

        // $specializationIds = Specialization::all()->pluck("id");

        for ($i = 0; $i < 250; $i++) {
            $newUser = new User();
            // Create two groups of hanonyms of test
            if ($i === 10 || $i === 50 || $i === 249) {
                $newUser->first_name = 'Adriano';
                $newUser->last_name = 'Carola';
            } else if ($i === 200 || $i === 210) {
                $newUser->first_name = 'Serena';
                $newUser->last_name = 'Pesano';
            } else {
                $newUser->first_name = $faker->firstName();
                $newUser->last_name = $faker->lastName();
            }
            //$newUser->specialization_id = $faker->randomElement($specializationIds);
            $newUser->password = $faker->password(6, 20);
            $newUser->email = $faker->email();
            $newUser->home_address = $faker->streetAddress();
            $newUser->save();
        }

        /* Check homonyms and assing them `homonymous_id` */
        $homonyms = DB::select("
            SELECT `homonymousName1` full_name, `id1` id
            FROM (
                SELECT
                    CONCAT(A.`first_name`, A.`last_name`) AS homonymousName1,
                    A.`id` AS id1,
                    CONCAT(B.`first_name`, B.`last_name`) AS homonymousName2,
                    B.`id` AS id2
                FROM `users` A, `users` B
                WHERE A.`id` <> B.`id`
                AND CONCAT(A.`first_name`, A.`last_name`) = CONCAT(B.`first_name`, B.`last_name`)
            ) AS homonyms
            GROUP BY 2
        ");
        // dd($homonyms);
        // Create list of the homonyms to create auto increment id
        // related to an homonymous name
        if (!empty($homonyms)) {
            $homonymsGroups = [];
            foreach ($homonyms as $homonymous) {
                $fullName = $homonymous->full_name;
                if (!array_key_exists($fullName, $homonymsGroups))
                    $homonymsGroups[$fullName] = [$homonymous];
                else
                    $homonymsGroups[$fullName][] = $homonymous;
            }
            var_dump($homonymsGroups);

            foreach ($homonymsGroups as $group) {
                foreach ($group as $index =>  $homonymous) {
                    $userWithHomonyms = User::findOrFail($homonymous->id);
                    $userWithHomonyms->update(['homonymous_id' => $index]);
                }
            }
        }
    }
}
