<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVariablesClinicalHistoryModelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('variables_clinical_history_models', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 55);
            $table->string('property', 55);
            $table->string('type', 55);
            $table->string('valor')->nullable();
            $table->integer('module_clinical_history_model_id');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->string('required', 20);
            $table->string('dependence', 20);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('variables_clinical_history_models');
    }
}
