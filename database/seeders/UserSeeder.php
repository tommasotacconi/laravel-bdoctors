<?php

namespace Database\Seeders;

use App\Actions\Profile\CreateProfile;
use App\Actions\User\CreateUser;
use App\Helpers\TimeHelper;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Specialization;
use Carbon\Carbon;
use Database\Factories\Generators\FakeProfileGenerator;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

//use Faker\Provider\it_IT as Faker;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(Faker $faker, FakeProfileGenerator $fakeProfGenerator, CreateUser $userCreator, CreateProfile $profileCreator): void
    {
        for ($i = 0; $i < 300; $i++) {
            // Create two groups of hanonyms of test
            if ($i === 10 - 1 || $i === 50 - 1 || $i === 250 - 1) {
                $gender = 'male';
                $userData = $this->makeTestUser($faker, $i);
            } elseif ($i === 200 - 1 || $i === 249 - 1) {
                $gender = 'female';
                $userData = $this->makeTestUser($faker, $i, 'Serena', 'Pesano');
            } else {
                $gender = $faker->randomElement(['male', 'female']);
                $fName = $userData['first_name'] = $faker->firstName($gender);
                $lName = $userData['last_name'] = $faker->lastName($gender);
                // ---Email generation
                $eventualUsernameNum = $faker->boolean(20) ? $faker->randomNumber(2, true) : null;
                $usernameComp = ["$fName{$faker->randomElement(['.', '-', '_', ''])}$lName", $eventualUsernameNum];
                $userData['email'] = str_replace(' ', '', strtolower(implode($usernameComp)) . "@{$faker->freeEmailDomain()}"); // ---
                $userData['password'] = Hash::make($faker->password(6, 20));
                $userData['created_at'] = TimeHelper::computeAppTime()->startOfYear()->addDays(365 * rand(1, 90) / 100);
            }
            $userData['home_address'] = $faker->streetAddress();
            $user = $userCreator->handle($userData, false);
            $profileCreator->handle($user, $fakeProfGenerator->make($gender, $user));
        }

        $homonymsName = $this->retrieveHoms();
        if (!empty($homonymsName)) {
            $homonyms = User::query()
                ->whereIn(
                    DB::raw('CONCAT(`first_name`, \' \', `last_name`)'),
                    $homonymsName
                )->orderBy('first_name')
                ->orderBy('last_name')
                ->orderBy('id')
                ->get();
            $homonymsGroups = array_fill_keys($homonymsName, 1);
            $homonyms->each(function ($hom) use (&$homonymsGroups) {
                $group = "$hom->first_name $hom->last_name";
                $hom->update(['homonymous_id' => $homonymsGroups[$group]++]);
            });
        }
    }

    protected function makeTestUser($fakerInstance, $ind, $fName = 'Adriano', $lName = 'Carola')
    {
        $pwd = $fakerInstance->password(8, 20);
        $userData = [
            'first_name' => $fName,
            'last_name' => $lName,
            'email' => $fName . ++$ind . $lName . '@testmail.com',
            'password' => Hash::make($pwd),
            'created_at' => TimeHelper::computeAppTime()->startOfYear()
        ];
        file_put_contents(
            'test-users.txt',
            print_r("  Test user email and password: $userData[email], $pwd\n", true),
            FILE_APPEND
        );

        return $userData;
    }

    protected function retrieveHoms()
    {
        return DB::table('users')
            ->selectRaw('CONCAT(first_name, " ", last_name) as full_name, COUNT(*) as cnt')
            ->groupBy('full_name')
            ->having('cnt', '>', 1)
            ->pluck('full_name')
            ->all();
    }
}
