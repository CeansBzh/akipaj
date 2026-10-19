<?php

namespace Tests\Feature;

use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use Stripe\Charge;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\CardException;
use Stripe\Service\ChargeService;
use Stripe\Service\TokenService;
use Stripe\StripeClient;
use Stripe\Token;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    /** @var TokenService&MockInterface */
    protected TokenService $tokens;

    /** @var ChargeService&MockInterface */
    protected ChargeService $charges;

    protected function setUp(): void
    {
        parent::setUp();

        // Never call the Stripe API: the controller receives a client whose services are mocks
        $this->tokens = Mockery::mock(TokenService::class);
        $this->charges = Mockery::mock(ChargeService::class);
        $stripe = new class($this->tokens, $this->charges) extends StripeClient
        {
            public function __construct(public TokenService $tokens, public ChargeService $charges)
            {
                parent::__construct(['api_key' => 'sk_test_dummy']);
            }
        };
        $this->app->instance(StripeClient::class, $stripe);
    }

    /**
     * @return array<string, string>
     */
    protected function validData(): array
    {
        return [
            'amount' => '25,50',
            'message' => 'Cotisation',
            'card_number' => '4242 4242 4242 4242',
            'card_expiry' => '12 / '.now()->addYear()->format('y'),
            'card_cvc' => '123',
            'card_holder_name' => 'Michel Dupont',
        ];
    }

    public function test_index_lists_only_the_payments_of_the_user(): void
    {
        $user = $this->actingAsMember();
        $mine = Payment::factory()->create(['user_id' => $user->id]);
        $other = Payment::factory()->create(['user_id' => $this->createUser()->id]);

        $payments = $this->get(route('payments.index'))->assertOk()->viewData('payments');

        $this->assertTrue($payments->contains($mine));
        $this->assertFalse($payments->contains($other));
    }

    public function test_payment_form_is_displayed(): void
    {
        $this->actingAsMember();

        $this->get(route('payments.create'))->assertOk()->assertViewIs('payment.create');
    }

    public function test_successful_payment_is_recorded(): void
    {
        $user = $this->actingAsMember();
        $this->tokens->shouldReceive('create')
            ->once()
            ->with(Mockery::on(fn (array $params) => $params['card']['number'] == '4242424242424242'
                && $params['card']['exp_month'] === '12'
                && $params['card']['name'] === 'Michel Dupont'))
            ->andReturn(Token::constructFrom(['id' => 'tok_123']));
        $this->charges->shouldReceive('create')
            ->once()
            ->with(Mockery::on(fn (array $params) => $params['amount'] == 2550
                && $params['currency'] === 'eur'
                && $params['source'] === 'tok_123'
                && $params['description'] === 'Cotisation'))
            ->andReturn(Charge::constructFrom(['id' => 'ch_123', 'status' => 'succeeded']));

        $this->from(route('payments.create'))
            ->post(route('payments.store'), $this->validData())
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', true)
            ->assertRedirect(route('payments.create'));

        $this->assertDatabaseHas('payments', ['user_id' => $user->id, 'amount' => 25.5, 'description' => 'Cotisation']);
    }

    public function test_default_description_is_used_when_no_message_is_given(): void
    {
        $user = $this->actingAsMember();
        $this->tokens->shouldReceive('create')->andReturn(Token::constructFrom(['id' => 'tok_123']));
        $this->charges->shouldReceive('create')
            ->once()
            ->with(Mockery::on(fn (array $params) => $params['description'] === 'Virement de '.$user->name.' à '.config('app.name')))
            ->andReturn(Charge::constructFrom(['id' => 'ch_123', 'status' => 'succeeded']));

        $this->post(route('payments.store'), array_merge($this->validData(), ['message' => '']))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('payments', 1);
    }

    public function test_declined_card_is_reported_and_not_recorded(): void
    {
        $this->actingAsMember();
        $this->tokens->shouldReceive('create')->andThrow(
            CardException::factory('Votre carte a été refusée.', 402, null, ['error' => ['message' => 'Votre carte a été refusée.']])
        );
        $this->charges->shouldNotReceive('create');

        $this->post(route('payments.store'), $this->validData())->assertSessionHas('alert-ERROR', 'Votre carte a été refusée.');

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_unexpected_errors_during_tokenization_are_reported(): void
    {
        $this->actingAsMember();
        $this->tokens->shouldReceive('create')->andThrow(new ApiConnectionException('Network error'));
        $this->charges->shouldNotReceive('create');

        $this->post(route('payments.store'), $this->validData())->assertSessionHas('alert-ERROR', 'Network error');

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_failed_charge_is_not_recorded(): void
    {
        $this->actingAsMember();
        $this->tokens->shouldReceive('create')->andReturn(Token::constructFrom(['id' => 'tok_123']));
        $this->charges->shouldReceive('create')->andReturn(Charge::constructFrom(['id' => 'ch_123', 'status' => 'failed']));

        $this->post(route('payments.store'), $this->validData())->assertSessionHas('alert-ERROR', 'Échec du paiement.');

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_stripe_errors_during_the_charge_are_not_recorded_as_payments(): void
    {
        $this->actingAsMember();
        $this->tokens->shouldReceive('create')->andReturn(Token::constructFrom(['id' => 'tok_123']));
        $this->charges->shouldReceive('create')->andThrow(new ApiConnectionException('Network error'));

        $this->post(route('payments.store'), $this->validData())->assertSessionHas('alert-ERROR', 'Échec du paiement.');

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_payment_data_is_validated(): void
    {
        $this->actingAsMember();
        $this->tokens->shouldNotReceive('create');

        $this->post(route('payments.store'), [
            'amount' => '1000',
            'card_number' => '1234',
            'card_expiry' => '01/20',
            'card_cvc' => '1',
        ])->assertSessionHasErrors(['amount', 'card_number', 'card_expiry_date', 'card_cvc', 'card_holder_name']);
    }
}
