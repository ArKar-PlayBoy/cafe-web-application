<?php

use App\Models\Order;

describe('Order canReviewPayment helper method', function () {
    it('returns false for COD orders regardless of screenshot', function () {
        $order = Order::factory()->create([
            'payment_method' => 'cod',
            'payment_status' => Order::PAYMENT_STATUS_PENDING,
            'payment_screenshot' => 'test.jpg',
        ]);

        expect($order->canReviewPayment())->toBeFalse();
    });

    it('returns false when no screenshot exists', function () {
        $order = Order::factory()->create([
            'payment_method' => 'kbz_pay',
            'payment_status' => Order::PAYMENT_STATUS_PENDING,
            'payment_screenshot' => null,
        ]);

        expect($order->canReviewPayment())->toBeFalse();
    });

    it('returns false for failed payment status even with screenshot', function () {
        $order = Order::factory()->create([
            'payment_method' => 'kbz_pay',
            'payment_status' => Order::PAYMENT_STATUS_FAILED,
            'payment_screenshot' => 'test.jpg',
        ]);

        expect($order->canReviewPayment())->toBeFalse();
    });

    it('returns false for verified payment status even with screenshot', function () {
        $order = Order::factory()->create([
            'payment_method' => 'kbz_pay',
            'payment_status' => Order::PAYMENT_STATUS_VERIFIED,
            'payment_screenshot' => 'test.jpg',
        ]);

        expect($order->canReviewPayment())->toBeFalse();
    });

    it('returns false for paid payment status even with screenshot', function () {
        $order = Order::factory()->create([
            'payment_method' => 'kbz_pay',
            'payment_status' => Order::PAYMENT_STATUS_PAID,
            'payment_screenshot' => 'test.jpg',
        ]);

        expect($order->canReviewPayment())->toBeFalse();
    });

    it('returns true for pending status with non-COD method and screenshot', function () {
        $order = Order::factory()->create([
            'payment_method' => 'kbz_pay',
            'payment_status' => Order::PAYMENT_STATUS_PENDING,
            'payment_screenshot' => 'test.jpg',
        ]);

        expect($order->canReviewPayment())->toBeTrue();
    });

    it('returns true for pending status with stripe method and screenshot', function () {
        $order = Order::factory()->create([
            'payment_method' => 'stripe',
            'payment_status' => Order::PAYMENT_STATUS_PENDING,
            'payment_screenshot' => 'test.jpg',
        ]);

        expect($order->canReviewPayment())->toBeTrue();
    });

    it('returns true for awaiting_verification status with screenshot', function () {
        $order = Order::factory()->create([
            'payment_method' => 'kbz_pay',
            'payment_status' => Order::PAYMENT_STATUS_AWAITING_VERIFICATION,
            'payment_screenshot' => 'test.jpg',
        ]);

        expect($order->canReviewPayment())->toBeTrue();
    });
});

describe('Order isPaymentReviewableStatus helper method', function () {
    it('returns true for pending status', function () {
        $order = Order::factory()->create([
            'payment_status' => Order::PAYMENT_STATUS_PENDING,
        ]);

        expect($order->isPaymentReviewableStatus())->toBeTrue();
    });

    it('returns true for awaiting_verification status', function () {
        $order = Order::factory()->create([
            'payment_status' => Order::PAYMENT_STATUS_AWAITING_VERIFICATION,
        ]);

        expect($order->isPaymentReviewableStatus())->toBeTrue();
    });

    it('returns false for verified status', function () {
        $order = Order::factory()->create([
            'payment_status' => Order::PAYMENT_STATUS_VERIFIED,
        ]);

        expect($order->isPaymentReviewableStatus())->toBeFalse();
    });

    it('returns false for paid status', function () {
        $order = Order::factory()->create([
            'payment_status' => Order::PAYMENT_STATUS_PAID,
        ]);

        expect($order->isPaymentReviewableStatus())->toBeFalse();
    });

    it('returns false for failed status', function () {
        $order = Order::factory()->create([
            'payment_status' => Order::PAYMENT_STATUS_FAILED,
        ]);

        expect($order->isPaymentReviewableStatus())->toBeFalse();
    });
});