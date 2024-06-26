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
        Schema::create('payment_settings', function (Blueprint $table) {
            $table->id();
            $table->text('address');
            $table->unsignedInteger('currency_id');
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade')->onUpdate('cascade');
            $table->string('blockchain_type');
            $table->text('payment_qr');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_settings');
    }
};
