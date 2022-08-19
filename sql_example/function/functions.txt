--CREATE TABLE CATEGORY
create table categories (
	id serial primary key,
	title text not null,
	lft int not null,
	rgt int not null
)
insert into categories(title,lft,rgt) values ('root',1,2);

--FUNCTION ADD CATEGORY
CREATE OR REPLACE FUNCTION store_category(myid integer, title text) RETURNS integer AS $$
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
 				  return resultcount;

        END;
$$ LANGUAGE plpgsql;

--FUNCTION ADD CATEGORY_leaf
CREATE OR REPLACE FUNCTION store_category_leaf() RETURNS void AS $$
	
        BEGIN
				delete from category_leaves;
				drop table if exists fullnode;
				drop table if exists leafNode;
				CREATE TEMP TABLE fullnode (
   					id integer,title text default '',path text default '',depth integer,lft int,rgt int
				);
				CREATE TEMP TABLE leafNode (
   					id integer,title text default '',path text default '',depth integer,lft int,rgt int
				);
				INSERT INTO fullnode (id,title,path,depth,lft,rgt) 
					SELECT node.id,node.title,array_to_string(array_agg (parent.title ORDER BY parent.lft),'/') as path,
					(COUNT(parent.title) - 1) depth,node.lft,node.rgt
					FROM categories AS node, categories AS parent
					WHERE node.lft BETWEEN parent.lft AND parent.rgt
					GROUP BY node.id,node.title,node.lft
					ORDER BY node.lft;
				INSERT INTO leafNode (id,title,lft,rgt) 
					SELECT node1.id,node1.title,node1.lft,node1.rgt
					FROM categories AS node1, categories AS parent1
					WHERE node1.lft BETWEEN parent1.lft AND parent1.rgt and parent1.rgt = parent1.lft +1
					GROUP BY node1.id,node1.title,node1.lft
					ORDER BY node1.lft;
				insert into category_leaves (id,title_leaf,path,depth,lft,rgt)
				select fullnode.id,leafNode.title,fullnode.path,fullnode.depth,fullnode.lft,fullnode.rgt from  fullnode 
				inner join leafNode on leafNode.id=fullnode.id;
        END;
$$ LANGUAGE plpgsql;

--FUNCTION ADD CATEGORY_leaf with parent id
drop function store_category_leaf;
CREATE OR REPLACE FUNCTION store_category_leaf(in positionNumber integer) RETURNS void AS $$
	
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
$$ LANGUAGE plpgsql;

--FUNCTION GET CATEGORY BY DEPTH
drop function category_by_depth;
CREATE OR REPLACE FUNCTION category_by_depth(depth_count integer) RETURNS 
table(
	id bigint,
	title text,
	path text,
	depth bigint
	)
AS $$
        BEGIN
				RETURN QUERY SELECT  
						node.id,
						node.title,
						array_to_string(array_agg (parent.title ORDER BY parent.lft),'/') as path,
						(COUNT(parent.title) - 1) depth
					FROM categories AS node, categories AS parent
					WHERE node.lft BETWEEN parent.lft AND parent.rgt
					GROUP BY node.id,node.title,node.lft
					HAVING (COUNT(parent.title) - 1) = depth_count
					ORDER BY node.lft;
        END;
$$ LANGUAGE plpgsql;

--FUNCTION CLASSIFY OPTIONS
-- drop function classify_options;
CREATE OR REPLACE FUNCTION classify_options(productId bigint,option1Id bigint,option2Id bigint,option3Id bigint) RETURNS 
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
                                (select distinct on (variant.fk_varopt_1_dtl_id) 
                                 ophdr.id fk_varopt_hdr_id,ophdr.title fk_varopt_hdr_title,opdtl.id fk_varopt_dtl_id,opdtl.title fk_varopt_hdr_title, 
                                variant.fk_prod_id,variant.var_1_title as var_title,1 as option_type from variant 
                                inner join variant_option_hdrs ophdr on ophdr.id=variant.fk_varopt_1_hdr_id 
                                inner join variant_option_dtls opdtl on variant.fk_varopt_1_dtl_id = opdtl.id
                                where variant.fk_varopt_1_hdr_id=option1Id
                                order by variant.fk_varopt_1_dtl_id,variant.fk_varopt_2_dtl_id,variant.fk_varopt_3_dtl_id) 
                                union all
                                ( select distinct on (variant.fk_varopt_2_dtl_id) 
                                ophdr.id fk_varopt_hdr_id,ophdr.title fk_varopt_hdr_title,opdtl.id fk_varopt_dtl_id,opdtl.title fk_varopt_hdr_title,
                                variant.fk_prod_id,variant.var_2_title as var_title,2 as option_type from variant 
                                inner join variant_option_hdrs ophdr on ophdr.id=variant.fk_varopt_2_hdr_id 
                                inner join variant_option_dtls opdtl on variant.fk_varopt_2_dtl_id = opdtl.id
                                where variant.fk_varopt_2_hdr_id=option2Id
                                order by variant.fk_varopt_2_dtl_id,variant.fk_varopt_3_dtl_id) 
                                union all
                                ( select distinct on (variant.fk_varopt_3_dtl_id) 
                               ophdr.id fk_varopt_hdr_id,ophdr.title fk_varopt_hdr_title,opdtl.id fk_varopt_dtl_id,opdtl.title fk_varopt_hdr_title,
                                variant.fk_prod_id,variant.var_3_title as var_title,3 as option_type from variant 
                                inner join variant_option_hdrs ophdr on ophdr.id=variant.fk_varopt_3_hdr_id 
                                inner join variant_option_dtls opdtl on variant.fk_varopt_3_dtl_id = opdtl.id
                                where variant.fk_varopt_3_hdr_id=option3Id);
        END;
