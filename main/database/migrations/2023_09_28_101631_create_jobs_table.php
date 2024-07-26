<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code')->nullable();
            $table->integer('company_id')->default(0);
            $table->string('title', 500)->default('0');
            $table->timestamp('date_start')->nullable();
            $table->timestamp('date_end')->nullable();
            $table->integer('position_id')->nullable();
            $table->integer('municipality_id')->nullable();
            $table->double('min_salary', 50, 2)->nullable();
            $table->double('max_salary', 50, 2)->nullable();
            $table->enum('turn_type', ['Fijo', 'Rotativo'])->nullable();
            $table->text('description')->nullable();
            $table->string('education', 200)->nullable();
            $table->integer('experience_year')->nullable();
            $table->integer('min_age')->nullable();
            $table->integer('max_age')->nullable();
            $table->tinyInteger('can_trip')->nullable();
            $table->tinyInteger('change_residence')->nullable();
            $table->enum('state', ['Activo', 'Cancelada'])->default('Activo');
            $table->tinyInteger('visa')->nullable();
            $table->integer('visa_type_id')->nullable();
            $table->integer('salary_type_id')->nullable();
            $table->integer('work_contract_type_id')->nullable();
            $table->enum('passport', ['Si', 'No'])->nullable();
            $table->integer('document_type_id')->nullable();
            $table->string('conveyance', 191)->nullable();
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
        Schema::dropIfExists('jobs');
    }
}
