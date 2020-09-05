1. varchar 如果做部分索引，那么like失效。



部分索引的性能一样那么高， 就是sql可以接收的参数变短了。

前缀索引(字符串截取部分作为索引)的原理？
哈希索引的原理？
跳表的原理？
索引下推是什么意思？

而MySQL 5.6 引入的索引下推优化（index condition pushdown)， 
可以在索引遍历过程中，对索引中包含的字段（联合索引中的字段都在索引上）先做判断，
直接过滤掉不满足条件的记录，减少回表次数。





MyISAM索引文件和数据文件是分离的，索引文件仅保存记录所在页的指针（物理位置），通过这些地址来读取页，进而读取被索引的行。

InnoDB 中，主键索引和数据是一体的，没有分开


MyISAM适合：(1)做很多count 的计算；(2)插入不频繁，查询非常频繁；(3)没有事务。

InnoDB适合：(1)可靠性要求比较高，或者要求事务；(2)表更新和查询都相当的频繁，并且行锁定的机会比较大的情况。


2. 为什么MyISAM会比Innodb 的查询速度快。

INNODB在做SELECT的时候，要维护的东西比MYISAM引擎多很多；
1）数据块，INNODB要缓存，MYISAM只缓存索引块，  这中间还有换进换出的减少； 
2）innodb寻址要映射到块，再到行，MYISAM 记录的直接是文件的OFFSET，定位比INNODB要快
3）INNODB还需要维护MVCC一致；虽然你的场景没有，但他还是需要去检查和维护



1 查询语句的执行流程

客户端 - 连接器(管理连接，权限验证) - 查询缓存（如果开启查询缓存）- 分析器(语法分析) 

- 优化器(生成执行计划，索引选择) - 执行器(操作引擎，返回结果)


1 更新语句的执行流程
redo log（重做日志）和 binlog（归档日志）
粉板redo log是InnoDB引擎特有的日志，而Server层也有自己的日志，称为binlog（归档日志）。

我想你肯定会问，为什么会有两份日志呢？

因为最开始MySQL里并没有InnoDB引擎。MySQL自带的引擎是MyISAM，但是MyISAM没有crash-safe的能力，binlog日志只能用于归档。而InnoDB是另一个公司以插件形式引入MySQL的，既然只依靠binlog是没有crash-safe能力的，所以InnoDB使用另外一套日志系统——也就是redo log来实现crash-safe能力。

简单说，redo log和binlog都可以用于表示事务的提交状态，而两阶段提交就是让这两个状态保持逻辑上的一致。


这两种日志有以下三点不同。

    redo log是InnoDB引擎特有的；binlog是MySQL的Server层实现的，所有引擎都可以使用。
    
    redo log是物理日志，记录的是“在某个数据页上做了什么修改”；binlog是逻辑日志，记录的是这个语句的原始逻辑，比如“给ID=2这一行的c字段加1 ”。
    
    redo log是循环写的，空间固定会用完；binlog是可以追加写入的。“追加写”是指binlog文件写到一定大小后会切换到下一个，并不会覆盖以前的日志。

有了对这两个日志的概念性理解，我们再来看执行器和InnoDB引擎在执行这个简单的update语句时的内部流程。

    执行器先找引擎取ID=2这一行。ID是主键，引擎直接用树搜索找到这一行。如果ID=2这一行所在的数据页本来就在内存中，就直接返回给执行器；否则，需要先从磁盘读入内存，然后再返回。
    
    执行器拿到引擎给的行数据，把这个值加上1，比如原来是N，现在就是N+1，得到新的一行数据，再调用引擎接口写入这行新数据。
    
    引擎将这行新数据更新到内存中，同时将这个更新操作记录到redo log里面，此时redo log处于prepare状态。然后告知执行器执行完成了，随时可以提交事务。
    
    执行器生成这个操作的binlog，并把binlog写入磁盘。
    
    执行器调用引擎的提交事务接口，引擎把刚刚写入的redo log改成提交（commit）状态，更新完成。
    
    
