<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFixedTurnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fixed_turns', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 191);
            $table->tinyInteger('extra_hours');
            $table->integer('entry_tolerance');
            $table->integer('leave_tolerance');
            $table->string('color', 191);
            $table->unsignedBigInteger('company_id')->default(0)->index('company_foreign');
            $table->timestamps();
            $table->enum('state', ['activo', 'inactivo'])->default('Activo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fixed_turns');
    }
}
