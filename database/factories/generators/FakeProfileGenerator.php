<?php

namespace Database\Factories\Generators;

use App\Helpers\TimeHelper;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class FakeProfileGenerator extends Seeder
{
    /**
     * Run the database seeds.
     */
    public static function make(?string $gender = null, ?User $user = null): array
    {
        $faker = fake();
        $gender ??= $faker->randomElement(['male', 'female']);
        $avatarsBaseUrl = 'https://api.dicebear.com/9.x/avataaars/svg';
        $params = [
            'inCommon' => [
                'eyes' => 'default',
                'mouth' => 'default,smile,twinkle,serious'
            ],
            'male' => [
                'top' => 'shortFlat,shortWaved,theCaesar,dreads01,dreads02',
            ],
            'female' => [
                'facialHairProbability' => 0,
                'top' => 'bigHair,bob,bun,curly,curvy'
            ]
        ];

        $photo = null;
        if (in_array($user?->id, array_merge([10, 50, 250, 200], range(11, 30), range(180, 189), range(235, 239)))) {
            $content = Http::get($avatarsBaseUrl, [
                'seed' => $user->email,
                ...$params['inCommon'],
                ...$params[$gender]
            ])->body();
            $photo = UploadedFile::fake()->createWithContent('profile_photo.svg', $content);
        }
        if ($photo) \Log::info('url created for '. $user->first_name . '(id '. $user->id . ')', [$photo]);
        $curriculum = UploadedFile::fake()->createWithContent('cv.txt', $faker->realTextBetween(200, 1000));

        $yearEnd = TimeHelper::computeAppTime()->endOfYear();

        return [
            'user_id' => $user?->id ?? null,
            'curriculum' => $curriculum,
            'photo' => $photo,
            'office_address' => $faker->streetAddress(),
            'phone' => $faker->phoneNumber(),
            'services' => $faker->realTextBetween(30, 100),
            'created_at' => $user?->created_at->addDays($user->created_at->diffInDays($yearEnd) * rand(0, 5) / 100) ?? null
        ];
    }
}
