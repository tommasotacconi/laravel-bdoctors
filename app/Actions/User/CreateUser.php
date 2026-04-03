<?php

namespace App\Actions\User;

use App\Models\User;

class CreateUser
{
    public function handle(array $data, bool $handleHomId = true)
    {
        $user = User::create($data);
        if ($handleHomId) $user->setHomId()->save();
        if (isset($data['specializations'])) $user->specializations()->sync($data['specializations']);

        return $user;
    }
}
