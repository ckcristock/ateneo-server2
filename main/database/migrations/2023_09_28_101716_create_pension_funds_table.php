<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePensionFundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pension_funds', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 150)->nullable();
            $table->string('code', 50)->nullable();
            $table->string('nit', 50)->nullable();
            $table->enum('status7', ['Activo', 'Inactivo'])->default('Activo');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pension_funds');
    }
}
