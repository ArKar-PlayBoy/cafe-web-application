<?php

use App\Models\Cart;
use App\Models\KitchenTicket;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\User;
use App\Exceptions\PaymentMethodOwnershipException;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    Log::spy();
});

afterEach(function () {
    \Mockery::close();
});

it('does not mark stripe orders as verified when payment is not paid', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create();
    $menuItem = MenuItem::factory()->create([
        'price' => 12.50,
        'is_available' => true,
    ]);

    Cart::factory()->create([
        'user_id' => $user->id,
        'menu_item_id' => $menuItem->id,
        'quantity' => 1,
    ]);

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'payment_method' => 'stripe',
        'payment_status' => 'pending',
        'status' => 'pending',
        'payment_reference' => null,
    ]);

    $mockPaymentService = \Mockery::mock(PaymentService::class);
    $mockPaymentService->shouldReceive('verifyPayment')
        ->once()
        ->with('cs_test_unpaid')
        ->andReturn([
            'status' => 'unpaid',
            'amount_total' => 12.50,
            'payment_intent' => null,
            'metadata_order_id' => (string) $order->id,
            'metadata_user_id' => (string) $user->id,
        ]);

    $this->app->instance(PaymentService::class, $mockPaymentService);

    $response = $this->actingAs($user)->get(route('checkout.verify', [
        'session_id' => 'cs_test_unpaid',
        'order_id' => $order->id,
    ]));

    $response->assertRedirect(route('cart'));
    $response->assertSessionHas('error', 'Payment not completed');

    $order->refresh();

    expect($order->payment_status)->toBe('pending')
        ->and($order->status)->toBe('pending')
        ->and($order->payment_reference)->toBeNull()
        ->and(Cart::where('user_id', $user->id)->count())->toBe(1);
});

it('blocks saved-card charges when the payment method belongs to another customer', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'payment_method' => 'stripe',
        'payment_status' => 'pending',
        'status' => 'pending',
    ]);

    $service = new class extends PaymentService
    {
        public bool $paymentIntentWasCreated = false;

        public function getOrCreateCustomer(User $user): string
        {
            return 'cus_owner_123';
        }

        protected function retrievePaymentMethod(string $paymentMethodId)
        {
            return (object) [
                'customer' => 'cus_attacker_999',
            ];
        }

        protected function createStripePaymentIntent(array $payload)
        {
            $this->paymentIntentWasCreated = true;

            return (object) [
                'status' => 'succeeded',
                'id' => 'pi_should_not_be_created',
                'client_secret' => 'secret_should_not_be_created',
            ];
        }
    };

    $result = $service->createPaymentIntentWithSavedCard($order, 'pm_foreign_card', []);

    expect($result['status'])->toBe('forbidden_payment_method')
        ->and($service->paymentIntentWasCreated)->toBeFalse();
});

it('blocks payment method deletion when method does not belong to expected customer', function () {
    $service = new class extends PaymentService
    {
        public bool $detachWasCalled = false;

        protected function retrievePaymentMethod(string $paymentMethodId)
        {
            return (object) [
                'customer' => 'cus_attacker_999',
            ];
        }

        protected function detachStripePaymentMethod($paymentMethod): void
        {
            $this->detachWasCalled = true;
        }
    };

    expect(fn () => $service->deletePaymentMethod('pm_foreign_card', 'cus_owner_123'))
        ->toThrow(PaymentMethodOwnershipException::class, 'Payment method ownership mismatch.');

    expect($service->detachWasCalled)->toBeFalse();
});

it('allows payment method deletion when method belongs to expected customer', function () {
    $service = new class extends PaymentService
    {
        public bool $detachWasCalled = false;

        protected function retrievePaymentMethod(string $paymentMethodId)
        {
            return (object) [
                'customer' => 'cus_owner_123',
            ];
        }

        protected function detachStripePaymentMethod($paymentMethod): void
        {
            $this->detachWasCalled = true;
        }
    };

    $deleted = $service->deletePaymentMethod('pm_owned_card', 'cus_owner_123');

    expect($deleted)->toBeTrue()
        ->and($service->detachWasCalled)->toBeTrue();
});

