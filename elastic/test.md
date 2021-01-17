### shard的好处
1. 横向扩展
2. 数据分布在多个shard，多台服务器上，所有的操作都会在多台服务器傻姑娘执行，提升吞吐量和性能。
3. index会被拆分为多个shard，每个shard就会存放这个index 的部分数据。
4. shard也叫primary shard，一般建成shard。
5. replica其实叫replica shard，一般叫replica
6. 高可用性，一个shard宕机，数据不丢，服务继续提供。
7. replica提升了搜索这类请求的吞吐离量和性能。
8. primary shard 建立索引时设置，不能修改，默认5个。
9. replica shard 随时修改数量，默认一个。
10. 默认每个索引10个shard，5个primary shard， 5个replica shard，最小的高可用配置是两台服务器。


### 其他
1. Document  行
2. type  表
3. index  数据库



### 简单的集群管理
1. 快速检查集群的健康状况
2. es提供了一套api，叫做cat api， 可以查看es中各种各样的数据
3. green ： 每个索引的primary shard 和replica shard都是active 状态
4. yellow ： 每个索引的primary shard 都是active 状态，但是部分replica处于不可用状态
5. red : 不是所有索引的primary shard都是active状态，部分索引有数据丢失。

### ES 对复杂分布式机制的透明隐藏特性
1. 分片机制
2. cluster discovery
3. shard负载均衡
4. shard 副本，请求路由

### 扩容方案
1. 水平扩容 ： 新购两台服务器，直接加入集群中去。



master节点不承载所有的请求，所以不会是一个单点瓶颈。
1. 管理es集群的元数据 ： 比如说索引和创建和删除，维护索引元数据，姐弟那的增加和移除，
维护集群的元数据。
3. 默认情况下，会自动选择出一台节点，作为master节点。
4. primary shard 不能和自己的replica shard放在
同一个节点上（否则节点宕机，primary shard和副本都丢失，
起不到容错作用）但是可以和其他的primary shard的replica sahrd放在同一个节点上。



5. 扩容后，每个节点的shard数量更少，就意味着每个shard可以占用
节点上更多的资源，IO / CPU / Memory 这个系统，性能会更好。


### 容错步骤
1. master选举，自动选举另外一个node成为新的master，承担起
master的责任来。
2. 新master，将丢失掉的primary shard 的某个replica shard
提升为primary shard。此时cluster status 会变为active了。
但是，少了一个replica shard，所以不是所有的replica shard 都是active了。
3. 重启故障的node，new master，会将缺失的副本都是copy一份到该node上去，而且该node会使用之前
已有的shard数据，只是同步一下宕机之后发生过的修改。
cluster status 变为green，因为primary shard 和replica shard都齐全了。