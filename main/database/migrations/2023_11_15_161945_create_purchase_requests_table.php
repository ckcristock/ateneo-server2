<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('category_id');
            $table->date('expected_date')->nullable();
            $table->longText('observations')->nullable();
            $table->integer('quantity_of_products')->default(0);
            $table->enum('status', ['Pendiente', 'Cotizada', 'Aprobada', 'Comprada'])->default('Pendiente');
            $table->string('code');
            $table->string('format_code', 50);
            $table->integer('user_id')->nullable();
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
        Schema::dropIfExists('purchase_requests');
    }
}
