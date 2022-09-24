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
        Schema::create('category_leaves', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->smallInteger('status')->default(BizStatus::ACTIVE->value);
            $table->smallInteger('biz_status')->default(RowStatus::NORMAL->value);
            $table->text('title');
            $table->integer('depth');
            $table->text('path');
            $table->integer('lft');
            $table->integer('rgt');
            $table->bigInteger('level_category_id')->nullable();
            $table->foreign('id')->references('id')->on('categories');
            $table->foreign('level_category_id')->references('id')->on('categories');
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
        Schema::dropIfExists('category_leaves');
    }
};
