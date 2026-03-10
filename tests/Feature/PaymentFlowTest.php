<?php

use App\Events\OrderCreated;
use App\Models\Cart;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\User;
use App\Services\PaymentService;
use App\Services\StockService;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    // We don't want to actually send emails or deal with real stock movements
    Event::fake([OrderCreated::class]);

    // Create a mock user
    $this->user = User::factory()->create();

    // Create a menu item
    $this->menuItem = MenuItem::factory()->create([
        'price' => 10.00,
        'is_available' => true,
    ]);

    // Create a cart item for the user
    Cart::factory()->create([
        'user_id' => $this->user->id,
        'menu_item_id' => $this->menuItem->id,
        'quantity' => 2,
    ]);
});

it('can checkout with cash on delivery (COD)', function () {
    // Mock StockService to bypass actual stock deductions for simplicity if needed
    // Assuming StockService::checkStockAvailability returns empty array (available)

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

    // Cart should be empty
    expect(Cart::where('user_id', $this->user->id)->count())->toBe(0);

    Event::assertDispatched(OrderCreated::class);
});

it('can initiate stripe checkout and redirect', function () {
    // Mock the PaymentService
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

    $response = $this->actingAs($this->user)->get(route('checkout.verify', [
        'session_id' => 'cs_test_123',
        'order_id' => $order->id,
    ]));

    $order->refresh();

    expect($order->payment_status)->toEqual('paid')
        ->and($order->payment_reference)->toEqual('pi_test_123')
        ->and($order->status)->toEqual('confirmed');

    // Cart should be empty
    expect(Cart::where('user_id', $this->user->id)->count())->toBe(0);

    $response->assertRedirect(route('orders.show', $order->id));
    $response->assertSessionHas('success');
});
