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
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->smallInteger('status')->default(BizStatus::ACTIVE->value);
            $table->smallInteger('biz_status')->default(RowStatus::NORMAL->value);
            $table->text('title');
            $table->text('brand');
            $table->text('manufacture');
            $table->integer('package_qty')->default(0);
            $table->bigInteger('fk_brand_id');
            $table->bigInteger('fk_category_id');
            $table->bigInteger('fk_lvlcategory_id');
            $table->bigInteger('fk_packtype_id');
            $table->bigInteger('fk_prod_group_id')->nullable();
            $table->bigInteger('fk_currency_id');
            $table->bigInteger('fk_purchased_currency_id');
            $table->bigInteger('fk_varopt_1_hdr_id')->nullable();
            $table->bigInteger('fk_varopt_2_hdr_id')->nullable();
            $table->bigInteger('fk_varopt_3_hdr_id')->nullable();
            $table->foreign('fk_brand_id')->references('id')->on('brands');
            $table->foreign('fk_category_id')->references('id')->on('categories');
            $table->foreign('fk_lvlcategory_id')->references('id')->on('categories');
            $table->foreign('fk_packtype_id')->references('id')->on('pack_types');
            $table->foreign('fk_prod_group_id')->references('id')->on('prod_groups');
            $table->foreign('fk_currency_id')->references('id')->on('regions');
            $table->foreign('fk_purchased_currency_id')->references('id')->on('regions');
            $table->foreign('fk_varopt_1_hdr_id')->references('id')->on('variant_option_hdrs');
            $table->foreign('fk_varopt_2_hdr_id')->references('id')->on('variant_option_hdrs');
            $table->foreign('fk_varopt_3_hdr_id')->references('id')->on('variant_option_hdrs');

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
        Schema::dropIfExists('products');
    }
};
