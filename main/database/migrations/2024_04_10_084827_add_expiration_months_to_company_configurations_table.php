<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExpirationMonthsToCompanyConfigurationsTable extends Migration
{
    public function up()
    {
        Schema::table('company_configurations', function (Blueprint $table) {
            $table->integer('Expiration_Months')->after('attention_expiry_days')->nullable();
        });
    }

    public function down()
    {
        Schema::table('company_configurations', function (Blueprint $table) {
            $table->dropColumn('Expiration_Months');
        });
    }
}

