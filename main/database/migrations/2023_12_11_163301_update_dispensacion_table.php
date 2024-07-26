<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('Dispensacion', function (Blueprint $table) {
            $table->date('Fecha_Reportar')->nullable();
            $table->enum('Estado_Acta', ['Validado', 'Sin Validar', 'Con Observacion'])->nullable();
            $table->string('Codigo_Radicacion')->nullable();
            $table->enum('Estado_Alistamiento', ['Alistado', 'Vacio'])->nullable();
            $table->integer('Id_Dispositivo_Radicacion')->nullable();
            $table->integer('Paciente_Reclamante')->nullable();
            $table->enum('Tipo_Entrega', ['Fisico', 'Domicilio'])->nullable();
            $table->integer('Id_Punto_Dispensacion_Entrega')->nullable();
            $table->string('Entrega_Externa')->nullable();
            $table->bigInteger('Autorizacion')->nullable();
            $table->integer('Id_Dispensacion_Pendientes')->nullable();
            $table->integer('Id_Auditoria')->nullable();
            $table->bigInteger('Id_Positiva_Data')->unsigned()->nullable();
            $table->string('Doctor_Que_Formula')->nullable();

            $table->index('Id_Positiva_Data', 'Idx_Id_Positiva_Data');
        });
    }

    public function down()
    {
        Schema::table('Dispensacion', function (Blueprint $table) {
            $table->dropColumn([
                'Fecha_Reportar',
                'Estado_Acta',
                'Codigo_Radicacion',
                'Estado_Alistamiento',
                'Id_Dispositivo_Radicacion',
                'Paciente_Reclamante',
                'Tipo_Entrega',
                'Id_Punto_Dispensacion_Entrega',
                'Entrega_Externa',
                'Autorizacion',
                'Id_Dispensacion_Pendientes',
                'Id_Auditoria',
                'Id_Positiva_Data',
                'Doctor_Que_Formula',
            ]);
            $table->dropIndex('Idx_Id_Positiva_Data');
        });
    }
};
