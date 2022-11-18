<?php

namespace App\Daos;

class ProductsDao {
    public function getSingleProducts()
    {
        $distinctProduct = 'distinct on (products.id) ';
        $distinctVariant = 'distinct on (prod_variants.id)';
        $sql = `
        with warehouse as (
            select prodloc.fk_prod_variant_id as variant_id,prodloc.quantity as variant_qty,
            loc.id as locid
            from prod_locations prodloc
            inner join locations as loc on  prodloc.fk_location_id = loc.id and loc.default = true
            order by variant_id
        )
        select 
            distinct on (products.id)
                        products.id as product_id,
                        products.biz_status as product_biz_status,
                        products.title as product_title,
                        products.brand as product_brand,
                        products.manufacture as product_manufacture,
                        option1.id as option1_id,
                        option1.title as option1_title,
                        option2.id as option2_id,
                        option2.title as option2_title,
                        option3.id as option3_id,
                        option3.title as option3_title,
        
                        regions.id as currency_id,
                        regions.currency_code as currency_currency_code,
                        prod_variants.id as variant_id,
                        prod_variants.biz_status as variant_biz_status,
                        prod_variants.seller_sku as variant_seller_sku,
                        prod_variants.fk_varopt_1_hdr_id as variant_fk_varopt_1_hdr_id,
                        prod_variants.fk_varopt_1_dtl_id as variant_fk_varopt_1_dtl_id,
                        prod_variants.var_1_title as variant_var_1_title,
                        prod_variants.fk_varopt_2_hdr_id as variant_fk_varopt_2_hdr_id,
                        prod_variants.fk_varopt_2_dtl_id as variant_fk_varopt_2_dtl_id,
                        prod_variants.var_2_title as variant_var_2_title,
                        prod_variants.fk_varopt_3_hdr_id as variant_fk_varopt_3_hdr_id,
                        prod_variants.fk_varopt_3_dtl_id as variant_fk_varopt_3_dtl_id,
                        prod_variants.var_3_title as variant_var_3_title,
                        prod_variants.buy_price as variant_buy_price,
                        prod_variants.selling_price as variant_selling_price,
                           warehouse.variant_qty as variant_quantity,
                        conditions.id as condition_id,
                        conditions.title as condition_title,
                        prod_variants.start_at as variant_start_at,
                        prod_variants.expired_at as variant_expired_at,
        
                        file_1.id as   variant_media_1_image_id,
                        file_1.path as variant_media_1_image_path
            from products 
            inner join prod_variants on products.id = prod_variants.fk_prod_id 
            inner join regions on regions.id = products.fk_currency_id 
            inner join conditions on prod_variants.fk_condition_id = conditions.id 
            left join variant_option_hdrs as option1 on products.fk_varopt_1_hdr_id = option1.id 
            left join variant_option_hdrs as option2 on products.fk_varopt_2_hdr_id = option2.id 
            left join variant_option_hdrs as option3 on products.fk_varopt_3_hdr_id = option3.id 
            left join files as file_1 on prod_variants.media_1_image = file_1.id 
            left join warehouse  on warehouse.variant_id = prod_variants.id
            where products.fk_brand_id = 1 
            and products.id = 15
            order by products.id,prod_variants.id
        
        `;
    }
}