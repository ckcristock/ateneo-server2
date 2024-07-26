<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('Categoria_Nueva', function (Blueprint $table) {
            $table->boolean('has_lote')->default(false);
            $table->boolean('has_expiration_date')->default(false);
        });
    }

    public function down()
    {
        Schema::table('Categoria_Nueva', function (Blueprint $table) {
            $table->dropColumn('has_lote');
            $table->dropColumn('has_expiration_date');
        });
    }
};