it('returns forbidden when deleting a payment method that fails ownership check', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create([
        'stripe_customer_id' => 'cus_owner_123',
    ]);

    $mockPaymentService = \Mockery::mock(PaymentService::class);
    $mockPaymentService->shouldReceive('deletePaymentMethod')
        ->once()
        ->with('pm_foreign_card', 'cus_owner_123')
        ->andThrow(new PaymentMethodOwnershipException('Payment method ownership mismatch.'));

    $this->app->instance(PaymentService::class, $mockPaymentService);

    $response = $this->actingAs($user)->delete(route('payment-methods.destroy'), [
        'payment_method_id' => 'pm_foreign_card',
    ]);

    $response->assertForbidden();
});

it('rejects unsigned webhooks outside local environment', function () {
    config(['stripe.webhook_secret' => null]);

    $service = new PaymentService();

    expect(fn () => $service->handleWebhook('{"type":"checkout.session.completed"}', null))
        ->toThrow(Exception::class, 'Webhook secret not configured.');
});

it('retains cancelled order on saved-card ownership mismatch in store()', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create();
    $menuItem = MenuItem::factory()->create([
        'price' => 10.00,
        'is_available' => true,
    ]);

    Cart::factory()->create([
        'user_id' => $user->id,
        'menu_item_id' => $menuItem->id,
        'quantity' => 1,
    ]);

    $mockPaymentService = \Mockery::mock(PaymentService::class);
    $mockPaymentService->shouldReceive('createPaymentIntentWithSavedCard')
        ->once()
        ->andReturn(['status' => 'forbidden_payment_method']);

    $this->app->instance(PaymentService::class, $mockPaymentService);

    $response = $this->actingAs($user)->post(route('checkout.store'), [
        'payment_method' => 'saved_pm_foreign_card',
    ]);

    $response->assertRedirect(route('cart'));
    $response->assertSessionHas('error');

    $order = Order::where('user_id', $user->id)->latest()->first();

    expect($order)->not->toBeNull()
        ->and($order->status)->toBe('cancelled')
        ->and($order->payment_status)->toBe('failed')
        ->and($order->payment_note)->toBe('Blocked: payment method ownership mismatch');
});

it('retains cancelled order on saved-card ownership mismatch in createPaymentIntent()', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create();
    $menuItem = MenuItem::factory()->create([
        'price' => 15.00,
        'is_available' => true,
    ]);

    Cart::factory()->create([
        'user_id' => $user->id,
        'menu_item_id' => $menuItem->id,
        'quantity' => 1,
    ]);

    $mockPaymentService = \Mockery::mock(PaymentService::class);
    $mockPaymentService->shouldReceive('createPaymentIntentWithSavedCard')
        ->once()
        ->andReturn(['status' => 'forbidden_payment_method']);

    $this->app->instance(PaymentService::class, $mockPaymentService);

    $response = $this->actingAs($user)->postJson(route('checkout.create-payment-intent'), [
        'payment_method_id' => 'pm_foreign_card',
    ]);

    $response->assertStatus(403);
    $response->assertJsonFragment(['error' => 'Payment method is not authorized for this account.']);

    $order = Order::where('user_id', $user->id)->latest()->first();

    expect($order)->not->toBeNull()
        ->and($order->status)->toBe('cancelled')
        ->and($order->payment_status)->toBe('failed')
        ->and($order->payment_note)->toBe('Blocked: payment method ownership mismatch');
});

it('retains cancelled order on saved-card generic failure in store()', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create();
    $menuItem = MenuItem::factory()->create([
        'price' => 20.00,
        'is_available' => true,
    ]);

    Cart::factory()->create([
        'user_id' => $user->id,
        'menu_item_id' => $menuItem->id,
        'quantity' => 1,
    ]);

    $mockPaymentService = \Mockery::mock(PaymentService::class);
    $mockPaymentService->shouldReceive('createPaymentIntentWithSavedCard')
        ->once()
        ->andReturn(['status' => 'canceled']);

    $this->app->instance(PaymentService::class, $mockPaymentService);

    $response = $this->actingAs($user)->post(route('checkout.store'), [
        'payment_method' => 'saved_pm_declined_card',
    ]);

    $response->assertRedirect(route('cart'));
    $response->assertSessionHas('error', 'Payment processing failed. Please try another payment method.');

    $order = Order::where('user_id', $user->id)->latest()->first();

    expect($order)->not->toBeNull()
        ->and($order->status)->toBe('cancelled')
        ->and($order->payment_status)->toBe('failed')
        ->and($order->payment_note)->toBe('Payment failed: canceled');
});

