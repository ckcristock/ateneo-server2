<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateThirdPartiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('third_parties', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nit', 50)->nullable();
            $table->enum('person_type', ['natural', 'juridico'])->nullable();
            $table->enum('third_party_type', ['cliente', 'proveedor'])->nullable();
            $table->string('social_reason', 150)->nullable();
            $table->string('first_name', 50)->nullable();
            $table->string('second_name', 50)->nullable();
            $table->string('first_surname', 50)->nullable();
            $table->string('second_surname', 50)->nullable();
            $table->string('dian_address', 50)->nullable();
            $table->string('address_one', 50)->nullable();
            $table->string('address_two', 50)->nullable();
            $table->string('address_three', 50)->nullable();
            $table->string('address_four', 50)->nullable();
            $table->string('cod_dian_address', 50)->nullable();
            $table->string('tradename', 50)->nullable();
            $table->integer('department_id')->nullable();
            $table->integer('municipality_id')->nullable();
            $table->integer('zone_id')->nullable();
            $table->string('landline', 50)->nullable();
            $table->string('cell_phone', 50)->nullable();
            $table->string('email', 50)->nullable();
            $table->integer('winning_list_id')->nullable();
            $table->enum('apply_iva', ['si', 'no'])->nullable();
            $table->string('contact_payments', 50)->nullable();
            $table->string('phone_payments', 50)->nullable();
            $table->string('email_payments', 50)->nullable();
            $table->string('regime', 50)->nullable();
            $table->enum('encourage_profit', ['si', 'no'])->nullable();
            $table->integer('ciiu_code_id')->nullable();
            $table->enum('withholding_agent', ['si', 'no'])->nullable();
            $table->enum('withholding_oninvoice', ['si', 'no'])->nullable();
            $table->string('reteica_type', 50)->nullable();
            $table->integer('reteica_account_id')->nullable();
            $table->integer('retefuente_account_id')->nullable();
            $table->string('g_contribut', 50)->nullable();
            $table->integer('reteiva_account_id')->nullable();
            $table->string('condition_payment', 50)->nullable();
            $table->string('assigned_space', 50)->nullable();
            $table->string('discount_prompt_payment', 50)->nullable();
            $table->string('discount_days', 50)->nullable();
            $table->enum('state', ['activo', 'inactivo'])->default('Activo');
            $table->string('rut', 191)->nullable();
            $table->string('image', 500)->nullable();
            $table->integer('company_id')->nullable();
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
        Schema::dropIfExists('third_parties');
    }
}
