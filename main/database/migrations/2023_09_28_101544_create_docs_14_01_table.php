<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocs1401Table extends Migration
{
    /*! REVISAR BASE DE DATOS EN CPANEL */
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('docs_14_01', function (Blueprint $table) {
            $table->decimal('cumentoPaciente', 15, 0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('docs_14_01');
    }
}