it('retains cancelled order on saved-card generic failure in createPaymentIntent()', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create();
    $menuItem = MenuItem::factory()->create([
        'price' => 14.00,
        'is_available' => true,
    ]);

    Cart::factory()->create([
        'user_id' => $user->id,
        'menu_item_id' => $menuItem->id,
        'quantity' => 1,
    ]);

    $mockPaymentService = \Mockery::mock(PaymentService::class);
    $mockPaymentService->shouldReceive('createPaymentIntentWithSavedCard')
        ->once()
        ->andReturn(['status' => 'requires_capture']);

    $this->app->instance(PaymentService::class, $mockPaymentService);

    $response = $this->actingAs($user)->postJson(route('checkout.create-payment-intent'), [
        'payment_method_id' => 'pm_saved_card',
    ]);

    $response->assertStatus(400);
    $response->assertJsonFragment(['error' => 'Payment failed. Please try again.']);

    $order = Order::where('user_id', $user->id)->latest()->first();

    expect($order)->not->toBeNull()
        ->and($order->status)->toBe('cancelled')
        ->and($order->payment_status)->toBe('failed')
        ->and($order->payment_note)->toBe('Payment failed: requires_capture');
});

it('retains cancelled order when payment intent creation fails for new card flow', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create();
    $menuItem = MenuItem::factory()->create([
        'price' => 18.00,
        'is_available' => true,
    ]);

    Cart::factory()->create([
        'user_id' => $user->id,
        'menu_item_id' => $menuItem->id,
        'quantity' => 1,
    ]);

    $mockPaymentService = \Mockery::mock(PaymentService::class);
    $mockPaymentService->shouldReceive('createPaymentIntent')
        ->once()
        ->andReturn(['error' => 'card_declined']);

    $this->app->instance(PaymentService::class, $mockPaymentService);

    $response = $this->actingAs($user)->postJson(route('checkout.create-payment-intent'), []);

    $response->assertStatus(400);
    $response->assertJsonFragment(['error' => 'Failed to process payment. Please try again.']);

    $order = Order::where('user_id', $user->id)->latest()->first();

    expect($order)->not->toBeNull()
        ->and($order->status)->toBe('cancelled')
        ->and($order->payment_status)->toBe('failed')
        ->and($order->payment_note)->toBe('Payment intent creation failed: card_declined');
});

// --- Payment Replay Protection ---

it('rejects checkout session verification when metadata order_id does not match', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'payment_method' => 'stripe',
        'payment_status' => 'pending',
        'status' => 'pending',
    ]);

    $mockPaymentService = \Mockery::mock(PaymentService::class);
    $mockPaymentService->shouldReceive('verifyPayment')
        ->once()
        ->with('cs_paid_for_other_order')
        ->andReturn([
            'status' => 'paid',
            'amount_total' => 10.00,
            'payment_intent' => 'pi_legit',
            'metadata_order_id' => '999',  // different order
            'metadata_user_id' => (string) $user->id,
        ]);

    $this->app->instance(PaymentService::class, $mockPaymentService);

    $response = $this->actingAs($user)->get(route('checkout.verify', [
        'session_id' => 'cs_paid_for_other_order',
        'order_id' => $order->id,
    ]));

    $response->assertRedirect(route('cart'));
    $response->assertSessionHas('error', 'Payment verification failed.');

    $order->refresh();

    // Order must NOT be marked as verified — replay was blocked
    expect($order->payment_status)->toBe('pending')
        ->and($order->status)->toBe('pending');
});

