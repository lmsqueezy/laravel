<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lemon_squeezy_customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('billable_id');
            $table->string('billable_type');
            $table->string('lemon_squeezy_id')->nullable()->unique();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();

            $table->unique(['billable_id', 'billable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lemon_squeezy_customers');
    }
};
