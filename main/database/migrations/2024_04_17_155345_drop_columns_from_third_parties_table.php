<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropColumnsFromThirdPartiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('third_parties', function (Blueprint $table) {

            $table->dropForeign('third_parties_retefuente_account_id_foreign');
            $table->dropColumn('retefuente_account_id');
            $table->dropColumn('retefuente_percentage');
            $table->dropForeign('third_parties_reteiva_account_id_foreign');
            $table->dropColumn('reteiva_account_id');
            $table->dropColumn('reteiva_percentage');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('third_parties', function (Blueprint $table) {
            $table->integer('retefuente_account_id')->nullable();
            $table->decimal('retefuente_percentage', 5, 2)->nullable();
            $table->string('cuenta_reteiva')->nullable();
            $table->decimal('reteica_percentage', 5, 2)->nullable();
        });
    }
}

