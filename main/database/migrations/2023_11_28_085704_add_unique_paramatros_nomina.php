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
        Schema::table('pension_funds', function (Blueprint $table) {
            $table->dropColumn('nit');
            $table->dropColumn('code');
        });

        Schema::table('pension_funds', function (Blueprint $table) {
            $table->string('nit')->unique()->nullable()->after('name');
            $table->string('code')->unique()->nullable()->after('name');
        });

        Schema::table('severance_funds', function (Blueprint $table) {
            $table->dropColumn('nit');
        });

        Schema::table('severance_funds', function (Blueprint $table) {
            $table->string('nit')->unique()->nullable()->after('name');
            $table->string('code')->unique()->nullable()->after('name');
        });

        Schema::table('eps', function (Blueprint $table) {
            $table->dropColumn('nit');
            $table->dropColumn('code');
        });

        Schema::table('eps', function (Blueprint $table) {
            $table->string('nit')->unique()->nullable()->after('name');
            $table->string('code')->unique()->nullable()->after('name');
        });

        Schema::table('compensation_funds', function (Blueprint $table) {
            $table->dropColumn('nit');
            $table->dropColumn('code');
        });

        Schema::table('compensation_funds', function (Blueprint $table) {
            $table->string('nit')->unique()->nullable()->after('name');
            $table->string('code')->unique()->nullable()->after('name');
        });

        Schema::table('banks', function (Blueprint $table) {
            $table->dropColumn('code');
        });

        Schema::table('banks', function (Blueprint $table) {
            $table->string('nit')->unique()->nullable()->after('name');
            $table->string('code')->unique()->nullable()->after('name');
        });

        Schema::table('arl', function (Blueprint $table) {
            $table->dropColumn('accounting_account');
            $table->string('code')->unique()->nullable()->after('nit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
