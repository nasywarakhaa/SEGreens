<?php

use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('order_number', 30)->unique();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('store_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('user_address_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('fulfillment_type_code')->default(FulfillmentType::Delivery->value)->index();
            $table->unsignedTinyInteger('status_code')->default(OrderStatus::Pending->value)->index();
            $table->unsignedTinyInteger('payment_status_code')->default(PaymentStatus::Unpaid->value)->index();
            $table->text('note')->nullable();
            $table->string('cancel_reason', 255)->nullable();
            $table->timestamp('schedule_at')->nullable();
            $table->decimal('distance_km', 8, 2)->default(0);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('delivery_fee', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_price', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
