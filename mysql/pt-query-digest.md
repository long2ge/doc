# 简介
官网 : https://www.percona.com/doc/percona-toolkit/LATEST/pt-query-digest.html  
 pt-query-digest是用于分析mysql慢查询的一个工具，它可以分析binlog、General log、slowlog，也可以通过SHOWPROCESSLIST或者通过tcpdump抓取的MySQL协议数据来进行分析。可以把分析结果输出到文件中，分析过程是先对查询语句的条件进行参数化，然后对参数化以后的查询进行分组统计，统计出各查询的执行时间、次数、占比等，可以借助分析结果找出问题进行优化。

# 安装教程
#### docker安装
    1. 在Dokcerfile文件中添加 RUN apt-get update && apt-get install -y percona-toolkit
    2. 打开慢日志， 修改 my.cnf 配置  
        log_output=file  
        slow_query_log=on  
        slow_query_log_file = /var/lib/mysql/mysql-slow.log  
        log_queries_not_using_indexes=on  
        long_query_time = 1  
    3. docker-compose stop mysql && docker-compose up -d --build mysql
####　其他方式安装教程
    https://www.percona.com/doc/percona-toolkit/3.0/installation.html


# pt-query-digest 参数说明
    --create-review-table  当使用--review参数把分析结果输出到表中时，如果没有表就自动创建。  
  
    --create-history-table  当使用--history参数把分析结果输出到表中时，如果没有表就自动创建。    

    --filter  对输入的慢查询按指定的字符串进行匹配过滤后再进行分析  

    --limit  限制输出结果百分比或数量，默认值是20,即将最慢的20条语句输出，如果是50%则按总响应时间占比从大到小排序，输出到总和达到50%位置截止。  

    --host  mysql服务器地址  

    --user  mysql用户名  

    --password  mysql用户密码  

    --history  将分析结果保存到表中，分析结果比较详细，下次再使用--history时，如果存在相同的语句，且查询所在的时间区间和历史表中的不同，则会记录到数据表中，可以通过查询同一CHECKSUM来比较某类型查询的历史变化。  

    --review  将分析结果保存到表中，这个分析只是对查询条件进行参数化，一个类型的查询一条记录，比较简单。当下次使用--review时，如果存在相同的语句分析，就不会记录到数据表中。  

    --output  分析结果输出类型，值可以是report(标准分析报告)、slowlog(Mysql slow log)、json、json-anon，一般使用report，以便于阅读。  

    --since  从什么时间开始分析，值为字符串，可以是指定的某个”yyyy-mm-dd [hh:mm:ss]”格式的时间点，也可以是简单的一个时间值：s(秒)、h(小时)、m(分钟)、d(天)，如12h就表示从12小时前开始统计。  

    --until  截止时间，配合—since可以分析一段时间内的慢查询。

# 分析 pt-query-digest 输出结果

#### 第一部分：总体统计结果  
    Overall：总共有多少条查询  
    
    Time range：查询执行的时间范围  
    
    unique：唯一查询数量，即对查询条件进行参数化以后，总共有多少个不同的查询  
    
    Attribute : 属性
    
    total：总计  
    
    min：最小  
    
    max：最大  
    
    avg：平均  
    
    stddev : 标准  
    
    95%：把所有值从小到大排列，位置位于95%的那个数，这个数一般最具有参考价值  
    
    median：中位数，把所有值从小到大排列，位置位于中间那个数  

> 340ms user time(用户时间), 140ms system time(系统时间), 23.99M rss(物理内存占用大小), 203.11M vsz(虚拟内存占用大小)  
> Current date: Fri Nov 25 02:37:18 2016(工具执行时间)   
> Hostname: localhost.localdomain(运行分析工具的主机名)  
> Files: slow.log(被分析的文件名)  
> Overall: 2 total(语句总数量), 2 unique(唯一的语句数量), 0.01 QPS(吞吐量), 0.01x concurrency(并发数) ________________  
> Time range: 2016-11-22 06:06:18 to 06:11:40(日志记录的时间范围)  

