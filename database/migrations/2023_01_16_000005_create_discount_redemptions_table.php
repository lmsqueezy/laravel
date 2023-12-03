<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lemon_squeezy_discount_redemptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('billable_id');
            $table->string('billable_type');
            $table->string('lemon_squeezy_id')->unique();
            $table->unsignedBigInteger('discount_id');
            $table->unsignedBigInteger('order_id');
            $table->string('discount_name');
            $table->string('discount_code');
            $table->integer('discount_amount');
            $table->string('discount_amount_type');
            $table->integer('amount');
            $table->timestamps();

            $table->foreign('discount_id')->references('id')->on('lemon_squeezy_discounts')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('lemon_squeezy_orders')->onDelete('cascade');

            $table->index(['billable_id', 'billable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lemon_squeezy_discount_redemptions');
    }
};