it('rejects checkout session verification when metadata user_id does not match', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'payment_method' => 'stripe',
        'payment_status' => 'pending',
        'status' => 'pending',
    ]);

    $mockPaymentService = \Mockery::mock(PaymentService::class);
    $mockPaymentService->shouldReceive('verifyPayment')
        ->once()
        ->with('cs_paid_by_other_user')
        ->andReturn([
            'status' => 'paid',
            'amount_total' => 10.00,
            'payment_intent' => 'pi_legit',
            'metadata_order_id' => (string) $order->id,
            'metadata_user_id' => '999',  // different user
        ]);

    $this->app->instance(PaymentService::class, $mockPaymentService);

    $response = $this->actingAs($user)->get(route('checkout.verify', [
        'session_id' => 'cs_paid_by_other_user',
        'order_id' => $order->id,
    ]));

    $response->assertRedirect(route('cart'));
    $response->assertSessionHas('error', 'Payment verification failed.');

    $order->refresh();
    expect($order->payment_status)->toBe('pending');
});

it('rejects checkout session verification when metadata is missing', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'payment_method' => 'stripe',
        'payment_status' => 'pending',
        'status' => 'pending',
    ]);

    $mockPaymentService = \Mockery::mock(PaymentService::class);
    $mockPaymentService->shouldReceive('verifyPayment')
        ->once()
        ->with('cs_no_metadata')
        ->andReturn([
            'status' => 'paid',
            'amount_total' => 10.00,
            'payment_intent' => 'pi_legit',
            'metadata_order_id' => null,
            'metadata_user_id' => null,
        ]);

    $this->app->instance(PaymentService::class, $mockPaymentService);

    $response = $this->actingAs($user)->get(route('checkout.verify', [
        'session_id' => 'cs_no_metadata',
        'order_id' => $order->id,
    ]));

    $response->assertRedirect(route('cart'));
    $response->assertSessionHas('error', 'Payment verification failed.');

    $order->refresh();
    expect($order->payment_status)->toBe('pending');
});

it('allows checkout session verification when metadata matches and status is paid', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'payment_method' => 'stripe',
        'payment_status' => 'pending',
        'status' => 'pending',
    ]);

    $mockPaymentService = \Mockery::mock(PaymentService::class);
    $mockPaymentService->shouldReceive('verifyPayment')
        ->once()
        ->with('cs_paid_matching')
        ->andReturn([
            'status' => 'paid',
            'amount_total' => 10.00,
            'payment_intent' => 'pi_legit',
            'metadata_order_id' => (string) $order->id,
            'metadata_user_id' => (string) $user->id,
        ]);

    $this->app->instance(PaymentService::class, $mockPaymentService);

    $response = $this->actingAs($user)->get(route('checkout.verify', [
        'session_id' => 'cs_paid_matching',
        'order_id' => $order->id,
    ]));

    $response->assertRedirect(route('orders.show', $order->id));

    $order->refresh();
    expect($order->payment_status)->toBe('verified')
        ->and($order->status)->toBe('preparing');
});

it('verifies payment via payment_intent path when 3DS redirect uses payment_intent', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'payment_method' => 'stripe',
        'payment_status' => 'pending',
        'status' => 'pending',
    ]);

    $mockPaymentService = \Mockery::mock(PaymentService::class);

    // verifyPayment (session method) must NOT be called
    $mockPaymentService->shouldNotReceive('verifyPayment');

    // verifyPaymentIntent must be called with the payment_intent ID
    $mockPaymentService->shouldReceive('verifyPaymentIntent')
        ->once()
        ->with('pi_3ds_return')
        ->andReturn([
            'status' => 'succeeded',
            'payment_intent' => 'pi_3ds_return',
            'metadata_order_id' => (string) $order->id,
            'metadata_user_id' => (string) $user->id,
        ]);

    $this->app->instance(PaymentService::class, $mockPaymentService);

    $response = $this->actingAs($user)->get(route('checkout.verify', [
        'payment_intent' => 'pi_3ds_return',
        'order_id' => $order->id,
    ]));

    $response->assertRedirect(route('orders.show', $order->id));

    $order->refresh();
    expect($order->payment_status)->toBe('verified')
        ->and($order->status)->toBe('preparing');
});

