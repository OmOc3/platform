<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->json('meta')->nullable()->after('placed_at');
        });

        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->unsignedInteger('attempt_number')->default(1);
            $table->string('provider');
            $table->string('status');
            $table->unsignedInteger('amount')->default(0);
            $table->string('currency', 3)->default('EGP');
            $table->string('provider_reference')->nullable()->unique();
            $table->string('provider_transaction_reference')->nullable();
            $table->string('checkout_url')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->string('failure_code')->nullable();
            $table->text('failure_message')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->unique(['order_id', 'attempt_number']);
        });

        Schema::create('payment_webhook_receipts', function (Blueprint $table): void {
            $table->id();
            $table->string('provider');
            $table->string('event_key');
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('status')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'event_key']);
            $table->index(['payment_id', 'order_id']);
        });

        Schema::create('shipments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained('orders')->cascadeOnDelete();
            $table->string('status');
            $table->string('recipient_name');
            $table->string('phone');
            $table->string('alternate_phone')->nullable();
            $table->string('governorate');
            $table->string('city');
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('landmark')->nullable();
            $table->unsignedInteger('shipping_fee_amount')->default(0);
            $table->string('currency', 3)->default('EGP');
            $table->string('carrier_name')->nullable();
            $table->string('carrier_reference')->nullable();
            $table->timestamp('prepared_at')->nullable();
            $table->timestamp('handed_to_carrier_at')->nullable();
            $table->timestamp('in_transit_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['status', 'governorate', 'city']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('payment_webhook_receipts');
        Schema::dropIfExists('payments');

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn('meta');
        });
    }
};
