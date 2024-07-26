<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;



class CreateTipoActivoFijoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Tipo_Activo_Fijo', function (Blueprint $table) {
            $table->bigIncrements('Id_Tipo_Activo_Fijo');
            $table->string('Nombre_Tipo_Activo', 150);
            $table->enum('Categoria', ['Tangible', 'Intangible'])->default('Tangible');
            $table->integer('Vida_Util')->comment('Este valor representa una cantidad en años');
            $table->float('Porcentaje_Depreciacion_Anual', 20, 4)->comment('Este valor representa porcentaje');
            $table->integer('Vida_Util_PCGA')->nullable();
            $table->float('Porcentaje_Depreciacion_Anual_PCGA', 20, 4)->nullable();
            $table->integer('Id_Plan_Cuenta_Depreciacion_NIIF')->nullable();
            $table->integer('Id_Plan_Cuenta_Depreciacion_PCGA')->nullable();
            $table->integer('Id_Plan_Cuenta_NIIF');
            $table->integer('Id_Plan_Cuenta_PCGA');
            $table->enum('Estado', ['Activo', 'Inactivo'])->default('Activo');
            $table->integer('Id_Plan_Cuenta_Credito_Depreciacion_PCGA')->nullable();
            $table->integer('Id_Plan_Cuenta_Credito_Depreciacion_NIIF')->nullable();
            $table->integer('Consecutivo')->nullable();
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
        Schema::dropIfExists('Tipo_Activo_Fijo');
    }
}
;
