<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('Reclamante', function (Blueprint $table) {
            $table->bigIncrements('Id_Reclamante');
            $table->string('Nombre', 200)->nullable();
            $table->date('Fecha_Nacimiento')->nullable();
            $table->timestamp('Fecha_Primer_Reclamo')->useCurrent();
            $table->string('Correo', 250)->nullable();
            $table->string('Telefono', 250)->nullable();
            $table->string('Parentesco', 50)->nullable();
            $table->string('Direccion', 50)->nullable();
            $table->timestamps(); 
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reclamante');
    }
};
