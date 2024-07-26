<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductApplicationCertificateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_application_certificate', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('application_certificate_id', 191);
            $table->string('product_id', 191)->nullable();
            $table->string('amount', 191)->nullable();
            $table->string('lote', 191)->nullable();
            $table->string('file1', 500)->nullable();
            $table->string('file2', 500)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_application_certificate');
    }
}
