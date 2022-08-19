<?php

use App\Services\Utility;
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
        Schema::create('variant_option_hdrs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->smallInteger('status')->default(Utility::$BIZ_STATUS['active']);
            $table->smallInteger('biz_status')->default(Utility::$ROW_STATUS['normal']);
            $table->text('title');
            $table->boolean('allow_dtls_custom_name')->default(false);
            $table->boolean('need_dtls_mapping')->default(false);
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
        Schema::dropIfExists('variant_option_hdrs');
    }
};
