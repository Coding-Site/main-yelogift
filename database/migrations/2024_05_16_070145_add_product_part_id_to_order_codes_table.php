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
        Schema::table('order_codes', function (Blueprint $table) {
            $table->unsignedBigInteger('product_part_id')->after('order_product_id')->default(0);
            $table->foreign('product_part_id')->references('id')->on('product_parts')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_codes', function (Blueprint $table) {
            // $table->dropForeign(['product_part_id']);
            // $table->dropColumn('product_part_id');
        });
    }
};