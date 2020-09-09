 ### 
1. group by 都比 distinct 快

2. in 比 or 快 。MySQL将in()列表中的数据先进行排序，然后通过二分查找的方式来确定列表中的值是否满足条件，
这是一个o(log n)复杂度的操作，等价转换成or的查询的复杂度为o(n)，对于in()列表中有大量取值的时候，MySQL的处理速度会更快。


3. 多个单列索引。多个 or 条件不走索引。修改成union。主键 or 索引条件会索引合并

4. 两个表中一个较小，一个是大表，则子查询表大的用exists，子查询表小的用in;



11、优化LIMIT分页
当需要分页操作时，通常会使用LIMIT加上偏移量的办法实现，同时加上合适的ORDER BY字句。如果有对应的索引，通常效率会不错，否则，MySQL需要做大量的文件排序操作。
 
一个常见的问题是当偏移量非常大的时候，比如：LIMIT 10000 20这样的查询，MySQL需要查询10020条记录然后只返回20条记录，前面的10000条都将被抛弃，这样的代价非常高。
 
优化这种查询一个最简单的办法就是尽可能的使用覆盖索引扫描，而不是查询所有的列。然后根据需要做一次关联查询再返回所有的列。对于偏移量很大时，这样做的效率会提升非常大。考虑下面的查询：
Mysql代码
SELECT film_id,description FROM film ORDER BY title LIMIT 50,5;  
 
如果这张表非常大，那么这个查询最好改成下面的样子：
Mysql代码
SELECT film.film_id,film.description  
FROM film INNER JOIN (  
    SELECT film_id FROM film ORDER BY title LIMIT 50,5  
) AS tmp USING(film_id);  
 
这里的延迟关联将大大提升查询效率，让MySQL扫描尽可能少的页面，获取需要访问的记录后在根据关联列回原表查询所需要的列。
 
有时候如果可以使用书签记录上次取数据的位置，那么下次就可以直接从该书签记录的位置开始扫描，这样就可以避免使用OFFSET，比如下面的查询：
Mysql代码
SELECT id FROM t LIMIT 10000, 10;  
改为：  
SELECT id FROM t WHERE id > 10000 LIMIT 10;  