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
        Schema::create('files_in_brands', function (Blueprint $table) {
            $table->primary(['fk_brand_id', 'fk_file_id']);
            $table->bigInteger('fk_brand_id');
            $table->bigInteger('fk_file_id');
            $table->foreign('fk_brand_id')->references('id')->on('brands')->onDelete('cascade');
            $table->foreign('fk_file_id')->references('id')->on('files')->onDelete('cascade');
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
        Schema::dropIfExists('files_in_brands');
    }
};
