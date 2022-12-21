<?php

namespace App\Http\Controllers;

use Exception;
use Stripe\StripeClient;
use App\Enum\AlertLevelEnum;
use Stripe\Exception\CardException;
use App\Http\Requests\PaymentRequest;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{

    private $stripe;
    public function __construct()
    {
        $this->stripe = new StripeClient(config('stripe.secret_key'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('payment.index', [
            'payments' => Payment::where('user_id', Auth::id())->paginate(30),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('payment.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\PaymentRequest  $request
     * @return \Illuminate\Http\Response
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
        if (empty($charge) || (isset($charge['status']) && $charge['status'] != 'succeeded')) {
            session()->flash('alert-' . AlertLevelEnum::ERROR->name, 'Échec du paiement.');
            return back();
        } else {
            $payment = new Payment();
            $payment->user_id = Auth::id();
            $payment->amount = $request['amount'];
            $payment->description = $request['message'];
            $payment->save();
        }

        return back()->with('success', true);
    }

    /**
     * Create a Stripe token.
     *
     * @param  \App\Http\Requests\PaymentRequest  $request
     * @return array<string, mixed>
     */
    private function createToken(PaymentRequest $cardData)
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

    /**
     * Create a Stripe charge.
     *
     * @param  string  $tokenId
     * @param  int  $amount
     * @param  string  $message
     * @return array<string, mixed>
     */
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
