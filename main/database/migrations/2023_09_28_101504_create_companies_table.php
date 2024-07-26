<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable()->index('name');
            $table->string('short_name', 50)->nullable();
            $table->string('tin')->nullable();
            $table->integer('dv')->nullable();
            $table->string('address')->nullable();
            $table->string('code')->nullable();
            $table->string('agreements')->nullable();
            $table->enum('category', ['ips', 'servicios'])->nullable();
            $table->string('city')->nullable();
            $table->string('country_code')->nullable();
            $table->string('creation_date')->nullable();
            $table->tinyInteger('disabled')->nullable();
            $table->string('email')->nullable();
            $table->string('encoding_characters')->nullable();
            $table->unsignedBigInteger('interface_id')->default(0);
            $table->text('logo')->nullable();
            $table->unsignedBigInteger('parent_id')->default(0);
            $table->string('pbx')->nullable();
            $table->bigInteger('regional_id')->default(0);
            $table->tinyInteger('send_email')->nullable();
            $table->string('settings')->nullable();
            $table->string('slogan')->nullable();
            $table->string('phone', 10)->nullable();
            $table->string('email_contact', 50)->nullable();
            $table->string('social_reason', 50)->nullable();
            $table->string('document_type', 15)->nullable();
            $table->boolean('state')->default(0);
            $table->string('telephone')->nullable();
            $table->unsignedTinyInteger('type')->nullable();
            $table->string('api_key', 100)->nullable();
            $table->integer('globo_id')->nullable();
            $table->string('simbol', 10)->nullable();
            $table->string('payment_frequency', 50)->nullable();
            $table->string('account_type')->nullable();
            $table->string('account_number', 50)->nullable();
            $table->integer('bank_id')->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->integer('base_salary')->nullable();
            $table->string('paid_operator', 50)->nullable();
            $table->tinyInteger('law_1429')->nullable();
            $table->tinyInteger('law_590')->nullable();
            $table->tinyInteger('law_1607')->nullable();
            $table->integer('transportation_assistance')->nullable();
            $table->integer('arl_id')->nullable();
            $table->time('night_end_time')->nullable();
            $table->time('night_start_time')->nullable();
            $table->integer('max_late_arrival')->nullable();
            $table->integer('max_holidays_legal')->nullable();
            $table->integer('max_extras_hours')->nullable();
            $table->string('page_heading', 100)->nullable();
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
        Schema::dropIfExists('companies');
    }
}
