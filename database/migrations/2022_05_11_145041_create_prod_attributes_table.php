<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prod_attributes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('fk_prod_id');
            $table->bigInteger('fk_varopt_hdr_id');
            $table->bigInteger('fk_variant_id');
            $table->text('value');
            $table->bigInteger('fk_varopt_dtl_id')->nullable();
            $table->bigInteger('fk_varopt_unit_id')->nullable();
            $table->foreign('fk_prod_id')->references('id')->on('products');
            $table->foreign('fk_varopt_hdr_id')->references('id')->on('variant_option_hdrs');
            $table->foreign('fk_variant_id')->references('id')->on('prod_variants')->onDelete('cascade');
            $table->foreign('fk_varopt_dtl_id')->references('id')->on('variant_option_dtls');
            $table->foreign('fk_varopt_unit_id')->references('id')->on('variant_option_units');
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
        Schema::dropIfExists('prod_attributes');
    }
};