|  Attribute(属性)     | total(总计)  | min(最小)  | max(最大)  | avg(平均)     | 95%  | stddev(标准)  | median(中等)  |
|  :---: | :---:  |  :---:  | :---:  | :---:  | :---:  |  :---:  | :---:  |
| Exec time(语句执行时间)           | 3s | 640ms  | 2s  | 1s  | 2s | 999ms  | 1s  |
| Lock time(锁占用时间)             | 1ms |      0  |   1ms |  723us  |   1ms   |  1ms | 723us   |
| Rows sent(发送到客户端的行数)      |         5  |     1   |    4  |  2.50   |    4 |   2.12|    2.50  |
| Rows examine(select语句扫描行数)  |   186.17k  |     0 |186.17k  |93.09k| 186.17k |131.64k | 93.09k  |
| Query size(查询的字符数)          | 455   |   15 |    440  |227.50   |  440 | 300.52 | 227.50  |


#### 第二部分：查询分组统计结果  
    Rank：所有语句的排名，默认按查询时间降序排列，通过--order-by指定  
    
    Query ID：语句的ID，（去掉多余空格和文本字符，计算hash值）
      
    Response：总的响应时间  
    
    time：该查询在本次分析中总的时间占比  
    
    calls：执行次数，即本次分析总共有多少条这种类型的查询语句  
    
    R/Call：平均每次执行的响应时间  
    
    V/M：响应时间Variance-to-mean的比率  
    
    Item：查询对象  

 Profile(mysql 提供可以用来分析当前会话中语句执行的资源消耗情况)

|  Rank   | Query ID           |Response time   | Calls  | R/Call  | V/M    | Item          |
|  :---:  | :---:              | :---:          | :---:  | :---:   | :---:  | :---:         | 
| 1       | 0xF9A57DD5A41825CA |  2.0529        | 76.2%  |1        | 2.0529 |  0.00 SELECT tests   |
| 2       | 0x4194D8F83F4F9365 | 0.6401         | 23.8%  |1        | 0.6401 |  0.0 <2 ITEMS>   |


#### 第三部分：每一种查询的详细统计结果  
    由下面查询的详细统计结果，最上面的表格列出了执行次数、最大、最小、平均、95%等各项目的统计。  
    
    ID：查询的ID号，和上图的Query ID对应  
    
    Databases：数据库名  
    
    Users：各个用户执行的次数（占比）  
    
    Query_time distribution ：查询时间分布, 长短体现区间占比，本例中1s-10s之间查询数量是10s以上的两倍。  
    
    Tables：查询中涉及到的表  
    
    Explain：SQL语句  
    


> Query 1: 0 QPS, 0x concurrency, ID 0xE059E46446FBFF3138ADF5F0076612E7 at byte 0  
> This item is included in the report because it matches --limit.  
> Scores: V/M = 0.00  
> Time range: all events occurred at 2020-07-07T05:54:18  

|  Attribute(属性)     | total(总计)  | min(最小)  | max(最大)  | avg(平均)     | 95%  | stddev(标准)  | median(中等)  |
|  :---: | :---:  |  :---:  | :---:  | :---:  | :---:  |  :---:  | :---:  |
| Exec time(语句执行时间)           | 3s | 640ms  | 2s  | 1s  | 2s | 999ms  | 1s  |
| Lock time(锁占用时间)             | 1ms |      0  |   1ms |  723us  |   1ms   |  1ms | 723us   |
| Rows sent(发送到客户端的行数)      |         5  |     1   |    4  |  2.50   |    4 |   2.12|    2.50  |
| Rows examine(select语句扫描行数)  |   186.17k  |     0 |186.17k  |93.09k| 186.17k |131.64k | 93.09k  |
| Query size(查询的字符数)          | 455   |   15 |    440  |227.50   |  440 | 300.52 | 227.50  |

