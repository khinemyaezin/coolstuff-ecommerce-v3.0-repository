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
        Schema::create('variant_option_units', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->smallInteger('status')->default(RowStatus::NORMAL->value);
            $table->smallInteger('biz_status')->default(BizStatus::ACTIVE->value);
            $table->text('code');
            $table->text('title');
            $table->bigInteger('fk_varopt_hdr_id');
            $table->foreign('fk_varopt_hdr_id')->references('id')->on('variant_option_hdrs');
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
        Schema::dropIfExists('variant_option_units');
    }
};