it('rejects verifyPayment when both session_id and payment_intent are provided', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'payment_method' => 'stripe',
        'payment_status' => 'pending',
        'status' => 'pending',
    ]);

    // No service method should be called since the controller rejects before dispatch
    $mockPaymentService = \Mockery::mock(PaymentService::class);
    $mockPaymentService->shouldNotReceive('verifyPayment');
    $mockPaymentService->shouldNotReceive('verifyPaymentIntent');
    $this->app->instance(PaymentService::class, $mockPaymentService);

    $response = $this->actingAs($user)->get(route('checkout.verify', [
        'session_id' => 'cs_both',
        'payment_intent' => 'pi_both',
        'order_id' => $order->id,
    ]));

    $response->assertRedirect(route('cart'));
    $response->assertSessionHas('error', 'Invalid payment verification');

    $order->refresh();
    expect($order->payment_status)->toBe('pending');
});

// --- Error Leakage ---

it('does not leak exception details in createPaymentIntent catch block', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create();
    $menuItem = MenuItem::factory()->create([
        'price' => 15.00,
        'is_available' => true,
    ]);

    Cart::factory()->create([
        'user_id' => $user->id,
        'menu_item_id' => $menuItem->id,
        'quantity' => 1,
    ]);

    $mockPaymentService = \Mockery::mock(PaymentService::class);
    $mockPaymentService->shouldReceive('createPaymentIntent')
        ->once()
        ->andThrow(new \Exception('sk_test_secret_exposed: internal server error detail'));

    $this->app->instance(PaymentService::class, $mockPaymentService);

    $response = $this->actingAs($user)->postJson(route('checkout.create-payment-intent'), []);

    $response->assertStatus(500);
    $body = $response->json();

    expect($body['error'])->toBe('Failed to process payment. Please try again.')
        ->and($body['error'])->not->toContain('sk_test');
});

it('does not leak exception details in confirmPayment catch block', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'payment_method' => 'stripe',
        'payment_status' => 'pending',
        'status' => 'pending',
    ]);

    $mockPaymentService = \Mockery::mock(PaymentService::class);
    $mockPaymentService->shouldReceive('verifyPaymentIntent')
        ->once()
        ->andThrow(new \Exception('Stripe API key leaked: internal detail'));

    $this->app->instance(PaymentService::class, $mockPaymentService);

    $response = $this->actingAs($user)->postJson(route('checkout.confirm-payment'), [
        'payment_intent_id' => 'pi_test',
        'order_id' => $order->id,
    ]);

    $response->assertStatus(500);
    $body = $response->json();

    expect($body['error'])->toBe('Failed to confirm payment. Please try again.')
        ->and($body['error'])->not->toContain('Stripe API key');
});

// --- Orphan Prevention ---

it('cancels order on unexpected exception in createPaymentIntent', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create();
    $menuItem = MenuItem::factory()->create([
        'price' => 15.00,
        'is_available' => true,
    ]);

    Cart::factory()->create([
        'user_id' => $user->id,
        'menu_item_id' => $menuItem->id,
        'quantity' => 1,
    ]);

    $mockPaymentService = \Mockery::mock(PaymentService::class);
    $mockPaymentService->shouldReceive('createPaymentIntent')
        ->once()
        ->andThrow(new \Exception('Unexpected Stripe failure'));

    $this->app->instance(PaymentService::class, $mockPaymentService);

    $response = $this->actingAs($user)->postJson(route('checkout.create-payment-intent'), []);

    $response->assertStatus(500);

    $order = Order::where('user_id', $user->id)->latest()->first();

    expect($order)->not->toBeNull()
        ->and($order->status)->toBe('cancelled')
        ->and($order->payment_status)->toBe('failed')
        ->and($order->payment_note)->toBe('Payment processing error');
});

// --- confirmPayment() Replay Protection ---

it('rejects confirmPayment when payment intent metadata order_id does not match', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'payment_method' => 'stripe',
        'payment_status' => 'pending',
        'status' => 'pending',
    ]);

    $mockPaymentService = \Mockery::mock(PaymentService::class);
    $mockPaymentService->shouldReceive('verifyPaymentIntent')
        ->once()
        ->with('pi_wrong_order')
        ->andReturn([
            'status' => 'succeeded',
            'payment_intent' => 'pi_wrong_order',
            'metadata_order_id' => '999',
            'metadata_user_id' => (string) $user->id,
        ]);

    $this->app->instance(PaymentService::class, $mockPaymentService);

    $response = $this->actingAs($user)->postJson(route('checkout.confirm-payment'), [
        'payment_intent_id' => 'pi_wrong_order',
        'order_id' => $order->id,
    ]);

    $response->assertStatus(400);
    $response->assertJsonFragment(['error' => 'Payment verification failed.']);

    $order->refresh();
    expect($order->payment_status)->toBe('pending');
});

