<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('people', function (Blueprint $table) {
            $table->unsignedBigInteger('identifier')->nullable(false)->change();
            $table->foreign('type_document_id')->references('id')->on('document_types');
            $table->unsignedBigInteger('type_document_id')->nullable(false)->change();
            $table->string('first_surname')->nullable(false)->change();
            $table->string('full_name')->nullable(false)->change();
            $table->date('birth_date')->nullable(false)->change();
            $table->dropColumn('birth_place');
            $table->dropColumn('optional_phone');
            $table->dropColumn('cellphone');
            $table->string('address')->nullable(false)->change();
            $table->dropColumn('degree_instruction');
            $table->dropColumn('talla_pantalon');
            $table->dropColumn('talla_bata');
            $table->dropColumn('talla_botas');
            $table->dropColumn('talla_camisa');
            $table->foreign('eps_id')->references('id')->on('eps');
            $table->unsignedBigInteger('eps_id')->nullable(true)->change();
            $table->foreign('compensation_fund_id')->references('id')->on('compensation_funds');
            $table->unsignedBigInteger('compensation_fund_id')->nullable(true)->change();
            $table->foreign('people_type_id')->references('id')->on('type_persons');
            $table->unsignedBigInteger('people_type_id')->nullable(false)->change();
            $table->foreign('severance_fund_id')->references('id')->on('severance_funds');
            $table->unsignedBigInteger('severance_fund_id')->nullable(true)->change();
            $table->string('shirt_size')->nullable(false)->change();
            $table->string('shue_size')->nullable(false)->after('shirt_size');
            $table->foreign('pension_fund_id')->references('id')->on('pension_funds');
            $table->unsignedBigInteger('pension_fund_id')->nullable(true)->change();
            $table->foreign('arl_id')->references('id')->on('arl');
            $table->unsignedBigInteger('arl_id')->nullable(true)->change();
            $table->string('pants_size')->nullable(false)->change();
            $table->unsignedBigInteger('payroll_risks_arl_id')->nullable()->change();
            $table->foreign('company_worked_id')->references('id')->on('companies');
            $table->unsignedBigInteger('company_worked_id')->change();
            $table->foreign('dispensing_point_id')->references('Id_Punto_Dispensacion')->on('Punto_Dispensacion');
            $table->unsignedBigInteger('dispensing_point_id')->change();
            $table->string('place_of_birth')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('people', function (Blueprint $table) {
            //
        });
    }
};
