<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventaryDotationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventary_dotations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('product_dotation_type_id')->nullable();
            $table->string('name', 50)->nullable();
            $table->string('code', 50)->nullable();
            $table->enum('type', ['Dotación', 'EPP'])->nullable();
            $table->enum('status', ['Nuevo', 'Usado'])->nullable();
            $table->double('cost', 50, 2)->nullable();
            $table->integer('stock')->nullable();
            $table->timestamps();
            $table->bigInteger('product_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventary_dotations');
    }
}
