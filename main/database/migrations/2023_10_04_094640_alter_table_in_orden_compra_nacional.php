<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AlterTableInOrdenCompraNacional extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Orden_Compra_Nacional', function (Blueprint $table) {
            $table->dropColumn('Codigo');
            $table->dropColumn('Estado');
        });

        Schema::table('Orden_Compra_Nacional', function (Blueprint $table) {
            $table->string('Codigo', 100)->nullable();
            $table->enum('Estado', ['Pendiente', 'Anulada', 'Recibida'])->default('Pendiente');
            $table->date('Fecha_Entrega_Real')->nullable()->after('Fecha_Entrega_Probable');
            $table->string('format_code', 100)->nullable()->after('Id_Pre_Compra');
            $table->double('Subtotal', 50, 2)->nullable()->after('Id_Pre_Compra');
            $table->double('Iva', 50, 2)->nullable()->after('Id_Pre_Compra');
            $table->double('Total', 50, 2)->nullable()->after('Id_Pre_Compra');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('OrdenCompraNacional', function (Blueprint $table) {
            //
        });
    }
}
