<?php

namespace App\Http\Controllers;

use Exception;
use Stripe\StripeClient;
use App\Enum\AlertLevelEnum;
use Stripe\Exception\CardException;
use App\Http\Requests\PaymentRequest;

class StripeController extends Controller
{

    private $stripe;
    public function __construct()
    {
        $this->stripe = new StripeClient(config('stripe.secret_key'));
    }

    /**
     * payment view
     */
    public function create()
    {
        return view('payment.create');
    }

    /**
     * handling payment with POST
     */
    public function store(PaymentRequest $request)
    {
        $token = $this->createToken($request);
        if (!empty($token['error'])) {
            session()->flash('alert-' . AlertLevelEnum::ERROR->name, $token['error']);
            return back();
        }
        if (empty($token['id'])) {
            session()->flash('alert-' . AlertLevelEnum::ERROR->name, 'Échec du paiement.');
            return back();
        }

        $charge = $this->createCharge($token['id'], $request['amount'] * 100, $request['message']);
        if (empty($charge) || $charge['status'] != 'succeeded') {
            session()->flash('alert-' . AlertLevelEnum::ERROR->name, 'Échec du paiement.');
            return back();
        }

        return back()->with('success', true);
    }

    private function createToken($cardData)
    {
        $token = null;
        try {
            $token = $this->stripe->tokens->create([
                'card' => [
                    'number' => $cardData['card_number'],
                    'exp_month' => $cardData['card_expiry_month'],
                    'exp_year' => $cardData['card_expiry_year'],
                    'cvc' => $cardData['card_cvc'],
                    'name' => $cardData['card_holder_name'],
                ]
            ]);
        } catch (CardException $e) {
            $token['error'] = $e->getError()->message;
        } catch (Exception $e) {
            $token['error'] = $e->getMessage();
        }
        return $token;
    }

    private function createCharge($tokenId, $amount, $message)
    {
        $charge = null;
        $message = empty($message) ? 'Virement de ' . auth()->user()->name . ' à ' . config('app.name') : $message;
        try {
            $charge = $this->stripe->charges->create([
                'amount' => $amount,
                'currency' => 'eur',
                'source' => $tokenId,
                'description' => $message,
            ]);
        } catch (Exception $e) {
            $charge['error'] = $e->getMessage();
        }
        return $charge;
    }
}
