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
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->smallInteger('status')->default(RowStatus::NORMAL->value);
            $table->smallInteger('biz_status')->default(BizStatus::ACTIVE->value);
            $table->string("transaction_id",500)->unique();
            $table->string("transaction_ref",500)->unique();
            $table->bigInteger("fk_customer_id")->nullable();
            $table->string("ship_phone",50)->nullable();
            $table->string("ship_email",200)->nullable();
            $table->string("shipping_address",500);
            $table->double("total_price")->default(0.0);
            $table->bigInteger("fk_payment_id");
            $table->foreign('fk_customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('fk_payment_id')->references('id')->on('payments')->onDelete('cascade');
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
        Schema::dropIfExists('orders');
    }
};