it('rejects confirmPayment when payment intent metadata user_id does not match', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'payment_method' => 'stripe',
        'payment_status' => 'pending',
        'status' => 'pending',
    ]);

    $mockPaymentService = \Mockery::mock(PaymentService::class);
    $mockPaymentService->shouldReceive('verifyPaymentIntent')
        ->once()
        ->with('pi_wrong_user')
        ->andReturn([
            'status' => 'succeeded',
            'payment_intent' => 'pi_wrong_user',
            'metadata_order_id' => (string) $order->id,
            'metadata_user_id' => '999',
        ]);

    $this->app->instance(PaymentService::class, $mockPaymentService);

    $response = $this->actingAs($user)->postJson(route('checkout.confirm-payment'), [
        'payment_intent_id' => 'pi_wrong_user',
        'order_id' => $order->id,
    ]);

    $response->assertStatus(400);
    $response->assertJsonFragment(['error' => 'Payment verification failed.']);

    $order->refresh();
    expect($order->payment_status)->toBe('pending');
});

it('rejects confirmPayment when payment intent metadata is missing', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'payment_method' => 'stripe',
        'payment_status' => 'pending',
        'status' => 'pending',
    ]);

    $mockPaymentService = \Mockery::mock(PaymentService::class);
    $mockPaymentService->shouldReceive('verifyPaymentIntent')
        ->once()
        ->with('pi_no_metadata')
        ->andReturn([
            'status' => 'succeeded',
            'payment_intent' => 'pi_no_metadata',
            'metadata_order_id' => null,
            'metadata_user_id' => null,
        ]);

    $this->app->instance(PaymentService::class, $mockPaymentService);

    $response = $this->actingAs($user)->postJson(route('checkout.confirm-payment'), [
        'payment_intent_id' => 'pi_no_metadata',
        'order_id' => $order->id,
    ]);

    $response->assertStatus(400);
    $response->assertJsonFragment(['error' => 'Payment verification failed.']);

    $order->refresh();
    expect($order->payment_status)->toBe('pending');
});

it('allows confirmPayment when payment intent metadata matches and status is succeeded', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'payment_method' => 'stripe',
        'payment_status' => 'pending',
        'status' => 'pending',
    ]);

    $mockPaymentService = \Mockery::mock(PaymentService::class);
    $mockPaymentService->shouldReceive('verifyPaymentIntent')
        ->once()
        ->with('pi_matching')
        ->andReturn([
            'status' => 'succeeded',
            'payment_intent' => 'pi_matching',
            'metadata_order_id' => (string) $order->id,
            'metadata_user_id' => (string) $user->id,
        ]);

    $this->app->instance(PaymentService::class, $mockPaymentService);

    $response = $this->actingAs($user)->postJson(route('checkout.confirm-payment'), [
        'payment_intent_id' => 'pi_matching',
        'order_id' => $order->id,
    ]);

    $response->assertStatus(200);
    $response->assertJsonFragment(['success' => true]);

    $order->refresh();
    expect($order->payment_status)->toBe('verified')
        ->and($order->status)->toBe('preparing');
});

// --- Status Consistency ---

it('webhook checkout.session.completed sets status to preparing for pending order', function () {
    /** @var \Tests\TestCase $this */
    $this->app['config']->set('stripe.webhook_secret', null);
    $this->app->detectEnvironment(fn () => 'local');

    $order = Order::factory()->create([
        'user_id' => User::factory()->create()->id,
        'payment_method' => 'stripe',
        'payment_status' => 'pending',
        'status' => 'pending',
    ]);

    $service = new PaymentService();
    $result = $service->handleWebhook(
        json_encode([
            'type' => 'checkout.session.completed',
            'data' => ['object' => [
                'id' => 'cs_webhook_test',
                'payment_intent' => 'pi_webhook_test',
                'metadata' => ['order_id' => (string) $order->id],
            ]],
        ]),
        null
    );

    $order->refresh();
    expect($result['status'])->toBe('payment_completed')
        ->and($order->payment_status)->toBe('verified')
        ->and($order->status)->toBe('preparing');
});

