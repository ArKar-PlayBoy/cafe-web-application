<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->text('delivery_address')->nullable()->after('payment_verified_by');
            $table->string('delivery_phone', 20)->nullable()->after('delivery_address');
            $table->enum('delivery_status', ['pending', 'out_for_delivery', 'delivered', 'failed'])->default('pending')->after('delivery_phone');
            $table->string('delivery_failed_reason', 500)->nullable()->after('delivery_status');
            $table->timestamp('delivered_at')->nullable()->after('delivery_failed_reason');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_address',
                'delivery_phone',
                'delivery_status',
                'delivery_failed_reason',
                'delivered_at',
            ]);
        });
    }
};
