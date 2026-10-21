<?php

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
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained()->restrictOnDelete();
            $table->string('name', 50);
            $table->string('postal_code', 8)->nullable();
            $table->text('address')->nullable();
            $table->string('contact_name', 50)->nullable();
            $table->string('phone', 30)->nullable();
            $table->foreignId('carrier_id')->constrained()->restrictOnDelete();
            $table->boolean('size_based');
            $table->integer('free_shipping_threshold_yen')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
