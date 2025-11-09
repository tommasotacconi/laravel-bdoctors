<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

function makeResponseWithCreated($resourceName, callable $process) {
    try {
        $newResource = $process();
        Log::info("$resourceName created successfully", ['review_id' => $newResource->id]);

        return response()->json([
            'message' => "$resourceName created successfully",
            'profile' => $newResource
        ], 201);
    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::error("$resourceName creation validation failed", ['errors' => $e->errors()]);
        return response()->json([
            'message' => "$resourceName validation failed",
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        $message = "$resourceName creation failed";
        Log::error($message, [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'message' => $message,
            'error' => $e->getMessage()
        ], 500);
    }

}
