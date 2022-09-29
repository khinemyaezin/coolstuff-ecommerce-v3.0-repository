<?php

use App\Enums\BizStatus;
use App\Enums\RowStatus;
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
        Schema::create('locations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->smallInteger('status')->default(BizStatus::ACTIVE->value);
            $table->smallInteger('biz_status')->default(RowStatus::NORMAL->value);
            $table->text('title');
            $table->bigInteger('fk_brand_id');
            $table->boolean('default')->default(true);
            $table->bigInteger('fk_region_id');
            $table->text('address')->nullable();
            $table->string('apartment',200)->nullable();
            $table->string('phone',100)->nullable();
            $table->foreign('fk_brand_id')->references('id')->on('brands')->onDelete('cascade');
            $table->foreign('fk_region_id')->references('id')->on('regions')->nullOnDelete('cascade');
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
        Schema::dropIfExists('locations');
    }
};
