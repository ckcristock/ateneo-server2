<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBooleanFieldsToThirdPartiesTable extends Migration
{
    public function up()
    {
        Schema::table('third_parties', function (Blueprint $table) {
            $table->boolean('retefuente_applies')->default(false)->after('company_id');
            $table->boolean('reteiva_applies')->default(false)->after('retefuente_applies');
        });
    }

    public function down()
    {
        Schema::table('third_parties', function (Blueprint $table) {
            $table->dropColumn('retefuente_applies');
            $table->dropColumn('reteiva_applies');
        });
    }
}

