<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('Positiva_Data', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('numeroAutorizacion')->nullable();
            $table->tinyInteger('Anulado')->nullable();
            $table->dateTime('fechaAnulado')->nullable();
            $table->dateTime('fechaHoraAutorizacion')->nullable();
            $table->date('fechaVencimiento')->nullable();
            $table->integer('tieneTutela')->nullable();
            $table->longText('serviciosAutorizados')->nullable();
            $table->longText('diagnosticos')->nullable();
            $table->string('PtipoDocumento', 50)->nullable();
            $table->string('PnumeroDocumento', 255)->nullable();
            $table->bigInteger('PcodigoHabilitacion')->nullable();
            $table->string('PrazonSocial', 255)->nullable();
            $table->string('Pdepartamento', 255)->nullable();
            $table->string('PcodigoDepartamento', 255)->nullable();
            $table->string('Pmunicipio', 255)->nullable();
            $table->string('PcodigoMunicipio', 255)->nullable();
            $table->string('Psede', 255)->nullable();
            $table->integer('Pdomicilio')->nullable();
            $table->string('Pdireccion', 255)->nullable();
            $table->integer('PindicativoTelefono')->nullable();
            $table->string('PnumeroTelefono', 100)->nullable();
            $table->string('AFtipoDocumento', 200)->nullable();
            $table->string('AFnumeroDocumento', 200)->nullable();
            $table->string('AFprimerNombre', 200)->nullable();
            $table->string('AFsegundoNombre', 200)->nullable();
            $table->string('AFprimerApellido', 200)->nullable();
            $table->string('AFsegundoApellido', 200)->nullable();
            $table->date('AFfechaNacimiento')->nullable();
            $table->string('AFdepartamento', 255)->nullable();
            $table->string('AFcodigoDepartamento', 255)->nullable();
            $table->string('AFmunicipio', 255)->nullable();
            $table->string('AFcodigoMunicipio', 255)->nullable();
            $table->string('AFzona', 255)->nullable();
            $table->string('AFlocalidad', 255)->nullable();
            $table->string('AFdireccionResidencial', 255)->nullable();
            $table->string('AFcorroElectronico', 255)->nullable();
            $table->integer('AFtelefonoFijoParticularIndicativo')->nullable();
            $table->integer('AFtelefonoFijoParticularNumero')->nullable();
            $table->bigInteger('AFtelefonoCelularParticularNumero')->nullable();
            $table->string('AFcodigoCoberturaSalud', 255)->nullable();
            $table->string('AFramo', 255)->nullable();
            $table->string('RLtipoDocumento', 255)->nullable();
            $table->string('RLnumeroDocumento', 255)->nullable();
            $table->string('RLrazonSocial', 255)->nullable();
            $table->date('RLfechaVinculacion')->nullable();
            $table->string('RLestadoRelacionLaboral', 255)->nullable();
            $table->string('RLmarcaEmpleador', 255)->nullable();
            $table->string('RLnumeroSolicitudSiniestro', 255)->nullable();
            $table->string('RLnumeroSiniestro', 255)->nullable();
            $table->string('PAnombre', 255)->nullable();
            $table->string('PAcargoActividad')->nullable();
            $table->integer('PAtelefonoContactoUno')->nullable();
            $table->integer('PAtelefonoContactoDos')->nullable();
            $table->dateTime('FechaCreacion')->default(now());
            $table->integer('usuarioPositiva')->nullable();
            $table->enum('Estado', ['Registrado', 'En Dispensacion', 'Pendiente', 'Anulada'])->default('Registrado');
            $table->string('Detalle_Estado', 2000)->nullable();
            $table->bigInteger('Id_Dispensacion')->nullable();
            $table->longText('Request')->nullable();
            $table->longText('requestAnulacion')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Positiva_Data');
    }
};
