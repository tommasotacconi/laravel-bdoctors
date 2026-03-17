<?php

namespace App\Http\Responses;

trait RespondsWithApi
{
    public function apiResponse(mixed $payload = null, string $key = 'data', ?string $msg = null, int $status = 200,)
    {
        $integratedPayload = [];
        if ($msg !== null) {
            if ($key === 'message') throw new \InvalidArgumentException('Cannot accept  a key of "message"');
            $integratedPayload['message'] = $msg;
        }
        if ($payload !== null) $integratedPayload[$key] = $payload;

        return response()->json($integratedPayload, $status);
    }
}
