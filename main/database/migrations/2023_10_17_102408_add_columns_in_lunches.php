<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsInLunches extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lunches', function (Blueprint $table) {
            $table->bigInteger('person_id')->after('user_id');
            $table->bigInteger('dependency_id')->after('state');
            $table->enum('apply', ['Si', 'No'])->nullable()->after('state');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lunches', function (Blueprint $table) {
            //
        });
    }
}
