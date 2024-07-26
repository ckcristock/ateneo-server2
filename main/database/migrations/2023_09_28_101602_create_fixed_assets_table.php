<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFixedAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type', 50)->nullable();
            $table->string('user_id', 50)->nullable();
            $table->integer('fixed_asset_type_id')->nullable();
            $table->decimal('niif_cost', 20, 6)->nullable();
            $table->decimal('pcga_cost', 20, 6)->nullable();
            $table->bigInteger('nit')->nullable();
            $table->decimal('iva', 20, 6)->nullable();
            $table->decimal('base', 20, 6)->nullable();
            $table->decimal('niif_iva', 20, 6)->nullable();
            $table->decimal('niif_base', 20, 6)->nullable();
            $table->integer('center_cost_id')->nullable();
            $table->string('name', 50)->nullable();
            $table->integer('amount')->nullable();
            $table->string('document', 50)->nullable();
            $table->string('reference', 50)->nullable();
            $table->bigInteger('code')->nullable();
            $table->integer('source')->nullable();
            $table->bigInteger('fixed_asset_code')->nullable();
            $table->string('concept', 50)->nullable();
            $table->date('date')->nullable();
            $table->integer('depreciation_type')->nullable();
            $table->decimal('source_rete_cost', 20, 6)->nullable();
            $table->decimal('ica_rete_cost', 20, 6)->nullable();
            $table->decimal('niif_source_rete_cost', 20, 6)->nullable();
            $table->decimal('niif_ica_rete_cost', 20, 6)->nullable();
            $table->integer('rete_ica_account_id')->nullable();
            $table->integer('source_rete_account_id')->nullable();
            $table->enum('state', ['activo', 'inactivo'])->nullable();
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
        Schema::dropIfExists('fixed_assets');
    }
}