> String:  
> Databases    default  
> Hosts        172.19.0.1  
> Users        wolffy  
> Query_time distribution( 查询时间分布, 长短体现区间占比，本例中1s-10s之间查询数量是10s以上的两倍。)  
>   1us  
>  10us  
> 100us  
>   1ms  
>  10ms  
> 100ms  
>    1s  ################################################################  
>  10s+  
> Tables(查询中涉及到的表)  
>    SHOW TABLE STATUS FROM `default` LIKE 'tests'\G  
>    SHOW CREATE TABLE `default`.`tests`\G  
> EXPLAIN /*!50100 PARTITIONS*/  
> SELECT * FROM `tests` where name = 'qqqqqq'\G  



# 用法示例
    1. 直接分析慢查询文件:  
        pt-query-digest  slow.log > slow_report.log
        
    2. 分析最近12小时内的查询：  
        pt-query-digest  --since=12h  slow.log > slow_report2.log
        
    3. 分析指定时间范围内的查询：  
        pt-query-digest slow.log --since '2017-01-07 09:30:00' --until '2017-01-07 10:00:00'> > slow_report3.log
        
    4. 分析指含有select语句的慢查询  
        pt-query-digest --filter '$event->{fingerprint} =~ m/^select/i' slow.log> slow_report4.log
        
    5. 针对某个用户的慢查询  
        pt-query-digest --filter '($event->{user} || "") =~ m/^root/i' slow.log> slow_report5.log
        
    6. 查询所有所有的全表扫描或full join的慢查询  
        pt-query-digest --filter '(($event->{Full_scan} || "") eq "yes") ||(($event->{Full_join} || "") eq "yes")' slow.log> slow_report6.log
        
    7. 把查询保存到query_review表  
        pt-query-digest --user=root –password=abc123 --review  h=localhost,D=test,t=query_review--create-review-table  slow.log
        
    8. 把查询保存到query_history表  
        pt-query-digest  --user=root –password=abc123 --review  h=localhost,D=test,t=query_history--create-review-table  slow.log_0001  
        
    9. 通过tcpdump抓取mysql的tcp协议数据，然后再分析  
        tcpdump -s 65535 -x -nn -q -tttt -i any -c 1000 port 3306 > mysql.tcp.txt  
        pt-query-digest --type tcpdump mysql.tcp.txt> slow_report9.log
        
    10. 分析binlog  
        mysqlbinlog mysql-bin.000093 > mysql-bin000093.sql  
        pt-query-digest  --type=binlog  mysql-bin000093.sql > slow_report10.log
        
    11. 分析general log  
        pt-query-digest  --type=genlog  localhost.log > slow_report11.log
    

# 常用命令
    1. pt-query-digest的帮助信息最全：  
        pt-query-digest --help

    2. 分析整个慢查询日志：
        pt-query-digest --report /var/lib/mysql/mysql-slow.log
        pt-query-digest /var/lib/mysql/mysql-slow.log
        
    3. 查看mysql慢日志的位置
        在mysql登录面板执行 : show variables like '%slow%';

    4. 服务器摘要
        pt-summary

    5. 服务器磁盘监测
        pt-diskstats

    6. mysql服务状态摘要
        pt-mysql-summary --user=root --password=root

    7. 指定时间点进行分析：
        pt-query-digest mysql-slow.log-20180907 --since '2018-09-07 08:10:00' --until '2018-09-07 08:25:00' > temp_slow_log.log

    8. 分析最近的时间点查询，例如一小时：
        pt-query-digest  --since=1h  /usr/local/mysql/data/slow.log > temp_slow_log.log
        pt-query-digest  --since=1h  vipstone-slow.log > temp_slow_log.log

    9. 分析点可以精确到秒，比如报告最近半小时的慢查询：
        pt-query-digest --report --since 1800s slow.log >temp_slow_log.log

    10. 分析针对某个用户的慢查询：
        pt-query-digest --filter '($event->{user} || "") =~ m/^db_user_name/i' slow.log  > temp_slow_log.log

    11. 组合（针对用户和时间）：
        pt-query-digest --filter '($event->{user} || "") =~ m/^db_user_name/i' mysql-slow.log --since '2018-10-27 18:00:00' --until '2018-10-27 23:59:00' > temp_slow_log.log


##　参考文献
 http://blog.itpub.net/807718/viewspace-2158116/  
 https://www.percona.com/doc/percona-toolkit/LATEST/pt-query-digest.html  