$$ LANGUAGE plpgsql;

--GET PRODUCT ATTRIBUTES
CREATE OR REPLACE FUNCTION get_product_attributes(lvlCategoryId bigint,variantId bigint,productId bigint) RETURNS 
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
$$ LANGUAGE plpgsql;


--FUNCTION PRODUCT SUMMARY
CREATE OR REPLACE FUNCTION inv_product_summary(brandId bigint) RETURNS 
table(
	total_product bigint,
	totalSellingPrice double precision,
	totalOrginalPrice double precision)
AS $$
	
        BEGIN
			RETURN QUERY select count(*) as total_product,
			sum(stock_variant.selling_price*quantity) as totalSellingPrice,
			sum(stock_variant.your_price*quantity) as totalOrginalPrice
			from stock_variant inner join stock on stock.id=stock_variant.fk_stock
			where stock.fk_brand=brandId; 
        END;
$$ LANGUAGE plpgsql;


--FUNCTION SET PRODUCT DEFAULT 
CREATE OR REPLACE FUNCTION set_product_default(stockId bigint)  RETURNS bigint
AS $$
	declare defVariId bigint;
        BEGIN
			select id into defVariId from stock_variant where fk_stock=stockId order by id asc limit 1;
			update stock_variant set is_default = false where fk_stock=stockId and id <> defVariId;
			update stock_variant set is_default = true where id=defVariId;
			return defVariId;
        END;
$$ LANGUAGE plpgsql;

--FUNCTION GEOMETRY
CREATE OR REPLACE FUNCTION get_location(latitude double precision,longitude double precision) RETURNS 
table(
	state_mm text,state_pcode text,
	district_mm text,district_pcode text,
	township_mm text,township_pcode text,
	villagetrack_mm text,villagetrack_pcode text,
	ward_mm text,ward_pcode text
	)
AS $$
	declare point text = CONCAT('POINT(',longitude,' ',latitude,')') ;
        BEGIN
			RETURN QUERY 
 			select 
 			geo_state.mmr4 as state_mm,geo_state.pcode as state_pcode,
 			geo_district.mmr4 as district_mm,geo_district.pcode as district_pcode,
 			geo_township.mmr4 as township_mm,geo_township.pcode as township_pcode,
 			geo_villagetrack.mmr4 as villagetrack_mm,geo_villagetrack.pcode as villagetrack_pcode,
 			geo_ward.mmr4 as ward_mm,geo_ward.pcode as ward_pcode
 			from geo_state
 			inner join geo_district on geo_district.p_pcode = geo_state.pcode
 			inner join geo_township on geo_township.p_pcode = geo_district.pcode
 			inner join geo_villagetrack on geo_villagetrack.p_pcode = geo_township.pcode
 			left join geo_ward on geo_ward.p_pcode = geo_township.pcode
 			where st_contains(geo_villagetrack.geometry, ST_GeomFromText(point, 4326))
 			or 
 			st_contains(geo_ward.geometry, ST_GeomFromText(point, 4326));
-- 			select 
-- 			point as state_mm,'' as state_pcode,
-- 			'' as district_mm,'' as district_pcode,
-- 			'' as township_mm,'' as township_pcode,
-- 			'' as villagetrack_mm,'' as villagetrack_pcode,
-- 			'' as ward_mm,'' as ward_pcode;
			
        END;
$$ LANGUAGE plpgsql;

--FUNCTION GEOMETRY
CREATE OR REPLACE FUNCTION get_location(latitude double precision,longitude double precision) RETURNS 
table(
	state_mm text,state_pcode text,
	district_mm text,district_pcode text,
	township_mm text,township_pcode text
	)
AS $$
	declare point text = CONCAT('POINT(',longitude,' ',latitude,')') ;
	
        BEGIN
			return query
			select
 			geo_state.mmr4 as state_mm,geo_state.pcode as state_pcode,
 			geo_district.mmr4 as district_mm,geo_district.pcode as district_pcode,
 			geo_township.mmr4 as township_mm,geo_township.pcode as township_pcode
 			from geo_state
 			inner join geo_district on geo_district.p_pcode = geo_state.pcode
 			inner join geo_township on geo_township.p_pcode = geo_district.pcode
 			where st_contains(geo_township.geometry, ST_GeomFromText(point, 4326));
        END;
$$ LANGUAGE plpgsql;

-----update image status by id
CREATE OR REPLACE FUNCTION cs_bef_updateuser()  
RETURNS TRIGGER 
AS $$
        BEGIN
			if (old.fk_image is not null) then
				update image set status=4 where id=old.fk_image;
			end if;
			RETURN new;
        END;
$$ LANGUAGE plpgsql;
CREATE OR REPLACE FUNCTION cs_bef_deleteuser()  
RETURNS TRIGGER 
AS $$
        BEGIN
			if (old.fk_image is not null) then
				update image set status=4 where id=old.fk_image;
			end if;
			RETURN old;
        END;
$$ LANGUAGE plpgsql;


create trigger cs_bef_updateuser
before update on myuser for each row
EXECUTE PROCEDURE  cs_bef_updateuser();

create trigger cs_bef_deleteuser
before delete on myuser for each row
EXECUTE PROCEDURE  cs_bef_deleteuser();

DROP TRIGGER cs_bef_deleteuser ON myuser;



