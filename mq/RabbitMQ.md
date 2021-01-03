
docker pull rabbitmq:3.7.7-management

docker run -d --name rabbitmq3.7.7 
-p 5672:5672 
-p 15672:15672 
-v `pwd`/data:/var/lib/rabbitmq 
--hostname myRabbit 
-e RABBITMQ_DEFAULT_VHOST=my_vhost  
-e RABBITMQ_DEFAULT_USER=admin 
-e RABBITMQ_DEFAULT_PASS=admin df80af9ca0c9

version: '3'
services:
  rabbitmq:
    image: rabbitmq:management
    container_name: myrabbitmq
    hostname: myrabbitmq
    restart: always
    ports:
      - 5672:5672
      - 15672:15672
    volumes:
      - /var/docker/rabbitmq/data:/var/lib/rabbitmq
    environment:
      - RABBITMQ_DEFAULT_USER=admin
      - RABBITMQ_DEFAULT_PASS=123456
说明:
rabbitmq:3.8.6-management:后面带management是带web管理界面的
RABBITMQ_DEFAULT_USER:默认账号和密码是:guest
RABBITMQ_DEFAULT_PASS:设置密码






## 简介
1. Erlang有着和原生soket一样的延迟
2. channel是进行消息读写的通道
3. 每个 channel 代表一个会话任务
4. message：消息传送的数据
5. Exchange:交换机，接收，分发数据到限定的队列
6. Binding ： Exchange和Queue之间的虚拟连接，binding中可以包含routing key
7. Routing key ： 一个路由规则，虚拟机可用它来确定如何路由一个特定消息
8. Queue ： 消息队列
9. 生产者（ channel ）->  Exchage -> Binding -> Queue ->（ channel ）消费者

## 命令行与管控台
1. rabbitmqctl stop_app ： 关闭应用
2. rabbitmqctl start_app ： 启动应用
3. rabbitmqctl status : 节点状态
4. add_user username password : 添加用户
5. list_user 列出所有用户
6. delete_user username 删除用户
7. clear_permissions -p vhostpath username 清除用户权限

## 交换机属性
1. name ： 名字
2. type ： 交换机类型 direct，topic，fanout， headers
3. durability ： 是否需要持久化，true是需要
4. auto delete 当最后一个绑定到Exchage上的队列删除后，自动删除该Exchange
5. internal ： 当前Exchage是否用于RabbitMQ内部使用，默认false
6. Arguments ； 扩展参数，用于扩展AMQP协议制定化使用

## 交换机模型
1. Direct Exchange
  所有发送到Direct Exchange 的消息被转发到Routekey中制定的Queue，
  Direct模式可以使用RabbitMQ自带的Exchange ： default Exchange，所以不需要将Exchange进行任何绑定操作
  消息传递时，Routekey必须完全匹配才会被队列接收，否则该信息会被抛弃。
2. Topic Exchange
  所有发送到Topic Exchange的消息被转发到所有关心Routekey中制定Topic的Queue上。
  Exchange将Routekey和某Topic进行模糊匹配，此时队列需要绑定一个Topic。
  注意：可以使用通配符进行模糊匹配
3. Fanout Exchange
  不处理路由键，只需要简单的将队列绑定到交换机上
  发送到交换机的消息都会被转发到与该交换机绑定的所有队列上
  Fanout 交换机住哪发消息是最快的

## 队列
1. Exchange 和 Exchange， Queue之间的连接关系
2. Binding中可以包含Routingkey或者参数
3. Queue消息队列实际存储消息数据
4. Durability是否持久化，Durable 是 Transiont 否
5. Auto delete ： 如选择yes， 代表当最后一个监听被移除之后，Queue会自动被删除
6. virtual host 虚拟主机
  虚拟地址，用于进行逻辑隔离，最上层的消息路由
  一个virtual host 里面可以有若干个Exchange 和 Queue
  同一个 virtual host 里面不能有相同名称的Exchange 或者 Queue


## 分布式事务
  1. 生产环境很少做分布式事务，因为在性能上损耗很大
  2. 大多数情况下，大厂的做法是不做事务，只做补偿  


  ### AMQP协议
  1. 使用Erlang ： 和原生socker一样的延迟
  2. 考量目标：录到MQ节点上之后的延迟和响应是非常重要的
  3. AMQP协议模型
  publisher -> server
                  virtual host
                    exchange
  consumer  <-         message queue

4. server ： 又称Broker，接受客户端的连接，实现AMQP实体服务。
5. connection ： 连接，应用程序与Broker的网络连接。
6. channel ： 网络通道。几乎所有的损耗都在channel中进行，channel是进行消息，读写的通道。客户端可建立多个channel。
7. virtual host 虚拟地址，用于进行逻辑隔离，最上层的消息路由。
8. Exchange ： 交换机
9. Routingkey ： 一个路由规则
10. Queue 消息队列



### 消息如何保障100%的投递成功
1. 什么是生产段的可靠性投递？
  1. 保障消息的成功发出
  2. 保障MQ节点的成功接收
  3. 发送段收到MQ节点（ Broker ）确认应答
  4. 完善的消息进行补偿机制

