<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lemon_squeezy_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('billable_id');
            $table->string('billable_type');
            $table->string('lemon_squeezy_id')->unique();
            $table->string('customer_id');
            $table->uuid('identifier')->unique();
            $table->string('product_id');
            $table->string('variant_id');
            $table->integer('order_number')->unique();
            $table->string('currency');
            $table->integer('subtotal');
            $table->integer('discount_total');
            $table->integer('tax');
            $table->integer('total');
            $table->string('tax_name');
            $table->string('status');
            $table->string('receipt_url')->nullable();
            $table->boolean('refunded');
            $table->timestamp('refunded_at')->nullable();
            $table->timestamp('ordered_at');
            $table->timestamps();

            $table->index(['billable_id', 'billable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lemon_squeezy_orders');
    }
};
