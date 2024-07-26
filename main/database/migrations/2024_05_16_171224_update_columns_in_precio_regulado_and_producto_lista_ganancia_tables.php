<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('Precio_Regulado', function (Blueprint $table) {
            // Rename column first to avoid conflict
            $table->renameColumn('Codigo_Cum', 'Id_Producto_temp');
        });

        Schema::table('Precio_Regulado', function (Blueprint $table) {
            // Change column type
            $table->unsignedBigInteger('Id_Producto_temp')->change();
            // Rename to the final column name
            $table->renameColumn('Id_Producto_temp', 'Id_Producto');
        });

        Schema::table('Producto_Lista_Ganancia', function (Blueprint $table) {
            // Rename column first to avoid conflict
            $table->renameColumn('Cum', 'Id_Producto_temp');
        });

        Schema::table('Producto_Lista_Ganancia', function (Blueprint $table) {
            // Change column type
            $table->unsignedBigInteger('Id_Producto_temp')->change();
            // Rename to the final column name
            $table->renameColumn('Id_Producto_temp', 'Id_Producto');
        });
    }

    public function down(): void
    {
        Schema::table('Precio_Regulado', function (Blueprint $table) {
            // Rename column back to the old name
            $table->renameColumn('Id_Producto', 'Codigo_Cum');
            // Change column type back to original
            $table->string('Codigo_Cum', 60)->change();
        });

        Schema::table('Producto_Lista_Ganancia', function (Blueprint $table) {
            // Rename column back to the old name
            $table->renameColumn('Id_Producto', 'Cum');
            // Change column type back to original
            $table->string('Cum', 200)->change();
        });
    }
};
