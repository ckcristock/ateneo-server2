<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubcategoriaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Subcategoria', function (Blueprint $table) {
            $table->bigIncrements('Id_Subcategoria');
            $table->integer('Id_Categoria_Nueva')->nullable();
            $table->integer('company_id')->nullable();
            $table->string('Nombre', 100);
            $table->enum('Separable', ['si', 'no'])->default('No');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Subcategoria');
    }
}
