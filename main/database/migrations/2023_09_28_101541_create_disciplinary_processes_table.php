<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDisciplinaryProcessesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('disciplinary_processes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('person_id')->nullable();
            $table->string('process_description', 50);
            $table->date('date_of_admission')->nullable();
            $table->date('date_end')->nullable();
            $table->timestamps();
            $table->enum('status', ['Abierto', 'Cerrado'])->default('Abierto');
            $table->string('file', 500)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('disciplinary_processes');
    }
}
