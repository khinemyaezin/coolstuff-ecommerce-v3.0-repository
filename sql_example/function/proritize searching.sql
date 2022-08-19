
--proritize searching
((SELECT *  FROM categories_leaf WHERE lower(path) = lower('clothing') LIMIT 10 ) UNION
(SELECT * FROM categories_leaf WHERE  lower(path) LIKE lower('clothing%') LIMIT 10) UNION
(SELECT * FROM categories_leaf WHERE  lower(path) LIKE lower('%clothing%') LIMIT 10)) 
UNION

	(SELECT *  FROM categories_leaf WHERE lower(title_leaf) = lower('clothing') LIMIT 10 ) UNION
(SELECT * FROM categories_leaf WHERE  lower(path) LIKE lower('clothing%') LIMIT 10) UNION
(SELECT * FROM categories_leaf WHERE  lower(path) LIKE lower('%clothing%') LIMIT 10)



	(SELECT *  FROM categories_leaf WHERE lower(path) = lower('watches') LIMIT 10 ) UNION
(SELECT * FROM categories_leaf WHERE  lower(path) LIKE lower('watches%') LIMIT 10) UNION
(SELECT * FROM categories_leaf WHERE  lower(path) LIKE lower('%watches%') order by length(path) LIMIT 10) 