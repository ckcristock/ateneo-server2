<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('work_contract_types', function (Blueprint $table) {
            $table->longText('template')->nullable()->after('description');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('work_contract_types', function (Blueprint $table) {
            $table->dropColumn('template');
        });
    }

};
