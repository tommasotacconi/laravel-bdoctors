<?php

namespace App\Actions\Fortify;

use App\Helpers\ValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Symfony\Component\HttpFoundation\Request;

use function App\Helpers\makeResponseWithCreated;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $validated = Validator::make($input, ValidationRules::user())->validate();
        $userData = [
            ...$validated,
            'password' => Hash::make($validated['password']),
        ];
        $this->setHomId($userData);

        $user = User::create($userData);
        // $token = $user->createToken('auth-token')->plainTextToken;

        $specIds = array_map(fn ($el) => $el['id'], $validated['specializations_id']);
        $user->specializations()->attach($specIds);
        $user->load('specializations');
        Log::info('User registered successfully', ['user_id' => $user->id]);

        return $user;
    }

    /**
     * Set homonymous_id on passed user and last found homonym
     *
     * @param array $data
     * @return User
     * @throws \Exception
     */
    private function setHomId($userData)
    {
        // Verify presence of homonyms and assign homonymous_id
        $last_homonymous = User::where([
            ['first_name', $userData['first_name']],
            ['last_name', $userData['last_name']]
        ])->orderByDesc('homonymous_id')->first();
        if ($last_homonymous !== null) {
            // Update even last homonymous if it had not homonyms yet
            if ($last_homonymous->homonymous_id === null) $last_homonymous->update(['homonymous_id' => 1]);
            $homonymous_id = $last_homonymous->homonymous_id + 1;
            $userData['homonymous_id'] = $homonymous_id;
        }

    }
}
