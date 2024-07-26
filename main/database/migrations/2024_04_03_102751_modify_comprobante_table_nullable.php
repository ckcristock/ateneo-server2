<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyComprobanteTableNullable extends Migration
{
    public function up()
    {
        Schema::table('Comprobante', function (Blueprint $table) {
            $table->text('Observaciones')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('Comprobante', function (Blueprint $table) {
            $table->text('Observaciones')->change();
        });
    }
}

