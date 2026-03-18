<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('total_orders')->default(0)->after('role_id');
            $table->decimal('total_spent', 10, 2)->default(0)->after('total_orders');
            $table->date('first_order_date')->nullable()->after('total_spent');
            $table->date('last_order_date')->nullable()->after('first_order_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['total_orders', 'total_spent', 'first_order_date', 'last_order_date']);
        });
    }
};
