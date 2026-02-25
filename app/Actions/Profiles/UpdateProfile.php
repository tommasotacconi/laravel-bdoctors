<?php

namespace App\Actions\Profiles;

use App\Actions\SetFileField;
use App\Actions\User\UpdateUser;
use App\Models\Profile;
use App\Models\User;

class UpdateProfile
{
    public function __construct(protected UpdateUser $updater, protected SetFileField $sFF) {}

    public function handle(User $user, array $data, array $userData): Profile
    {
        $this->updater->handle($user, $userData);
        $profile = $user->loadMissing('profile')->profile;
        $filesDir = ['photo' => 'photos', 'curriculum' => 'curricula'];
        foreach ($filesDir as $file => $dir) {
            $result = $this->sFF->handle($data[$file] ?? null, $dir);
            if ($result) $data[$file] = $result;
        }
        $profile->update($data);

        return $profile->setRelation('user', $user->makeHidden('profile'));
    }
}
