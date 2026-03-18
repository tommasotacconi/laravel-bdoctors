<?php

namespace App\Http\Controllers\Api;

use App\Helpers\TimeHelper;
use Braintree\Gateway;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RespondsWithApi;
use App\Models\Profile;
use App\Models\Sponsorship;
use Illuminate\Support\Facades\Auth;

class BraintreeApiController extends Controller
{
    use RespondsWithApi;

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

        return $this->apiResponse($clientToken, 'token');
    }

    public function processPayment(Request $req)
    {
        try {
            $result = $this->gateway->transaction()->sale([
                'amount' => $req->amount,
                'paymentMethodNonce' => $req->payment_method_nonce,
                'options' => ['submitForSettlement' => true]
            ]);

            if ($result->success) {
                // Activate sponsorship on authenticated user
                $sponsorship = Sponsorship::where('name', $req->selectedSpon)->first();
                Profile::where('user_id', Auth::id())->first()->sponsorships()->attach(
                    $sponsorship->id,
                    [
                        'start_date' => $computedTime = TimeHelper::computeAppTime(false),
                        'end_date' => $computedTime->addHours($sponsorship->duration),
                        'created_at' => $computedTime
                    ]
                );

                return $this->apiResponse(
                    [
                        'id' => $result->transaction->id,
                        'amount' => $result->transaction->amount,
                        'status' => $result->transaction->status
                    ],
                    'transaction'
                );
            } else
                return $this->apiResponse($result->errors->deepAll(), 'errors', 'Transaction failed', 422);
        } catch (\Exception $e) {
            return $this->apiResponse(
                [
                    'trace' => $e->getTrace()
                ],
                'error',
                $e->getMessage(),
                500
            );
        }
    }
}
