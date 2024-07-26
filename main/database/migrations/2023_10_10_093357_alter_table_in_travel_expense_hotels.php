<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableInTravelExpenseHotels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('travel_expense_hotels', function (Blueprint $table) {
            $table->dropColumn('accommodation');
        });
        Schema::table('travel_expense_hotels', function (Blueprint $table) {
            $table->bigInteger('accommodation')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('travel_expense_hotels', function (Blueprint $table) {
            //
        });
    }
}
