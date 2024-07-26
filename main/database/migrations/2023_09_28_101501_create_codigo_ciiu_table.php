<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCodigoCiiuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Codigo_Ciiu', function (Blueprint $table) {
            $table->bigIncrements('Id_Codigo_Ciiu');
            $table->string('Codigo', 60);
            $table->text('Descripcion');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Codigo_Ciiu');
    }
}
