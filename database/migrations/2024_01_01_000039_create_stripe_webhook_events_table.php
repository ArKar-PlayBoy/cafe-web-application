<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stripe_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_event_id', 255)->unique();
            $table->timestamp('processed_at')->useCurrent();
            $table->string('payload_hash', 64)->nullable();
            $table->string('event_type', 100)->nullable();
            $table->timestamps();

            $table->index('processed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stripe_webhook_events');
    }
};
