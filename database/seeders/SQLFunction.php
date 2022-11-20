<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SQLFunction extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Store Category Function.
        $sql = "CREATE OR REPLACE FUNCTION store_category(myid integer, title text) RETURNS integer AS $$
                	declare mylft integer;
                	declare resultcount integer;
                       BEGIN
                			select lft into mylft from categories where id = myid;
                  			update categories set rgt = (rgt+2) where rgt > mylft;
                  			update categories set lft = lft +2 where lft > mylft;
                                      			insert into categories (title,lft,rgt,created_at,updated_at) values (
                  				title, mylft+1, mylft+2, now(), now()
                  			);
                			SELECT currval('categories_id_seq') into resultcount;
                           
                			with fullNode as (
                				SELECT node.id,node.title,array_to_string( array_remove(array_agg (parent.title ORDER BY parent.lft),'root'), ', ' ) as path,
                				(COUNT(parent.title) - 1) depth,node.lft,node.rgt
                				FROM categories AS node, categories AS parent
                				WHERE node.lft BETWEEN parent.lft AND parent.rgt 
                				and node.id = resultcount
                				GROUP BY node.id,node.title,node.lft
                				ORDER BY node.lft
                			) update categories set full_path = fullNode.path from fullNode where fullNode.id=categories.id;
                 			return resultcount;
                               
                       END;
                $$ LANGUAGE plpgsql;";
        DB::unprepared($sql);

        // --FUNCTON CREATE CATEGORY TRIGGER FUNCTON 
        $sql = "CREATE OR REPLACE FUNCTION categories_tsvector_trigger() RETURNS trigger as $$
                    begin
                        new.ts_path_search := setweight(to_tsvector('english', coalesce(new.title, '')), 'A') ||
                        setweight(to_tsvector('english', coalesce(new.full_path, '')), 'B');
                        return new;
                    end
                $$ LANGUAGE plpgsql;";
        DB::unprepared($sql);

        $sql = "DROP TRIGGER if exists categories_tsvector_update on categories cascade;";
        DB::unprepared($sql);

        $sql = "CREATE TRIGGER categories_tsvector_update BEFORE INSERT OR UPDATE
                ON categories FOR EACH ROW EXECUTE PROCEDURE categories_tsvector_trigger();";
        DB::unprepared($sql);

        /** Remove row from Category */
        $sql = "CREATE OR REPLACE FUNCTION delete_category(myid integer) RETURNS void AS $$
                declare mylft integer;
                declare myrgt integer;
                declare mydepth integer;
                declare resultcount integer;
                BEGIN
                    SELECT lft INTO mylft FROM categories WHERE id = myid;
                    SELECT lft,rgt,(rgt - lft + 1) INTO mylft,myrgt,mydepth FROM categories WHERE id = myid;
                    DELETE FROM categories WHERE lft BETWEEN mylft AND myrgt;
                            
                    update categories SET rgt = rgt - mydepth WHERE rgt > myrgt;
                    UPDATE categories SET lft = lft - mydepth WHERE lft > mylft;
                            
                END;
            $$ LANGUAGE plpgsql;";
        DB::unprepared($sql);

        // Get Categories By Depth.
        $sql = "CREATE OR REPLACE FUNCTION store_category_leaf(in positionNumber integer) RETURNS void AS $$
                    BEGIN
                            delete from category_leaves;
                            with leafNode as (
                                SELECT node.id,node.title,
                                array_to_string(array_agg (parent.title ORDER BY parent.lft),'/') as path,
                                (array_agg (parent.id ORDER BY parent.lft))[positionNumber] as parent_id,
                                (COUNT(parent.title) - 1) depth,node.lft,node.rgt
                                FROM categories AS node, categories AS parent
                                WHERE node.lft BETWEEN parent.lft AND parent.rgt
                                GROUP BY node.id,node.title,node.lft
                                HAVING node.lft+1=node.rgt
                                ORDER BY node.lft
                            )
                            insert into category_leaves (id,title,path,level_category_id,depth,lft,rgt)
                            select id,title,path,parent_id,depth,lft,rgt from leafNode;
                    END;
                $$ LANGUAGE plpgsql;";
        DB::unprepared($sql);

        $sql = "CREATE OR REPLACE FUNCTION classify_options(productId bigint,option1Id bigint,option2Id bigint,option3Id bigint) RETURNS 
                table(
                    fk_varopt_hdr_id bigint,
                    fk_varopt_hdr_title text,
                    fk_varopt_dtl_id bigint,
                    fk_varopt_dtl_title text,
                    fk_prod_id bigint,
                    var_title text,
                    option_type integer
                    )
                AS $$
                        BEGIN
                                RETURN QUERY 
                                with variant as (select * from prod_variants where prod_variants.fk_prod_id=productId) 
                                (select distinct on (variant.fk_varopt_1_dtl_id,variant.var_1_title)
                                 ophdr.id fk_varopt_hdr_id,ophdr.title fk_varopt_hdr_title,opdtl.id fk_varopt_dtl_id,opdtl.title fk_varopt_dtl_title, 
                                variant.fk_prod_id,variant.var_1_title as var_title,1 as option_type 
                            	from variant 
                                inner join variant_option_hdrs ophdr on ophdr.id=variant.fk_varopt_1_hdr_id 
                                left join variant_option_dtls opdtl on variant.fk_varopt_1_dtl_id = opdtl.id
                                where variant.fk_varopt_1_hdr_id = option1Id
                                order by variant.var_1_title,variant.fk_varopt_1_dtl_id,variant.fk_varopt_2_dtl_id,variant.fk_varopt_3_dtl_id) 
                                union all
                                ( select distinct on (variant.fk_varopt_2_dtl_id,variant.var_2_title)
                                ophdr.id fk_varopt_hdr_id,ophdr.title fk_varopt_hdr_title,opdtl.id fk_varopt_dtl_id,opdtl.title fk_varopt_dtl_title,
                                variant.fk_prod_id,variant.var_2_title as var_title,2 as option_type from variant 
                                inner join variant_option_hdrs ophdr on ophdr.id=variant.fk_varopt_2_hdr_id 
                                left join variant_option_dtls opdtl on variant.fk_varopt_2_dtl_id = opdtl.id
                                where variant.fk_varopt_2_hdr_id = option2Id
                                order by variant.var_2_title,variant.fk_varopt_2_dtl_id,variant.fk_varopt_3_dtl_id) 
                                union all
                                ( select distinct on (variant.fk_varopt_3_dtl_id,variant.var_3_title)
                                ophdr.id fk_varopt_hdr_id,ophdr.title fk_varopt_hdr_title,opdtl.id fk_varopt_dtl_id,opdtl.title fk_varopt_dtl_title,
                                variant.fk_prod_id,variant.var_3_title as var_title,3 as option_type from variant 
                                inner join variant_option_hdrs ophdr on ophdr.id=variant.fk_varopt_3_hdr_id 
                                left join variant_option_dtls opdtl on variant.fk_varopt_3_dtl_id = opdtl.id
                                where variant.fk_varopt_3_hdr_id=option3Id);
                        END;
                $$ LANGUAGE plpgsql;";
        DB::unprepared($sql);

        $sql = "CREATE OR REPLACE FUNCTION get_product_attributes(lvlCategoryId bigint,variantId bigint,productId bigint) RETURNS 
                table(
                    id bigint,
                    fk_varopt_hdr_id bigint,
                    title text,
                    allow_dtls_custom_name boolean,
                    need_dtls_mapping boolean,
                    fk_varopt_dtl_id bigint,
                    fk_varopt_dtl_title text,
                    fk_varopt_unit_id bigint,
                    fk_varopt_unit_title text,
                    attri_value text
                    )
                AS $$
                        BEGIN
                                RETURN QUERY 
                                with optionGroup as (                                                              
                                    select                                                                           
                                    opthdr.id as option_id,                                                          
                                    opthdr.title as option_name,                                                      
                                    opthdr.allow_dtls_custom_name as allow_dtls_custom_name,                           
                                    opthdr.need_dtls_mapping as need_dtls_mapping                            
                                    from  category_attributes catvar                                                  
                                    inner join variant_option_hdrs opthdr on opthdr.id= catvar.fk_varoption_hdr_id 
                                    where catvar.fk_category_id=lvlCategoryId                                                                                                                 
                                ),attri as (                                                                       
                                    select                                                                           
                                      attri.id as id,                                                                  
                                    attri.fk_prod_id as prod_id,                                                       
                                    attri.fk_variant_id as var_id,                                                      
                                    attri.fk_varopt_hdr_id ,                        
                                    attri.fk_varopt_dtl_id,
                                    details.title as fk_varopt_dtl_title,
                                    attri.fk_varopt_unit_id,      
                                    units.title as fk_varopt_unit_title,
                                    attri.value as attri_value                                                       
                                    from prod_attributes attri
                                    left join variant_option_dtls details on details.id=attri.fk_varopt_dtl_id
                                    left join variant_option_units units on units.id=attri.fk_varopt_unit_id
                                    where attri.fk_prod_id=productId                                                        
                                    and attri.fk_variant_id=variantId                                     
                                )                                                                                  
                                 select 
                                 attri.id,option_id as fk_varopt_hdr_id,
                                 option_name as title,
                                 optionGroup.allow_dtls_custom_name,
                                 optionGroup.need_dtls_mapping,
                                 attri.fk_varopt_dtl_id,
                                 attri.fk_varopt_dtl_title,
                                 attri.fk_varopt_unit_id,
                                 attri.fk_varopt_unit_title,
                                 attri.attri_value  
                                 from attri                                                               
                                 right join optionGroup on attri.fk_varopt_hdr_id= optionGroup.option_id;   
                        END;
            $$ LANGUAGE plpgsql;";
        DB::unprepared($sql);

        // // --FUNCTON CREATE VARIANTS TRIGGER FUNCTON 
        // $sql = "CREATE OR REPLACE FUNCTION prodvariant_tsvector_trigger() RETURNS trigger as $$
        //             begin
        //                 new.ts_search := setweight(to_tsvector('english', coalesce(new.title, '')), 'A') ||
        //                 setweight(to_tsvector('english', coalesce(new.full_path, '')), 'B');
        //                 return new;
        //             end
        //         $$ LANGUAGE plpgsql;";
        // DB::unprepared($sql);

        // $sql = "DROP TRIGGER if exists categories_tsvector_update on categories cascade;";
        // DB::unprepared($sql);

        // $sql = "CREATE TRIGGER categories_tsvector_update BEFORE INSERT OR UPDATE
        //         ON categories FOR EACH ROW EXECUTE PROCEDURE categories_tsvector_trigger();";
        // DB::unprepared($sql);
    }
}
