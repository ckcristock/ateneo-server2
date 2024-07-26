<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;



class CreateCausalNoConformeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Causal_No_Conforme', function (Blueprint $table) {
            $table->bigIncrements('Id_Causal_No_Conforme');
            $table->string('Codigo', 100)->nullable();
            $table->string('Nombre', 100)->nullable();
            $table->text('Tratamiento')->nullable();
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
        Schema::dropIfExists('Causal_No_Conforme');
    }
}
;
