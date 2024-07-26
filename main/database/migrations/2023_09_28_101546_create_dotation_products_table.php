<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDotationProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dotation_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('dotation_id')->nullable();
            $table->integer('inventary_dotation_id')->nullable();
            $table->integer('quantity');
            $table->double('cost', 50, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dotation_products');
    }
}
