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
        Schema::create('travel_expense_air_transports', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['Ida', 'Vuelta']);
            $table->string('journey', 191);
            $table->string('company', 191);
            $table->enum('ticket_payment', ['Agencia', 'Viajero']);
            $table->date('departure_date');
            $table->integer('ticket_value');
            $table->foreignId('travel_expense_id')->constrained();
            $table->double('total', 50, 2)->nullable();
            $table->timestamps();
        });

        Schema::table('travel_expenses', function (Blueprint $table) {
            $table->double('total_air_transport_cop', 50, 2)->nullable()->after('total_transports_cop');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_expense_air_transports');
    }
};
