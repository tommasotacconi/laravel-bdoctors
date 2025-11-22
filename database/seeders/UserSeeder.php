<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Specialization;
use Carbon\Carbon;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
            if ($i === 10 - 1 || $i === 50 - 1 || $i === 250 - 1) {
                $this->makeTestUser($newUser, $faker, $i);
            } else if ($i === 200 - 1 || $i === 249 - 1) {
                $this->makeTestUser($newUser, $faker, $i, 'Serena', 'Pesano');
            } else {
                $fName = $faker->firstName();
                $lName = $faker->lastName();
                $newUser->first_name = $fName;
                $newUser->last_name = $lName;
                // ---Email generation
                $usernameNumber = $faker->randomNumber(2, true);
                $eventualUsNum = $faker->boolean(20) ? $usernameNumber : null;
                $usernameComponents = $faker->shuffle(["$fName{$faker->randomElement(['.', '-', '_', ''])}$lName", $eventualUsNum]);
                $newUser->email = str_replace(' ', '', strtolower(implode($usernameComponents)) . "@$faker->freeEmailDomain"); // ---
                $newUser->password = Hash::make($faker->password(6, 20));
                $newUser->created_at = Carbon::create(2024, 1, 1, 0);
            }
            //$newUser->specialization_id = $faker->randomElement($specializationIds);

            $newUser->home_address = $faker->streetAddress();
            $newUser->save();
        }

        /* Check homonyms and assing them `homonymous_id` */
        $homonyms = DB::select("
            SELECT `homonymousName2` full_name, `id2` id
            FROM (
                SELECT
                    CONCAT(A.`first_name`, A.`last_name`) AS homonymousName1, A.`id` AS id1,
                    CONCAT(B.`first_name`, B.`last_name`) AS homonymousName2, B.`id` AS id2
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

            foreach ($homonymsGroups as $group) {
                foreach ($group as $index =>  $homonymous) {
                    $userWithHomonyms = User::findOrFail($homonymous->id);
                    $userWithHomonyms->update(['homonymous_id' => $index + 1]);
                }
            }
        }
    }

    public function makeTestUser($userInstance, $fakerInstance, $id, $fName = 'Adriano', $lName = 'Carola')
    {
        $fName = $userInstance->first_name = $fName;
        $lName = $userInstance->last_name = $lName;
        $email = $userInstance->email = $fName . ++$id . $lName . '@testmail.com';
        $pwd = $fakerInstance->password(6, 20);
        $userInstance->password = Hash::make($pwd);
        $userInstance->created_at = Carbon::create(2024, 1, 1, 0);
        file_put_contents('test-users.txt', print_r("  Test user email and password: $email, $pwd\n", true), FILE_APPEND);
    }
}