2. 生产端可靠性投递方案1
  1. 消息落库，对消息状态进行打标
  2. 消息的延迟投递，做二次确认，回调检查。
  3. 建议在机制中加入超时机制
 将消息存入数据库，记录消息的状态。可以通过轮询不断获取消息的状态，从而保证消息的成功投递。如下图示所示，这样做有一个很严重的问题就是要多次操作数据库，对于一些高并发、对性能要求较高的业务，这种方式是不太合适的。因为频繁操作数据库会带来严重的性能问题。

3. 生产端可靠性投递方案2
  1. 高并发场景下，消息的延迟投递，做二次确认，回调检查。
  2. 一定是数据先入库，然后再发送数量
  3. 不加事务，只做补偿
生产端会发送两次消息：
      第一次：生产端首先会将业务数据存入DB，之后会向MQ发送一个消息，消费端收到消息后发送确认消息(这里的确认消息不是指ack，而是重新编辑发送一条新消息)，回调服务监听到确认消息后将消息存入DB；
       第二次：在第一条消息发送出去后一段时间，生产端会再发送一条check消息，回调服务监听到check消息后会检查第一条消息的执行情况，如果消息未能按照预期结果执行的话，回调服务会给生产端发送一条指令让生产端重新发送消息；
       如下图所示，这种方法可以有效的避免对数据库的频繁操作，从而提高性能；同时业务DB和消息DB之间解耦；



### 消费端幂等性保障：
1. 唯一ID+指纹码机制，利用数据库主键去重
select count(1) from xxx where id = 唯一id + 指纹
好处：实现简单；
坏处：高并发下有数据库写入的性能瓶颈；
解决方案：利用ID进行分库分表进行算法路由；

2. 利用redis的原子性去实现
  1. 我们是否要进行数据落库，如果落库的话，关键解决的问题是数据库和缓存如何做到原子性？
  2. 如果不进行落库，那么都存储到缓存中，如何设置定时同步的策略？

### confirm 确认消息
1. 消息的确认是指生产者投递消息后，如果Broker 收到消息，则会给我们生产者一个应答。
2. Broker 缓存代理，一台或多台服务器统称。
3. 生产者进行接收应答，用来确认这条消息是否正常的发送到Broker，这种方式也是消息的可靠性投递的核心保障。


### Return 消息机制
1. Return Listener 用于处理一些不可路由的消息
2. 我们的消息生产者，通过制定一个Exchange和Routiagkey把消息送达到某一个队列中去，然后我们的消息者监听队列，进行消费处理操作。
3. 但是在某些情况下，如果我们在发送消息的时候，当前的exchange不存在，或者制定的路由key路由不到，这个时候如果我们需要监听这种不可达的消息，
就需要使用Return Listener。

### 消费端限流
1. 假设一个场景，首先我们Rabbitmq服务器有上万条为处理的消息，我们随便打开一个消费者客户端会出现下面情况：
巨量的消息瞬间全部推送过来，但是我们单个客户端无法同时处理这么多数据。
2. RabbitMQ提供了一种QOS（服务质量保证）功能，在非自动确认消息的前提下，如果一定数目的消息通过基于consume或者channel设置Qos的值）未被确认前，
不进行消费新的消费，这样就可以减压了。
3. MQ的消息签收有两种形式，手动和自动签收，如果是限流的话，一定要手动签收。

### 消费端 ACK 与 重回队列
1. 消费端的手工ACK和NACK
ACK ： 应答
NACK ： 无应答

2. 消费端进行消费的时候，如果由于业务一场我们进行日志的记录，然后进行补偿。
3. 如果由于服务器宕机等严重问题，那我们就需要手动进行ACK保障消费端消费成功。


### 消费端的重回队列
1. 消费端重回队列是为了对没有处理成功的消息，把消息重新投递给Broker
2. 一般我们在实际应用中，都会关闭重回队列。也就是设置成false。

### TTL队列/消息
1. TTL是time to live 的缩写，也就是生存时间。
2. Rabbit mq 支持消息的过期时间，在消息被送时可以进行执行。
3. Rabbit mq 支持队列的过期时间，，从消息入队列开始计算，只要超过了队列的超时时间配置，那么消息会自动的清除。


### 死信队列
1. 利用DLX，当消息在一个队列中，变成死信队列后，它能被重新publish到另外一个Exchange，这个Exchange就是DLX。
2. 消息变成死信队列有几个情况：
  1. 消息被拒绝，并且requeue = false
  2. 消息TTL过期时间
  3. 队列达到最大长度
3. DLX也是一个正常的Exchange和一般的Exchange没有区别，它能在任务的队列上被指定，实际上就是设置某个队列的属性。
4. 当这个队列中有死信时，RabbitMQ就会自动的将这个消息重新发布到设置的Exchange上去，进而被路由到另外一个队列。
5. 可以监听这个队列中消息做相应的处理。