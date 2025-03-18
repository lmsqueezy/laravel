<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lemon_squeezy_license_key_instances', function (Blueprint $table) {
            $table->id();
            $table->uuid('identifier')->unique();
            $table->string('license_key_id');
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lemon_squeezy_license_key_instances');
    }
};
