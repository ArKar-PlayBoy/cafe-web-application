<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Drop existing enum columns and recreate with correct values
            // Note: MySQL doesn't support modifying enum values directly, so we need to change to string first

            // Change status column to string (allowing 'confirmed')
            $table->string('status', 50)->default('pending')->change();

            // Change payment_method column to string (allowing 'stripe')
            $table->string('payment_method', 50)->change();

            // Change payment_status column to string (already has the right values)
            $table->string('payment_status', 50)->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Revert back to enum if needed (optional)
            // For now, keep as string since it's more flexible
        });
    }
};
