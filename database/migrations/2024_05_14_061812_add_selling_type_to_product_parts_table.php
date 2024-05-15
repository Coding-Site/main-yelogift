<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('product_parts', function (Blueprint $table) {
        $table->string('selling_type')->default('manual');
    });
}

public function down()
{
    Schema::table('product_parts', function (Blueprint $table) {
        $table->dropColumn('selling_type');
    });
}
};
