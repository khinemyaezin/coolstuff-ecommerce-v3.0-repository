-- RETRIEVING A FULL TREE
SELECT node.id,node.title
FROM categories AS node,
        categories AS parent
WHERE node.lft BETWEEN parent.lft AND parent.rgt
AND parent.title = 'root'
ORDER BY node.lft;

-- FINDING ALL THE LEAF NODES
SELECT title
FROM categories
WHERE rgt = lft + 1 ;

-- FINDING THE DEPTH OF THE NODES
SELECT node.id,node.title, (COUNT(parent.title) - 1) AS depth
FROM categories AS node,
        categories AS parent
WHERE node.lft BETWEEN parent.lft AND parent.rgt 
-- and parent.id=9
GROUP BY node.title,node.lft ,node.id
--HAVING (COUNT(parent.title) - 1) =2
ORDER BY node.lft



-- DEPTH OF A SUB-TREE
SELECT node.id,node.title
, ( (COUNT(parent.title)) - (sub_tree.depth + 1)) AS depth
FROM categories AS node,
        categories AS parent,
        categories AS sub_parent,
        (
                SELECT node.title, (COUNT(parent.title) - 1) AS depth
                FROM categories AS node,
                categories AS parent
                WHERE node.lft BETWEEN parent.lft AND parent.rgt
                AND node.id = 12
                GROUP BY node.title
        )AS sub_tree
WHERE node.lft BETWEEN parent.lft AND parent.rgt
        AND node.lft BETWEEN sub_parent.lft AND sub_parent.rgt
        AND sub_parent.title = sub_tree.title
GROUP BY node.id,node.title,sub_tree.depth,node.lft
ORDER BY node.lft

-- FIND THE IMMEDIATE SUBORDINATES OF A NODE
-- Imagine you are showing a category of electronics products on a retailer web site.
-- When a user clicks on a category, you would want to show the products of that category, 
-- as well as list its immediate sub-categories, but not the entire tree of categories beneath it.
-- For this, we need to show the node and its immediate sub-nodes, but no further down the tree. For example, 
-- when showing the PORTABLE ELECTRONICS category,
-- we will want to show MP3 PLAYERS, CD PLAYERS, and 2 WAY RADIOS, but not FLASH.
-- This can be easily accomplished by adding a HAVING clause to our previous query:
SELECT node.id,node.title, (COUNT(parent.id) - (sub_tree.depth + 1)) AS depth
FROM categories AS node,
        categories AS parent,
        categories AS sub_parent,
        (
                SELECT node.id, (COUNT(parent.id) - 1) AS depth
                FROM categories AS node,
                        categories AS parent
                WHERE node.lft BETWEEN parent.lft AND parent.rgt
                        AND node.id = 12
                GROUP BY node.id,node.title,node.lft
                 ORDER BY node.lft
        )AS sub_tree
WHERE node.lft BETWEEN parent.lft AND parent.rgt
        AND node.lft BETWEEN sub_parent.lft AND sub_parent.rgt
        AND sub_parent.id = sub_tree.id
GROUP BY node.id,node.title,sub_tree.depth,node.lft
HAVING (COUNT(parent.id) - (sub_tree.depth + 1)) <= 1
ORDER BY node.lft;

-- structure all tree
SELECT node.id, CONCAT( REPEAT( '>', (COUNT(parent.title) - 1)::integer ), node.title) AS name, node.lft,node.rgt
FROM categories AS node,
        categories AS parent
WHERE node.lft BETWEEN parent.lft AND parent.rgt
GROUP BY node.title,node.lft,node.id,node.lft,node.rgt
ORDER BY node.lft;

--SINGLE PATH
SELECT parent.*
FROM categories AS node,
        categories AS parent
WHERE node.lft BETWEEN parent.lft AND parent.rgt
        AND node.id = 12
ORDER BY parent.lft

--GET LEAF NODE OF UNDER NODE
SELECT child.*
FROM categories as child , categories as parent
WHERE child.rgt = child.lft + 1 and child.lft>parent.lft 
and child.rgt < parent.rgt and parent.id=11
order by child.title asc

--GET PARANT BY CHILD ID
SELECT row_number() over(order by t2.rgt desc)-1 as depth,t2.rgt,t2.lft,
title 
FROM categories t2 
WHERE t2.lft < 110 AND t2.rgt > 111	
ORDER BY t2.rgt-111 desc
--AND
SELECT t1.id,t1.lft,t1.rgt,title
, (SELECT title 
           FROM categories t2 
           WHERE t2.lft < t1.lft AND t2.rgt > t1.rgt    
			
           ORDER BY t2.rgt-t1.rgt ASC
			   limit 1) AS parent
FROM categories t1
ORDER BY rgt-lft DESC
------------------------


--SELECT ALL
select * from categories_leaf where lower(path) like lower('%man%') and lower(path) like lower('%clothing%')
select add_category_leaf();
select * from categories_leaf;

--pagination
 row_number() over(order by b.name asc) as row_num,count(*) over() as total
 select * from pricetype;
 
 --get product under category
 where sv.is_default = true and 
(stock.fk_category in (
SELECT child.id
FROM categories as child , categories as parent
WHERE child.rgt = child.lft + 1 and child.lft>parent.lft 
and child.rgt < parent.rgt and parent.id= 41
) or 
stock.fk_category in (
SELECT parent.id
FROM categories as parent
WHERE parent.rgt = parent.lft+1 and parent.id= 41
))
order by stock.name asc;
 
 
