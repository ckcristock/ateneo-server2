<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFixedAssetTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fixed_asset_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 150)->nullable();
            $table->enum('category', ['tangible', 'intangible'])->default('Tangible');
            $table->integer('useful_life_niif')->nullable();
            $table->double('annual_depreciation_percentage_niif', 20, 4)->nullable();
            $table->integer('useful_life_pcga')->nullable();
            $table->double('annual_depreciation_percentage_pcga', 20, 4)->nullable();
            $table->integer('niif_depreciation_account_plan_id')->nullable();
            $table->integer('pcga_depreciation_account_plan_id')->nullable();
            $table->integer('niif_account_plan_id')->nullable();
            $table->integer('pcga_account_plan_id')->nullable();
            $table->integer('niif_account_plan_credit_depreciation_id')->nullable();
            $table->integer('pcga_account_plan_credit_depreciation_id')->nullable();
            $table->integer('niif_account_plan_debit_depreciation_id')->nullable();
            $table->integer('pcga_account_plan_debit_depreciation_id')->nullable();
            $table->integer('consecutive')->nullable();
            $table->string('mantis', 50)->nullable();
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
        Schema::dropIfExists('fixed_asset_types');
    }
}
