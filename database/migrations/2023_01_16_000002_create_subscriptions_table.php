<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lemon_squeezy_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('billable_id');
            $table->string('billable_type');
            $table->string('type');
            $table->string('lemon_squeezy_id')->unique();
            $table->string('status');
            $table->string('product_id');
            $table->string('variant_id');
            $table->string('card_brand')->nullable();
            $table->string('card_last_four')->nullable();
            $table->string('pause_mode')->nullable();
            $table->timestamp('pause_resumes_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('renews_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->index(['billable_id', 'billable_type']);
        });
    }
};
