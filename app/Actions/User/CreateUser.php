<?php

namespace App\Actions\User;

use App\Models\User;

class CreateUser
{
    public function handle(array $data)
    {
        $user = User::create($data);
        $user->setHomId()->save();
        $user->specializations()->sync($data['specializations']);

        return $user;
    }
}
