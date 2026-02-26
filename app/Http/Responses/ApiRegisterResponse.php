<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\RegisterResponse;

class ApiRegisterResponse implements RegisterResponse
{
    use RespondsWithApi;

    public function toResponse($req)
    {
        return $this->apiResponse(
            $req->user()->loadMissing('specializations'),
            'user',
            status: 201
        );
    }
}
