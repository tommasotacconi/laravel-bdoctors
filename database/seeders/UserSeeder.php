<?php

namespace Database\Seeders;

use App\Actions\Profile\CreateProfile;
use App\Actions\SetFileField;
use App\Actions\User\CreateUser;
use App\Helpers\TimeHelper;
use App\Models\Profile;
use Illuminate\Database\Seeder;
use App\Models\User;
use Database\Factories\Generators\FakeProfileGenerator;
use Faker\Generator as Faker;
use GuzzleHttp\Promise;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

//use Faker\Provider\it_IT as Faker;

class UserSeeder extends Seeder
{
    public function __construct(protected Faker $faker)
    {
    }

    /**
     * Run the database seeds.
     */
    public function run(
        FakeProfileGenerator $fakeProfGenerator,
        CreateUser $userCreator,
        CreateProfile $profileCreator
    ): void {
        $usersNum = (int) $this->command->ask('How many users?', 250);
        if ($usersNum <= 0) {
            $this->command->error('Count must be greater than 0');
            return;
        }
        $profilesData = [];
        $promises = [];

        for ($i = 0; $i < $usersNum; $i++) {
            // Create two groups of hanonyms of test
            if ($i === 10 - 1 || $i === 50 - 1 || $i === 250 - 1) {
                $gender = 'male';
                $userData = $this->makeTestUser($this->faker, $i);
            } elseif ($i === 200 - 1 || $i === 249 - 1) {
                $gender = 'female';
                $userData = $this->makeTestUser($this->faker, $i, 'Serena', 'Pesano');
            } else {
                $gender = $this->faker->randomElement(['male', 'female']);
                $userData = [
                    'first_name' => $fName = $this->faker->firstName($gender),
                    'last_name' => $lName = $this->faker->lastName($gender),
                    'email' => $this->getRandEmail($fName, $lName),
                    'password' => Hash::make($this->faker->password(6, 20)),
                    'created_at' => TimeHelper::computeAppTime()->startOfYear()->addDays(365 * rand(1, 90) / 100)
                ];
            }
            $userData['home_address'] = $this->faker->streetAddress();
            $user = $userCreator->handle($userData, false);
            $withPhotoUsers = array_merge([10, 50, 250, 200], range(11, 60), range(150, 249));
            if (in_array($user?->id, $withPhotoUsers)) $hasPhoto = true;
            else $hasPhoto = false;
            [$profileData, $promises[$user->id]] = $fakeProfGenerator->make($gender, $hasPhoto, $user);
            $profilesData[$user->id] = [$user, $profileData];
        }

        $responses = Promise\Utils::settle($promises)->wait();

        foreach ($profilesData as $userId => [$user, $profileData]) {
            $photoPath = $responses[$userId]['value'] ?? null;
            if ($photoPath) $profileData['photo'] = $photoPath;
            $createdProf[] = $profileCreator->handle($user, $profileData);
        }

        $this->setHomonymsId();
        \Log::info(
            'created profiles ' . count($createdProf) .
            ', of which ' . array_reduce($responses, fn ($acc, $item) => $item['value'] ?? null ? ++$acc : $acc, 0) . ' with photo'
        );
    }

    protected function getRandEmail($fName, $lName)
    {
        $eventualUsernameNum = $this->faker->boolean(20) ? $this->faker->randomNumber(2, true) : null;
        $usernameComp = ["$fName{$this->faker->randomElement(['.', '-', '_', ''])}$lName", $eventualUsernameNum];

        return  str_replace(' ', '', strtolower(implode($usernameComp)) . "@{$this->faker->freeEmailDomain()}");
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

    protected function setHomonymsId()
    {
        $homonymsName = DB::table('users')
            ->selectRaw('CONCAT(first_name, " ", last_name) as full_name, COUNT(*) as cnt')
            ->groupBy('full_name')
            ->having('cnt', '>', 1)
            ->pluck('full_name')
            ->all();

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
}
