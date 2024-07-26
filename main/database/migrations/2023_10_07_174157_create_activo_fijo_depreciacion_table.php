<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivoFijoDepreciacionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Activo_Fijo_Depreciacion', function (Blueprint $table) {
            $table->bigIncrements('Id_Activo_Fijo_Depreciacion');
            $table->integer('Id_Depreciacion')->nullable();
            $table->integer('Id_Activo_Fijo')->nullable();
            $table->decimal('Valor_PCGA', 20)->nullable();
            $table->decimal('Valor_NIIF', 20)->nullable();
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
        Schema::dropIfExists('Activo_Fijo_Depreciacion');
    }
}
;
