<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('Producto_Nota_Credito_Global', function (Blueprint $table) {
            $table->id('Id_Producto_Nota_Credito_Global');
            $table->bigInteger('Id_Nota_Credito_Global')->nullable();
            $table->string('Tipo_Producto', 2000)->nullable();
            $table->bigInteger('Id_Producto')->nullable();
            $table->string('Nombre_Producto', 2000)->nullable();
            $table->string('Observacion', 2000)->nullable();
            $table->decimal('Valor_Nota_Credito', 50, 2)->nullable();
            $table->decimal('Impuesto', 50, 2)->nullable();
            $table->decimal('Precio_Nota_Credito', 50, 3)->nullable();
            $table->integer('Cantidad')->nullable();
            $table->integer('Id_Causal_No_Conforme')->nullable();
            $table->integer('Id_Causal_Anulacion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Producto_Nota_Credito_Global');
    }
};
