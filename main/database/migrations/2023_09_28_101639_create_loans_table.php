<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code')->nullable();
            $table->timestamp('date')->nullable()->useCurrent();
            $table->enum('interest_type', ['Sin', 'Prestamo', 'Capital'])->default('Sin');
            $table->double('interest', 50, 2)->nullable();
            $table->integer('account_plain_id')->default(0);
            $table->decimal('value', 20, 2)->nullable();
            $table->enum('pay_fees', ['Si', 'No'])->nullable();
            $table->integer('number_fees')->nullable();
            $table->decimal('monthly_fee', 20, 2)->nullable();
            $table->enum('payment_type', ['Quincenal', 'Mensual'])->default('Quincenal');
            $table->date('first_payment_date')->nullable();
            $table->text('observation')->nullable();
            $table->enum('state', ['Pendiente', 'Pagada', 'Anulada'])->default('Pendiente');
            $table->string('type', 100)->default('Prestamo');
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('person_id')->nullable();
            $table->string('Mes', 45)->nullable();
            $table->integer('Quincena')->nullable();
            $table->decimal('outstanding_balance', 20, 2)->nullable();
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
        Schema::dropIfExists('loans');
    }
}
