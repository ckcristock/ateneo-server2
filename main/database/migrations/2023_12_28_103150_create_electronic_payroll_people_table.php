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
        Schema::create('electronic_payroll_people', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained();
            $table->foreignId('person_payroll_payment_id')->constrained();
            $table->string('cune', 500)->default('0');
            $table->date('report_date')->nullable();
            $table->string('status', 20)->nullable();
            $table->json('dian_response', 500)->nullable();
            $table->string('payroll_code', 200);
            $table->text('observation', 300)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('electronic_payroll_people');
    }
};
