<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBooleanFieldsToCategoriaNuevaTable extends Migration
{
    public function up()
    {
        Schema::table('Categoria_Nueva', function (Blueprint $table) {
            $table->boolean('receives_barcode')->default(false);
            $table->boolean('is_stackable')->default(false);
            $table->boolean('is_inventory')->default(false);
            $table->boolean('is_listed')->default(false);
        });
    }

    public function down()
    {
        Schema::table('Categoria_Nueva', function (Blueprint $table) {
            $table->dropColumn('receives_barcode');
            $table->dropColumn('is_stackable');
            $table->dropColumn('is_inventory');
            $table->dropColumn('is_listed');
        });
    }
}
