<?php

namespace App\Actions\User;

use App\Models\User;

class UpdateUser
{
    public function handle(User $user, array $data): User
    {
        // dd($data['specializations']);
        $user->update($data);
        if (isset($data['specializations'])) $user->specializations()->sync($data['specializations']);
        // $token = $user->createToken('auth-token')->plainTextToken;

        return $user->refresh()->makeVisible('home_address');
    }
}
