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
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->smallInteger('status')->default(Utility::$BIZ_STATUS['active']);
            $table->smallInteger('biz_status')->default(Utility::$ROW_STATUS['normal']);
            $table->string('first_name');
            $table->string('last_name');
            $table->string('fk_nrc_state_id',100)->nullable();
            $table->string('fk_nrc_district_id',100)->nullable();
            $table->string('fk_nrc_nation_id',100)->nullable();
            $table->foreign('fk_nrc_state_id')->references('id')->on('nrc_states');
            $table->foreign('fk_nrc_district_id')->references('id')->on('nrc_districts');
            $table->foreign('fk_nrc_nation_id')->references('id')->on('nrc_nations');
            $table->string('nrc_value',6)->nullable();
            $table->bigInteger('fk_usertype_id');
            $table->foreign('fk_usertype_id')->references('id')->on('user_types');
            $table->bigInteger('fk_brand_id')->nullable();
            $table->foreign('fk_brand_id')->references('id')->on('brands');
            $table->text('image_url')->nullable();
            $table->string('email',200);
            $table->timestamp('email_verify_at')->nullable();
            $table->string('phone',200)->nullable();
            $table->text('address')->nullable();
            $table->text('password')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
};
