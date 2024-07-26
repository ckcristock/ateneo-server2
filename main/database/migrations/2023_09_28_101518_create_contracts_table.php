<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('status')->default(1);
            $table->string('name');
            $table->enum('state', ['activo', 'inactivo'])->default('Activo');
            $table->string('number');
            $table->string('code', 50)->nullable();
            $table->bigInteger('administrator_id')->nullable();
            $table->string('price', 50)->nullable();
            $table->bigInteger('company_id')->nullable();
            $table->bigInteger('contract_type_id')->nullable();
            $table->bigInteger('department_id')->nullable();
            $table->bigInteger('location_id')->nullable();
            $table->bigInteger('regimen_id')->nullable();
            $table->bigInteger('eps_id')->nullable();
            $table->bigInteger('payment_method_id')->nullable();
            $table->bigInteger('payment_methods_contracts_id')->nullable();
            $table->bigInteger('benefits_plans_id')->nullable();
            $table->bigInteger('benefits_plan_id')->nullable();
            $table->bigInteger('price_list_id')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('site');
            $table->string('variation');
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
        Schema::dropIfExists('contracts');
    }
}
