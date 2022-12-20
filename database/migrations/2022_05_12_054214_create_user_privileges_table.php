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
        Schema::create('user_privileges', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->smallInteger('status')->default(RowStatus::NORMAL->value);
            $table->smallInteger('biz_status')->default(BizStatus::ACTIVE->value);
            $table->text('title');
            $table->bigInteger('fk_user_id');
            $table->bigInteger('fk_role_id');
            $table->unique(['fk_user_id','fk_role_id']);
            $table->foreign('fk_user_id')->references('id')->on('users');
            $table->foreign('fk_role_id')->references('id')->on('roles');
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
        Schema::dropIfExists('user_privileges');
    }
};
