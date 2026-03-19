<?php

namespace App\Actions\Profile;

use App\Actions\SetFileField;
use App\Models\Profile;
use App\Models\User;

class CreateProfile
{
    public function __construct(protected SetFileField $sFF) {}

    public function handle(User $user, array $data): Profile
    {
        $fileDir = ['photo' => 'photos', 'curriculum' => 'curricula'];
        foreach ($data as $field => $value) {
            if (in_array($field, ['photo', 'curriculum'])) $data[$field] = $this->sFF->handle($data[$field] ?? null, $fileDir[$field]);
        }
        $profile = $user->profile()->create($data);
        $profile->user->makeVisible('home_address');

        return $profile;
    }
}
