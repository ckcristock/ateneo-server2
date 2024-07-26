<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductoListaGananciaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Producto_Lista_Ganancia', function (Blueprint $table) {
            $table->bigIncrements('Id_Producto_Lista_Ganancia');
            $table->string('Cum', 200)->nullable();
            $table->double('Precio', 50, 2)->nullable();
            $table->bigInteger('Id_Lista_Ganancia')->nullable();
            $table->decimal('Precio_Anterior', 50, 2)->nullable();
            $table->timestamp('Ultima_Actualizacion')->nullable()->useCurrent();
            $table->enum('Estado', ['activo', 'anulado'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Producto_Lista_Ganancia');
    }
}
