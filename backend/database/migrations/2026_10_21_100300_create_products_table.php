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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->restrictOnDelete();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->integer('price_incl_tax_yen');
            $table->unsignedTinyInteger('tax_rate_percent');
            $table->boolean('no_stock_management');
            $table->integer('stock_quantity')->nullable();
            $table->string('temperature', 10);
            $table->decimal('packing_length_cm', 6, 1)->nullable();
            $table->decimal('packing_width_cm', 6, 1)->nullable();
            $table->decimal('packing_height_cm', 6, 1)->nullable();
            $table->decimal('packing_weight_kg', 6, 2)->nullable();
            $table->boolean('accepts_upload');
            $table->boolean('is_food');
            $table->text('food_label')->nullable();
            $table->text('allergens')->nullable();
            $table->string('publish_status', 10);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');

            $table->index(['store_id', 'updated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
