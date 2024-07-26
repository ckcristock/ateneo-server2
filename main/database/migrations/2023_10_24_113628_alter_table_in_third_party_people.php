<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableInThirdPartyPeople extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('third_party_people', function (Blueprint $table) {
            $table->string('landline', 50)->change();
            $table->string('cell_phone', 50)->change();
            $table->text('observation')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('third_party_people', function (Blueprint $table) {
            //
        });
    }
}
