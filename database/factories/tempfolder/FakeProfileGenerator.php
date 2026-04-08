<?php

namespace Database\Factories\Generators;

use App\Helpers\TimeHelper;
use App\Models\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class FakeProfileGenerator extends Seeder
{
    /**
     * Run the database seeds.
     */
    public static function make(?string $gender = null, bool $hasPhoto = false, ?User $user = null): array
    {
        $client = new Client();
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

        if ($hasPhoto) {
            $url = "$avatarsBaseUrl?" . http_build_query([
                'seed' => $user->email,
                ...$params['inCommon'],
                ...$params[$gender]
            ]);
            $promise = $client->getAsync($url)->then(fn ($res) => UploadedFile::fake()->createWithContent(
                'profile_photo.svg',
                $res->getBody()->getContents()
            ));
            // \Log::info("started request for user $user->email", [$gender, 'status' => $promises[$user->id]->getState()]);
        }
        $curriculum = UploadedFile::fake()->createWithContent('cv.txt', $faker->realTextBetween(200, 1000));

        $yearEnd = TimeHelper::computeAppTime()->endOfYear();

        return [[
                'user_id' => $user?->id ?? null,
                'curriculum' => $curriculum,
                'office_address' => $faker->streetAddress(),
                'phone' => $faker->phoneNumber(),
                'services' => $faker->realTextBetween(30, 100),
                'created_at' => $user?->created_at->addDays($user->created_at->diffInDays($yearEnd) * rand(0, 5) / 100) ?? null,
            ],
            $promise ?? null
        ];
    }
}