隔离性与隔离级别

ACID（Atomicity、Consistency、Isolation、Durability，即原子性、一致性、隔离性、持久性）

    读未提交是指，不管开不开事务，所有的改动都可以实时看到。
    读提交是指，一个事务提交之后，它做的变更才会被其他事务看到。（Oracle数据库的默认隔离级别）（可能会出现幻象读、不可重复读）
    可重复读是指，一个事务执行过程中看到的数据，总是跟这个事务在启动时看到的数据是一致的。
        当然在可重复读隔离级别下，未提交变更对其他事务也是不可见的。（MySQL的隔离级别）(能会出现幻象读)
    串行化是指，事务串联在一起。一个接一个执行。
    
不可重复读：是指在数据库访问中，一个事务范围内两个相同的查询却返回了不同数据。
这是由于查询时系统中其他事务修改的提交而引起的。比如事务T1读取某一数据，事务T2读取并修改了该数据，
T1为了对读取值进行检验而再次读取该数据，便得到了不同的结果。


幻觉读：指当事务不是独立执行时发生的一种现象，
例如 第一个事务对一个表中的数据进行了修改，这种修改涉及到表中的全部数据行。
同时，第二个事务也修改这个表中的数据，这种修改是向表中插入一行新数据。
那么，以后就会发生操作第一个事务的用户发现表中还有没有修改的数据行，就好像发生了幻觉一样。



在MySQL中，实际上每条记录在更新的时候都会同时记录一条回滚操作。



系统会判断，当系统里没有比这个回滚日志(undo log)更早的read-view的时候，回滚日志会被删除。


长事务意味着系统里面会存在很老的事务视图。由于这些事务随时可能访问数据库里面的任何数据，
所以这个事务提交之前，数据库里面它可能用到的回滚记录都必须保留，这就会导致大量占用存储空间。


主键长度越小，普通索引的叶子节点就越小，普通索引占用的空间也就越小。因为主键长度越小，
每页可以存储的节点就越多。

在InnoDB事务中，行锁是在需要的时候才加上的，但并不是不需要了就立刻释放，
而是要等到事务结束时才释放。这个就是两阶段锁协议。

如果你的事务中需要锁多个行，要把最可能造成锁冲突、最可能影响并发度的锁尽量往后放。这就最大程度地减少了事务之间的锁等待，提升了并发度。



死锁的两种策略：

一种策略是，直接进入等待，直到超时。这个超时时间可以通过参数innodb_lock_wait_timeout来设置。
另一种策略是，发起死锁检测，发现死锁后，主动回滚死锁链条中的某一个事务，让其他事务得以继续执行。将参数innodb_deadlock_detect设置为on，表示开启这个逻辑。

1，加快接口的速度
2  梳理业务和软件设计，避免死锁的发生



InnoDB利用了“所有数据都有多个版本”的这个特性，实现了“秒级创建快照”的能力。
在实现上， InnoDB为每个事务构造了一个数组，用来保存这个事务启动瞬间，当前正在“活跃”的所有事务ID。“活跃”指的就是，启动了但还没提交。

数组里面事务ID的最小值记为低水位，当前系统里面已经创建过的事务ID的最大值加1记为高水位。

这个视图数组和高水位，就组成了当前事务的一致性视图（read-view）。

而数据版本的可见性规则，就是基于数据的row trx_id和这个一致性视图的对比结果得到的。

这个视图数组把所有的row trx_id 分成了几种不同的情况。
这样，对于当前事务的启动瞬间来说，一个数据版本的row trx_id，有以下几种可能：

如果落在绿色部分，表示这个版本是已提交的事务或者是当前事务自己生成的，这个数据是可见的；

如果落在红色部分，表示这个版本是由将来启动的事务生成的，是肯定不可见的；

