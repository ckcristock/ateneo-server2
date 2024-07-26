<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMemorandumsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('memorandums', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('person_id');
            $table->text('details')->nullable();
            $table->string('file', 500)->nullable();
            $table->enum('level', ['leve', 'grave'])->nullable();
            $table->enum('state', ['pendiente', 'aprobado', 'legalizado'])->default('Pendiente');
            $table->bigInteger('approve_user_id')->nullable();
            $table->timestamps();
            $table->bigInteger('memorandum_type_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('memorandums');
    }
}
