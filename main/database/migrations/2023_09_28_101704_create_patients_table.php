<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePatientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->json('query_history')->nullable();
            $table->integer('type_document_id')->nullable();
            $table->bigInteger('identifier')->nullable()->index('identifier');
            $table->integer('regimen_id')->nullable()->index('regimen_id');
            $table->string('affiliate_type', 50)->nullable();
            $table->string('category_affiliate', 50)->nullable();
            $table->string('secondsurname', 50)->nullable();
            $table->string('surname', 50)->nullable();
            $table->string('firstname', 50)->nullable();
            $table->string('middlename', 50)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('state', 50)->default('Activo');
            $table->string('gener', 20)->nullable();
            $table->string('population_group', 50)->nullable();
            $table->integer('contract_id')->nullable()->index('contract_id');
            $table->integer('regional_id')->nullable()->index('regional_id');
            $table->integer('location_id')->nullable()->index('location_id');
            $table->integer('company_id')->nullable()->index('company_id');
            $table->unsignedInteger('eps_id')->nullable()->index('eps_id');
            $table->string('phone', 100)->nullable();
            $table->string('address')->nullable();
            $table->string('civil_state', 20)->nullable();
            $table->integer('level_id')->nullable()->index('level_id');
            $table->integer('department_id')->nullable()->index('department_id');
            $table->integer('municipality_id')->nullable()->index('municipality_id');
            $table->string('email', 50)->nullable();
            $table->string('ips_principal')->nullable();
            $table->enum('zone', ['urbano', 'rural'])->nullable();
            $table->text('description')->nullable();
            $table->string('blood_type', 5)->nullable();
            $table->string('manager', 50)->nullable();
            $table->string('rol_manager', 50)->nullable();
            $table->string('phone_manager', 50)->nullable();
            $table->string('ethnic_group', 50)->nullable();
            $table->string('token', 11)->nullable();
            $table->string('ep')->nullable();
            $table->string('database', 50)->nullable();
            $table->timestamps();
            $table->integer('municipio')->default(0);
            $table->string('optional_phone', 20)->nullable();
            $table->integer('Realidad')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('patients');
    }
}
