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
        Schema::create('brands', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('public_id',10);
            $table->string('title',200);
            $table->string('description',300)->nullable();
            $table->smallInteger('status')->default(RowStatus::NORMAL->value);
            $table->smallInteger('biz_status')->default(BizStatus::ACTIVE->value);
            $table->bigInteger('profile_image')->nullable();
            $table->bigInteger('cover_image')->nullable();
            $table->bigInteger('fk_region_id');

            //Settings
            $table->bigInteger('def_currency_id')->nullable();
            $table->bigInteger('industry_id')->nullable();
            $table->string('phone',20)->nullable();
            $table->string('sys_email',100)->nullable();
            $table->string('cus_email',100)->nullable();

            $table->foreign('def_currency_id')->references('id')->on('regions');
            $table->foreign('industry_id')->references('id')->on('categories');
            $table->foreign('fk_region_id')->references('id')->on('regions');
            $table->foreign('profile_image')->references('id')->on('files')->cascadeOnUpdate()->nullOnDelete();
            $table->foreign('cover_image')->references('id')->on('files')->cascadeOnUpdate()->nullOnDelete();
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
        Schema::dropIfExists('brands');
    }
};
