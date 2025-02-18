<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lemon_squeezy_license_keys', function (Blueprint $table) {
            $table->id();
            $table->string('lemon_squeezy_id')->unique();
            $table->string('license_key')->unique();
            $table->string('status');
            $table->string('order_id')->index();
            $table->string('product_id')->index();
            $table->boolean('disabled');
            $table->integer('activation_limit')->nullable();
            $table->integer('instances_count');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('lemon_squeezy_id')->on('lemon_squeezy_orders');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lemon_squeezy_license_keys');
    }
};
