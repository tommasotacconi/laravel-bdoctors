<?php

namespace App\Actions\Profile;

use App\Actions\SetFileField;
use App\Models\Profile;
use App\Models\User;

class CreateProfile
{
    public function __construct(protected SetFileField $sFF)
    {
    }

    public function handle(User $user, array $data): Profile
    {
        $fileDirs = ['photo' => 'photos', 'curriculum' => 'curricula'];
        foreach ($fileDirs as $field => $dir) {
            $data[$field] = $this->sFF->handle($data[$field] ?? null, $dir);
        }
        $profile = $user->profile()->create($data);
        $profile->user->makeVisible('home_address');

        return $profile;
    }
}
