<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuotationPurchaseRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quotation_purchase_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('product_purchase_request_id')->nullable();
            $table->string('code');
            $table->string('format_code', 50);
            $table->unsignedBigInteger('third_party_id')->nullable();
            $table->double('total_price', 50, 2)->nullable();
            $table->enum('status', ['Pendiente', 'Aprobada', 'Rechazada'])->nullable()->default('Pendiente');
            $table->string('file', 500)->nullable();
            $table->unsignedBigInteger('purchase_request_id')->nullable();
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
        Schema::dropIfExists('quotation_purchase_requests');
    }
}