it('webhook payment_intent.succeeded sets status to preparing for pending order', function () {
    /** @var \Tests\TestCase $this */
    $this->app['config']->set('stripe.webhook_secret', null);
    $this->app->detectEnvironment(fn () => 'local');

    $order = Order::factory()->create([
        'user_id' => User::factory()->create()->id,
        'payment_method' => 'stripe',
        'payment_status' => 'pending',
        'status' => 'pending',
    ]);

    $service = new PaymentService();
    $result = $service->handleWebhook(
        json_encode([
            'type' => 'payment_intent.succeeded',
            'data' => ['object' => [
                'id' => 'pi_webhook_succeeded',
                'metadata' => ['order_id' => (string) $order->id],
            ]],
        ]),
        null
    );

    $order->refresh();
    expect($result['status'])->toBe('success')
        ->and($order->payment_status)->toBe('verified')
        ->and($order->status)->toBe('preparing');
});

it('webhook does not downgrade preparing order to confirmed', function () {
    /** @var \Tests\TestCase $this */
    $this->app['config']->set('stripe.webhook_secret', null);
    $this->app->detectEnvironment(fn () => 'local');

    $order = Order::factory()->create([
        'user_id' => User::factory()->create()->id,
        'payment_method' => 'stripe',
        'payment_status' => 'pending',
        'status' => 'preparing',
    ]);

    $service = new PaymentService();
    $service->handleWebhook(
        json_encode([
            'type' => 'checkout.session.completed',
            'data' => ['object' => [
                'id' => 'cs_late_webhook',
                'payment_intent' => 'pi_late',
                'metadata' => ['order_id' => (string) $order->id],
            ]],
        ]),
        null
    );

    $order->refresh();
    expect($order->status)->toBe('preparing')
        ->and($order->payment_status)->toBe('verified');
});

it('webhook does not change cancelled order status', function () {
    /** @var \Tests\TestCase $this */
    $this->app['config']->set('stripe.webhook_secret', null);
    $this->app->detectEnvironment(fn () => 'local');

    $order = Order::factory()->create([
        'user_id' => User::factory()->create()->id,
        'payment_method' => 'stripe',
        'payment_status' => 'failed',
        'status' => 'cancelled',
    ]);

    $service = new PaymentService();
    $service->handleWebhook(
        json_encode([
            'type' => 'checkout.session.completed',
            'data' => ['object' => [
                'id' => 'cs_cancelled_webhook',
                'payment_intent' => 'pi_cancelled',
                'metadata' => ['order_id' => (string) $order->id],
            ]],
        ]),
        null
    );

    $order->refresh();
    expect($order->status)->toBe('cancelled')
        ->and($order->payment_status)->toBe('failed')
        ->and(KitchenTicket::where('order_id', $order->id)->count())->toBe(0);
});

it('duplicate webhook does not create duplicate kitchen tickets', function () {
    /** @var \Tests\TestCase $this */
    $this->app['config']->set('stripe.webhook_secret', null);
    $this->app->detectEnvironment(fn () => 'local');

    $order = Order::factory()->create([
        'user_id' => User::factory()->create()->id,
        'payment_method' => 'stripe',
        'payment_status' => 'pending',
        'status' => 'pending',
    ]);

    $payload = json_encode([
        'type' => 'checkout.session.completed',
        'data' => ['object' => [
            'id' => 'cs_idempotent',
            'payment_intent' => 'pi_idempotent',
            'metadata' => ['order_id' => (string) $order->id],
        ]],
    ]);

    $service = new PaymentService();
    $service->handleWebhook($payload, null);
    $service->handleWebhook($payload, null);

    $ticketCount = KitchenTicket::where('order_id', $order->id)->count();
    expect($ticketCount)->toBe(1);
});
