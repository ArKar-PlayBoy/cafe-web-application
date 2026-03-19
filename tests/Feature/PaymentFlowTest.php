<?php

namespace Tests\Feature;

use App\Events\OrderCreated;
use App\Models\Cart;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Event;
use Mockery;

beforeEach(function () {
    Event::fake([OrderCreated::class]);

    $this->user = User::factory()->create();

    $this->menuItem = MenuItem::factory()->create([
        'price' => 10.00,
        'is_available' => true,
    ]);

    Cart::factory()->create([
        'user_id' => $this->user->id,
        'menu_item_id' => $this->menuItem->id,
        'quantity' => 2,
    ]);
});

afterEach(function () {
    Mockery::close();
});

it('can checkout with cash on delivery (COD)', function () {
    $response = $this->actingAs($this->user)->post(route('checkout.store'), [
        'payment_method' => 'cod',
        'delivery_address' => '123 Main Street, Yangon',
        'delivery_phone' => '09123456789',
    ]);

    $order = Order::where('user_id', $this->user->id)->latest()->first();

    expect($order)->not->toBeNull()
        ->and($order->total)->toEqual(20.00)
        ->and($order->payment_method)->toEqual('cod')
        ->and($order->status)->toEqual('pending')
        ->and($order->delivery_address)->toEqual('123 Main Street, Yangon')
        ->and($order->delivery_phone)->toEqual('09123456789')
        ->and($order->payment_reference)->not->toBeNull();

    $response->assertRedirect(route('orders.show', $order->id));
    $response->assertSessionHas('success');

    expect(Cart::where('user_id', $this->user->id)->count())->toBe(0);

    // COD checkout should NOT dispatch OrderCreated immediately
    // Email will be sent when staff changes status to 'preparing'
    Event::assertNotDispatched(OrderCreated::class);
});

it('can initiate stripe checkout and redirect', function () {
    $mockPaymentService = Mockery::mock(PaymentService::class);
    $mockPaymentService->shouldReceive('processPayment')
        ->once()
        ->andReturn(['url' => 'https://checkout.stripe.com/pay/cs_test_123']);

    $this->app->instance(PaymentService::class, $mockPaymentService);

    $response = $this->actingAs($this->user)->post(route('checkout.store'), [
        'payment_method' => 'stripe',
    ]);

    $order = Order::where('user_id', $this->user->id)->latest()->first();

    expect($order)->not->toBeNull()
        ->and($order->payment_method)->toEqual('stripe');

    $response->assertRedirect('https://checkout.stripe.com/pay/cs_test_123');
});

it('verifies successful stripe payment and clears cart', function () {
    $this->markTestSkipped(
        'This test requires Stripe CLI to be running for webhook processing. '
        .'Run: stripe listen --forward-to localhost:8000/webhook/stripe '
        .'Then update STRIPE_WEBHOOK_SECRET in .env'
    );

    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'payment_method' => 'stripe',
        'payment_status' => 'pending',
        'status' => 'pending',
    ]);

    $mockPaymentService = Mockery::mock(PaymentService::class);
    $mockPaymentService->shouldReceive('verifyPayment')
        ->once()
        ->with('cs_test_123')
        ->andReturn([
            'status' => 'paid',
            'amount_total' => 20.00,
            'payment_intent' => 'pi_test_123',
        ]);

    $this->app->instance(PaymentService::class, $mockPaymentService);

    $response = $this->actingAs($this->user, 'web')->get(route('checkout.verify', [
        'session_id' => 'cs_test_123',
        'order_id' => $order->id,
    ]));

    $order->refresh();

    expect($order->payment_status)->toEqual('paid')
        ->and($order->payment_reference)->toEqual('pi_test_123')
        ->and($order->status)->toEqual('confirmed');

    expect(Cart::where('user_id', $this->user->id)->count())->toBe(0);

    $response->assertRedirect(route('orders.show', $order->id));
    $response->assertSessionHas('success');
});
