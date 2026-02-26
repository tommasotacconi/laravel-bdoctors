<?php

namespace App\Http\Controllers\Api;

use App\Actions\User\CreateUser;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Validation\BaseValidation;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(protected Request $req, protected BaseValidation $bV) {}

    public function store(CreateUser $creator): User
    {
        $rules = $this->bV->userToCreate();
        $validated = $this->req->validate($rules);

        return $this->apiResponse(
            $creator->handle($validated),
            'user',
            'user created'
        );
    }
}