如果落在黄色部分，那就包括两种情况
a. 若 row trx_id在数组中，表示这个版本是由还没提交的事务生成的，不可见；
b. 若 row trx_id不在数组中，表示这个版本是已经提交了的事务生成的，可见。




配置Change Buffer
对表执行 INSERT，UPDATE和 DELETE操作时， 索引列的值（尤其是secondary keys的值）
通常按未排序顺序排列，需要大量I / O才能使二级索引更新。
Change Buffer会缓存这个更新当相关页面不在Buffer Pool中，
从而磁盘上的相关页面不会立即被读避免了昂贵的I / O操作。
当页面加载到缓冲池中时，将合并缓冲的更改，稍后将更新的页面刷新到磁盘。
该InnoDB主线程在服务器几乎空闲时以及在慢速关闭期间合并缓冲的更改 。


Change Buffer占用Buffer Pool的一部分，从而减少了可用于缓存数据页的内存。
如果工作集几乎适合Buffer Pool，
或者您的表具有相对较少的二级索引，则禁用Change Buffer可能很有用。




简而言之：Change buffer的主要目的是将对二级索引的数据操作缓存下来，
以此减少二级索引的随机IO，并达到操作合并的效果。

change buffer用的是buffer pool里的内存，因此不能无限增大。change buffer的大小，可以通过参数innodb_change_buffer_max_size来动态设置。这个参数设置为50的时候，表示change buffer的大小最多只能占用buffer pool的50%。

    如果要在这张表中插入一个新记录(4,400)的话，InnoDB的处理流程是怎样的。
    
    第一种情况是，这个记录要更新的目标页在内存中。这时，InnoDB的处理流程如下：
    
    对于唯一索引来说，找到3和5之间的位置，判断到没有冲突，插入这个值，语句执行结束；
    对于普通索引来说，找到3和5之间的位置，插入这个值，语句执行结束。
    这样看来，普通索引和唯一索引对更新语句性能影响的差别，只是一个判断，只会耗费微小的CPU时间。
    
    但，这不是我们关注的重点。
    
    第二种情况是，这个记录要更新的目标页不在内存中。这时，InnoDB的处理流程如下：
    
    对于唯一索引来说，需要将数据页读入内存，判断到没有冲突，插入这个值，语句执行结束；
    对于普通索引来说，则是将更新记录在change buffer，语句执行就结束了。
    将数据从磁盘读入内存涉及随机IO的访问，是数据库里面成本最高的操作之一。change buffer因为减少了随机磁盘访问，所以对更新性能的提升是会很明显的。
    
    之前我就碰到过一件事儿，有个DBA的同学跟我反馈说，他负责的某个业务的库内存命中率突然从99%降低到了75%，整个系统处于阻塞状态，更新语句全部堵住。而探究其原因后，我发现这个业务有大量插入数据的操作，而他在前一天把其中的某个普通索引改成了唯一索引。
    
所以，我建议你尽量选择普通索引。



redo log 主要节省的是随机写磁盘的IO消耗（转成顺序写）（缓存起来，不用马上写），
而change buffer主要节省的则是随机读磁盘的IO消耗。(因为操作直接缓存起来了，
不用读取磁盘的数据做一系列的操作。)


索引选择异常和处理

一种方法是，像我们第一个例子一样，采用force index强行选择一个索引。
第二种方法就是，我们可以考虑修改语句，引导MySQL使用我们期望的索引。
第三种方法是，在有些场景下，我们可以新建一个更合适的索引，来提供给优化器做选择，或删掉误用的索引。


MySQL在处理事务时，会在数据共享 表空间里申请一个段叫做segment段，用保存undo信息，当在处理rollback，不是完完全全的物理undo，而是逻辑undo,就是说会对之 前的操作进行反操作，但是这些共享表空间是不进行回收的。这些表空间的回收需要由mysql的master thread进程来进行回收。