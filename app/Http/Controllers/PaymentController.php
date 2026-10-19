<?php

namespace App\Http\Controllers;

use App\Enums\AlertLevelEnum;
use App\Http\Requests\PaymentRequest;
use App\Models\Payment;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Stripe\Charge;
use Stripe\Exception\CardException;
use Stripe\StripeClient;
use Stripe\Token;

class PaymentController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @param  StripeClient  $stripe  Bound in AppServiceProvider
     * @return void
     */
    public function __construct(private StripeClient $stripe) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('payment.index', [
            'payments' => Payment::where('user_id', Auth::id())->paginate(30),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('payment.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PaymentRequest $request): RedirectResponse
    {
        $token = $this->createToken($request);
        if (! empty($token['error'])) {
            session()->flash('alert-'.AlertLevelEnum::ERROR->name, $token['error']);

            return back();
        }
        if (empty($token['id'])) {
            session()->flash('alert-'.AlertLevelEnum::ERROR->name, 'Échec du paiement.');

            return back();
        }

        $charge = $this->createCharge($token['id'], $request['amount'] * 100, $request['message']);
        // createCharge() returns an array containing the error message when Stripe throws
        if (is_array($charge) || $charge->status !== 'succeeded') {
            session()->flash('alert-'.AlertLevelEnum::ERROR->name, 'Échec du paiement.');

            return back();
        } else {
            $payment = new Payment;
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
     * @return Token|array{error: string}
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
                ],
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
     * @return Charge|array{error: string}
     */
    private function createCharge($tokenId, $amount, $message)
    {
        $charge = null;
        $message = empty($message) ? 'Virement de '.auth()->user()->name.' à '.config('app.name') : $message;
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
