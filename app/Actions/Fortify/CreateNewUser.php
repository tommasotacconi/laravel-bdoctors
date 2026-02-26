<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\CreatesNewUsers;
use App\Models\User;
use App\Validation\BaseValidation;
use App\Actions\User\CreateUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function __construct(protected CreateUser $creator) {}


    /**
     * Create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $data): User
    {
        $validated = Validator::make($data, BaseValidation::userToCreate())->validate();
        $validated['password'] = Hash::make($validated['password']);
        // $token = $user->createToken('auth-token')->plainTextToken;

        return $this->creator->handle($validated);
    }
}
