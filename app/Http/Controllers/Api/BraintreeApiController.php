<?php

namespace App\Http\Controllers\Api;

use App\Helpers\TimeHelper;
use Braintree\Gateway;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\Sponsorship;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;

class BraintreeApiController extends Controller
{
    private $gateway;

    public function __construct()
    {
        $this->gateway = new Gateway([
            'environment' => config('braintree.environment'),
            'merchantId' => config('braintree.merchantId'),
            'publicKey' => config('braintree.publicKey'),
            'privateKey' => config('braintree.privateKey'),
        ]);
    }

    public function generateToken()
    {
        $clientToken = $this->gateway->clientToken()->generate();
        return response()->json(['token' => $clientToken]);
    }

    public function processPayment(Request $request)
    {
        try {
            $result = $this->gateway->transaction()->sale([
                'amount' => $request->amount,
                'paymentMethodNonce' => $request->payment_method_nonce,
                'options' => [
                    'submitForSettlement' => true
                ]
            ]);

            if ($result->success) {
                // Activate sponsorship on authorized user
                $authenticatedUserId = Auth::id();
                $sponsorshipName = $request->sponsorshipName;
                $sponsorship = Sponsorship::where('name', $sponsorshipName)->first();
                Profile::where('user_id', $authenticatedUserId)->first()
                    ->sponsorships()->attach($sponsorship->id, [
                        'start_date' => $computedTime = TimeHelper::computeAppTime(false),
                        'end_date' => $computedTime->addHours($sponsorship->duration),
                        'created_at' => $computedTime
                    ]);

                return response()->json([
                    'success' => true,
                    'transaction' => [
                        'id' => $result->transaction->id,
                        'amount' => $result->transaction->amount,
                        'status' => $result->transaction->status
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction failed',
                    'errors' => $result->errors->deepAll()
                ], 422);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
