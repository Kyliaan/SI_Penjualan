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
        Schema::create('product_transactions', function (Blueprint $table) {
            $table->id();
            $table->string(column: 'name');
            $table->string(column: 'phone');
            $table->string(column: 'email');
            $table->string(column: 'booking_trx_id');
            $table->string(column: 'city');
            $table->string(column: 'post_code');
            $table->string(column: 'proof');
            $table->unsignedBigInteger(column: 'produk_size');
            $table->text(column: 'address');
            $table->unsignedBigInteger(column: 'quantity');
            $table->unsignedBigInteger(column: 'sub_total_amount');
            $table->unsignedBigInteger(column: 'grand_total_amount');
            $table->boolean(column: 'is_paid');
            $table->foreignId(column: 'produk_id')->constrained()->cascadeOnDelete();
            $table->foreignId(column: 'promo_code_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_transactions');
    }
};
