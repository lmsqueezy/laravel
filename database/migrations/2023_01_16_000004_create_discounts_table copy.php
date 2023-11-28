<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lemon_squeezy_discounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('billable_id');
            $table->string('billable_type');
            $table->string('lemon_squeezy_id')->unique();
            $table->string('name');
            $table->string('code')->unique();
            $table->integer('amount');
            $table->string('amount_type');
            $table->boolean('is_limited_to_products')->default(false);
            $table->boolean('is_limited_redemptions')->default(false);
            $table->integer('max_redemptions')->nullable();
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->string('duration')->nullable();
            $table->integer('duration_in_months')->nullable();
            $table->string('status');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lemon_squeezy_discounts');
    }
};
