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
        Schema::table('retention_types', function (Blueprint $table) {
            $table->unsignedBigInteger('account_plan_id')->nullable()->after('type');
            $table->foreign('account_plan_id')->references('Id_Plan_Cuentas')->on('Plan_Cuentas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('retention_types', function (Blueprint $table) {
            $table->dropColumn('account_plan_id');
        });
    }
};
