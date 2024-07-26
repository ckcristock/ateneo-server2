<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLegalDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('legal_documents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('file', 500);
            $table->integer('disciplinary_process_id');
            $table->string('name', 250);
            $table->string('type', 20);
            $table->enum('state', ['Activo', 'Inactivo'])->nullable()->default('Activo');
            $table->string('motivo', 500)->nullable();
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
        Schema::dropIfExists('legal_documents');
    }
}
