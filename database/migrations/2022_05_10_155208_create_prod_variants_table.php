<?php

use App\Enums\BizStatus;
use App\Enums\RowStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::create('prod_variants', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->smallInteger('status')->default(BizStatus::ACTIVE->value);
            $table->smallInteger('biz_status')->default(RowStatus::NORMAL->value);
            $table->string('seller_sku', 50);
            $table->bigInteger('fk_prod_id');
            $table->bigInteger('fk_varopt_1_hdr_id')->nullable();
            $table->bigInteger('fk_varopt_1_dtl_id')->nullable();
            $table->text('var_1_title')->nullable();
            $table->bigInteger('fk_varopt_2_hdr_id')->nullable();
            $table->bigInteger('fk_varopt_2_dtl_id')->nullable();
            $table->text('var_2_title')->nullable();
            $table->bigInteger('fk_varopt_3_hdr_id')->nullable();
            $table->bigInteger('fk_varopt_3_dtl_id')->nullable();
            $table->text('var_3_title')->nullable();
            $table->double('buy_price')->default(0.0);
            $table->double('selling_price')->default(0.0);
            $table->double('compared_price')->default(0.0);
            $table->integer('qty')->default(0);
            $table->boolean('track_qty')->default(true);
            $table->boolean('keep_selling_outofstock')->default(false);
            $table->bigInteger('fk_condition_id');
            $table->string('condition_desc')->nullable();
            $table->json('features')->nullable();
            $table->text('prod_desc')->nullable();
            $table->timestamp('start_at', 0)->nullable();
            $table->timestamp('expired_at', 0)->nullable();
            $table->bigInteger('media_1_image')->nullable();
            $table->bigInteger('media_2_image')->nullable();
            $table->bigInteger('media_3_image')->nullable();
            $table->bigInteger('media_4_image')->nullable();
            $table->bigInteger('media_5_image')->nullable();
            $table->bigInteger('media_6_image')->nullable();
            $table->bigInteger('media_7_image')->nullable();
            $table->bigInteger('media_8_video')->nullable();
            $table->bigInteger('media_9_video')->nullable();
            $table->foreign('fk_varopt_1_hdr_id')->references('id')->on('variant_option_hdrs');
            $table->foreign('fk_varopt_1_dtl_id')->references('id')->on('variant_option_dtls');
            $table->foreign('fk_varopt_2_hdr_id')->references('id')->on('variant_option_hdrs');
            $table->foreign('fk_varopt_2_dtl_id')->references('id')->on('variant_option_dtls');
            $table->foreign('fk_varopt_3_hdr_id')->references('id')->on('variant_option_hdrs');
            $table->foreign('fk_varopt_3_dtl_id')->references('id')->on('variant_option_dtls');
            $table->foreign('fk_condition_id')->references('id')->on('conditions');
            $table->foreign('fk_prod_id')->references('id')->on('products')->onDelete('cascade');

            foreach ( range(1,7) as $value) {
                $table->foreign('media_'.$value.'_image')->references('id')->on('files')->cascadeOnUpdate()->nullOnDelete();
            }

            $table->foreign('media_8_video')->references('id')->on('files')->cascadeOnUpdate()->nullOnDelete();
            $table->foreign('media_9_video')->references('id')->on('files')->cascadeOnUpdate()->nullOnDelete();
            $table->unique(['fk_prod_id', 'seller_sku']);

            $table->timestamps();
        });
       // DB::statement('ALTER TABLE prod_variants ADD column ts_search tsvector');

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prod_variants');
    }
};
