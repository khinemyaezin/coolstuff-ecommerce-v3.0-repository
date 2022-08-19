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
        Schema::create('nrc_states', function (Blueprint $table) {
            $table->string('id',100)->primary();
            $table->smallInteger('status')->default(Utility::$BIZ_STATUS['active']);
            $table->smallInteger('biz_status')->default(Utility::$ROW_STATUS['normal']);
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
        Schema::dropIfExists('nrc_states');
    }
};
