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
        Schema::create('orden_compra_nacional_purchase_request', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_orden_compra');
            $table->unsignedBigInteger('id_purchase_request');
            $table->foreign('id_orden_compra','fk_orden_compra')->references('Id_Orden_Compra_Nacional')->on('Orden_Compra_Nacional')->onDelete('cascade');
            $table->foreign('id_purchase_request','fk_purchase_request')->references('id')->on('purchase_requests')->onDelete('cascade');
            $table->enum('status', ['activo', 'inactivo']);
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_compra_nacional_purchase_request');
    }
};
