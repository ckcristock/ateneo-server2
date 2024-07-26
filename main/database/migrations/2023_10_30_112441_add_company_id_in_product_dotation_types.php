<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompanyIdInProductDotationTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_dotation_types', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_dotation_types', function (Blueprint $table) {
            $table->dropColumn('company_id');
            $table->dropColumn('deleted_at');
        });
    }
}
