<?php

namespace App\Actions;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Create a response based on the passed process of resource management
 */
class CreateResourceResponse
{
    public function handle(string $resourceName, string $processName, string $processAsVerbPastParticiple, array|string|callable $process)
    {
        try {
            $resultingResource = app()->call($process);
            Log::info("$resourceName $processAsVerbPastParticiple successfully", ['review_id' => $resultingResource->id]);

            return response()->json([
                'message' => "$resourceName $processAsVerbPastParticiple successfully",
                $resourceName => $resultingResource
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $msg = "$resourceName $processName's validation failed";
            Log::error($msg, ['errors' => $e->errors()]);

            return response()->json([
                'message' => $msg,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            $msg = "$resourceName $processName failed";
            Log::error($msg, ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return response()->json([
                'message' => $msg,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
