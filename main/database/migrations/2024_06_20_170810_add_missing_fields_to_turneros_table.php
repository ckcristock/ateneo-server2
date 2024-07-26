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
        Schema::table('Turneros', function (Blueprint $table) {
            $table->bigInteger('Identificacion_Persona')->nullable()->after('Id_Turneros');
            $table->string('Persona', 200)->nullable()->after('Identificacion_Persona');
            $table->date('Fecha')->nullable()->after('Persona');
            $table->time('Hora_Turno')->nullable()->after('Fecha');
            $table->time('Hora_Inicio_Atencion')->default('00:00:00')->after('Hora_Turno');
            $table->time('Hora_Fin_Atencion')->default('00:00:00')->after('Hora_Inicio_Atencion');
            $table->string('Estado', 200)->default('Espera')->after('Hora_Fin_Atencion');
            $table->integer('Orden')->nullable()->after('Estado');
            $table->string('Caja', 50)->nullable()->after('Orden');
            $table->string('Tipo', 100)->nullable()->after('Caja');
            $table->integer('Id_Auditoria')->nullable()->after('Tipo');
            $table->string('Tag', 100)->nullable()->after('Id_Auditoria');
            $table->integer('Prioridad')->default(4)->after('Tag');
            $table->integer('Id_Prioridad_Turnero')->default(0)->after('Prioridad');
            $table->integer('Id_Dispensacion_Mipres')->default(0)->after('Id_Prioridad_Turnero');
            $table->string('Tipo_Turno', 100)->default('Dispensacion')->after('Id_Dispensacion_Mipres');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('Turneros', function (Blueprint $table) {
            $table->dropColumn([
                'Identificacion_Persona',
                'Persona',
                'Fecha',
                'Hora_Turno',
                'Hora_Inicio_Atencion',
                'Hora_Fin_Atencion',
                'Estado',
                'Orden',
                'Caja',
                'Tipo',
                'Id_Auditoria',
                'Tag',
                'Prioridad',
                'Id_Prioridad_Turnero',
                'Id_Dispensacion_Mipres',
                'Tipo_Turno'
            ]);
        });
    }
};
