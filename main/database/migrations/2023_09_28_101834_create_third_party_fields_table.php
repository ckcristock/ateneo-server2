<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateThirdPartyFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('third_party_fields', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('label', 191)->nullable();
            $table->string('name', 191)->nullable();
            $table->string('type', 191)->nullable();
            $table->enum('required', ['si', 'no'])->nullable();
            $table->integer('length')->nullable();
            $table->enum('state', ['activo', 'inactivo'])->default('Activo');
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
        Schema::dropIfExists('third_party_fields');
    }
}
