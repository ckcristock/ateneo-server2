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
        Schema::create('account_configurations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('configurable_id');
            $table->string('configurable_type');
            $table->foreignId('retention_type_id')->nullable()->constrained();
            $table->unsignedBigInteger('income_account')->nullable();
            $table->unsignedBigInteger('inventory_account')->nullable();
            $table->unsignedBigInteger('expense_account')->nullable();
            $table->unsignedBigInteger('cost_account')->nullable();
            $table->unsignedBigInteger('entry_account')->nullable();
            $table->unsignedBigInteger('sale_iva_account')->nullable();
            $table->unsignedBigInteger('purchase_iva_account')->nullable();
            $table->unsignedBigInteger('sale_discount_account')->nullable();
            $table->unsignedBigInteger('purchase_discount_account')->nullable();
            $table->unsignedBigInteger('retefuente_sale_account')->nullable();
            $table->unsignedBigInteger('retefuente_purchase_account')->nullable();
            $table->double('retefuente_percentage')->nullable();
            $table->unsignedBigInteger('reteica_sale_account')->nullable();
            $table->unsignedBigInteger('reteica_purchase_account')->nullable();
            $table->double('reteica_percentage')->nullable();
            $table->unsignedBigInteger('reteiva_sale_account')->nullable();
            $table->unsignedBigInteger('reteiva_purchase_account')->nullable();
            $table->double('reteiva_percentage')->nullable();
            $table->timestamps();

            $table->foreign('income_account')->references('Id_Plan_Cuentas')->on('Plan_Cuentas');
            $table->foreign('inventory_account')->references('Id_Plan_Cuentas')->on('Plan_Cuentas');
            $table->foreign('expense_account')->references('Id_Plan_Cuentas')->on('Plan_Cuentas');
            $table->foreign('cost_account')->references('Id_Plan_Cuentas')->on('Plan_Cuentas');
            $table->foreign('entry_account')->references('Id_Plan_Cuentas')->on('Plan_Cuentas');
            $table->foreign('sale_iva_account')->references('Id_Plan_Cuentas')->on('Plan_Cuentas');
            $table->foreign('purchase_iva_account')->references('Id_Plan_Cuentas')->on('Plan_Cuentas');
            $table->foreign('sale_discount_account')->references('Id_Plan_Cuentas')->on('Plan_Cuentas');
            $table->foreign('purchase_discount_account')->references('Id_Plan_Cuentas')->on('Plan_Cuentas');
            $table->foreign('retefuente_sale_account')->references('Id_Plan_Cuentas')->on('Plan_Cuentas');
            $table->foreign('retefuente_purchase_account')->references('Id_Plan_Cuentas')->on('Plan_Cuentas');
            $table->foreign('reteica_sale_account')->references('Id_Plan_Cuentas')->on('Plan_Cuentas');
            $table->foreign('reteica_purchase_account')->references('Id_Plan_Cuentas')->on('Plan_Cuentas');
            $table->foreign('reteiva_sale_account')->references('Id_Plan_Cuentas')->on('Plan_Cuentas');
            $table->foreign('reteiva_purchase_account')->references('Id_Plan_Cuentas')->on('Plan_Cuentas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_configurations');
    }
};
