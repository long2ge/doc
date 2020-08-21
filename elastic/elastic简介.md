# elastic

## 软件介绍 
ELK是Elasticsearch、Logstash、Kibana的简称，这三者是核心套件，但并非全部。  

https://github.com/elastic/elasticsearch  
Elasticsearch是实时全文搜索和分析引擎，提供搜集、分析、存储数据三大功能；

https://github.com/elastic/kibana  
Kibana是一个基于Web的图形界面，用于搜索、分析和可视化存储在 Elasticsearch指标中的日志数据。

https://github.com/elastic/logstash   
Logstash是一个用来搜集、分析、过滤日志的工具。

https://github.com/elastic/beats  
Filebeat主要用于转发和集中日志数据。可以转发到ElasticSearch或Logstash上。


## 安装
### docker安装

1. 创建网卡  
    > docker network create elk

2. 拉去镜像
elastic更新得非常快，软件之间要求版本一致。下面拉取的版本是7.8.1。
    > docker pull elasticsearch:7.8.1  
    docker pull kibana:7.8.1  
    docker pull logstash:7.8.1  
    docker pull store/elastic/filebeat:7.8.1  
                                                   
3. 查看容器状态
    > docker ps | grep elasticsearch
             
4. 检测 elasticsearch 是否启动成功
    > curl 127.0.0.1:9200  
      >>
      {  
        "name" : "ab49020cb285",  
        "cluster_name" : "docker-cluster",  
        "cluster_uuid" : "iATljVk3S7SDk1tp7p8HPg",  
        "version" : {  
          "number" : "7.1.1",  
          "build_flavor" : "default",  
          "build_type" : "docker",  
          "build_hash" : "7a013de",  
          "build_date" : "2019-05-23T14:04:00.380842Z",  
          "build_snapshot" : false,  
          "lucene_version" : "8.0.0",  
          "minimum_wire_compatibility_version" : "6.8.0",  
          "minimum_index_compatibility_version" : "6.0.0-beta1"  
        },  
        "tagline" : "You Know, for Search"  
      }
      
5. 运行容器  

    5.1 运行 elasticsearch  
     
    > docker run -d --name elasticsearch  
        --net elk    
        -p 9200:9200  
        -p 9300:9300  
        -e "discovery.type=single-node"  
        elasticsearch:7.8.1  
      
    快捷复制       
    ```
    docker run -d --name elasticsearch --net elk -p 9200:9200 -p 9300:9300 -e "discovery.type=single-node" elasticsearch:7.8.1
    ```
        
    5.2 运行 kibana
     > docker run -d --name kibana  
         --net elk  
         -p 5601:5601  
         kibana:7.8.1
      
    快捷复制       
    ```
    docker run -d --name kibana --net elk -p 5601:5601 kibana:7.8.1
    ```
   
    5.3 运行 logstash
     > docker run -it -d -p 5044:5044 --name logstash  
       --net elk
       -v xxxxx/logstash.yml:/usr/share/logstash/config/logstash.yml (总配置)   
       -v xxxx/logstash/config/:/usr/share/logstash/conf.d/ (详细配置)  
       -v xxxx/logstash/output-logs:/usr/share/logstash/output-logs (输出的日志位置)  
       -v xxxx/logstash/logs:/usr/share/logstash/logs （把一些文件映射过去，方便测试）  
       logstash:7.8.1  
      
    快捷复制       
    ```
    docker run -it -d -p 5044:5044 --name logstash 
    --net elk
    -v E:/work/laradock/elk-stack/logstash/logstash.yml:/usr/share/logstash/config/logstash.yml 
    -v E:/work/laradock/elk-stack/logstash/config/:/usr/share/logstash/conf.d/ 
    -v E:/work/laradock/elk-stack/logstash/output-logs:/usr/share/logstash/output-logs
    -v E:/work/laradock/elk-stack/logstash/logs:/usr/share/logstash/logs
    logstash:7.8.1
    ```

    5.4 运行 filebeat
      > docker run --name filebeat --user=root -d  
       --net elk  
       --net elk--volume=xxxx/filebeat/logs:/usr/share/filebeat/logs"  
       --volume="xxx/filebeat/filebeat.docker.yml:/usr/share/filebeat/filebeat.yml:ro"  
       store/elastic/filebeat:7.8.1  
      
    快捷复制       
    ```
    docker run --name filebeat --user=root -d --net elk   
    --volume="E:/work/laradock/elk-stack/filebeat/logs:/usr/share/filebeat/logs" 
    --volume="E:/work/laradock/elk-stack/filebeat/filebeat.docker.yml:/usr/share/filebeat/filebeat.yml:ro"  
    store/elastic/filebeat:7.8.1
    ```

## 配置
     
### Filebeat
#### filebeat.docker.yml
发送到elasticsearch
```
     filebeat.inputs:
     - type: log
       enabled: true
       paths:
       - /usr/share/filebeat/logs/*.log
     output.elasticsearch:
       hosts: '${ELASTICSEARCH_HOSTS:elasticsearch:9200}'
```
发送到 logstash
```
    filebeat.inputs:
    - type: log
      enabled: true
      paths:
      - /usr/share/filebeat/logs/*.log
    output.logstash:
      hosts: ['logstash:5044']
```


## Elasticsearch
### 基本概念
 1. 近实时 Near Realtime NRT : 基于es，写入生成索引，执行搜索，分析可以达到秒级。  
 
 2. 集群 cluster : 是一个或多和 es 节点组成的集合。  

 3. 节点 node : 一个node就是一个es实例。  
 
 4. 索引 index : 等同于关系型数据库中的表,用来存储document。  
 
 5. 类型 type : 等同于mysql的表。ElasticSearch 6.x 版本废弃掉 Type 后。建议的是每个类型（业务）的数据单独放在一个索引中，简单点来说就是一个集群中有多个索引，一个索引中仅设置一种类型。这样其实回归到一般意义上的索引定义，索引定位文档。  
     
 6. 文档 document : 等同于mysql的行。  
 
 7. 分片 shard ： es可以将一个索引中的数据切分为多个分片，分布在多台服务器上进行存储，搜索，分析等操作。
  
 8. 复制 replicas ： shard可能就会丢失，因此可以为每个shard创建多个replica副本。replica可以在shard故障时提供备用服务，保证数据不丢失，多个replica还可以提升搜索操作的吞吐量和性能。最小的高可用配置，是2台服务器。
  
 9. 倒排索引 invertedindex ：反向索引又叫倒排索引，是根据文章内容中的关键字建立索引。就是关键字对应id的索引结构。

 10. 中文分词器 ik : 中文分词器。  
 
 11. 索引模版 Index templates ：针对一批大量数据存储的时候需要使用多个索引库的情况, 索引可以使用预定义的模板进行创建。  

 12. 映射 mapping : 映射像关系数据库中的表结构。每一个索引都有一个映射，它定义了索引中的每一个字段类型，以及一个索引范围内的设置。一个映射可以事先被定义(静态映射)，或者在第一次存储文档的时候自动识别(动态映射)。

 13. 来源字段 source field ： 默认情况下，你的原文档将被存储在_source这个字段中。

## laravel + Elasticsearch = Laravel Scout
 
目前流行的解决方案都是封装在基于Laravel Scout的第三方扩展包里。

1. laravel-scout-elastic  
优点 ：简单。  
缺点 : github很久没有更新了。对中文分词的操作不友好。代码的规范太限制了，自由度不够。不支持多张表。  
laravel中国社区推荐的人挺多的。800多个star。  
https://github.com/ErickTamayo/laravel-scout-elastic

2. laravel-scout-elasticsearch  
laravel中国社区站长使用过， 300多start。  
https://github.com/matchish/laravel-scout-elasticsearch  

3. scout-elasticsearch-driver  
优点 ：模型操作索引，非常好理解。github上更新频率不错。有原生操作，也支持高级用法。  
缺点 : 文档不太完善，有些问题需去github看一下。  
900多个star。推荐使用。  
https://github.com/babenkoivan/scout-elasticsearch-driver  

### Laravel Scout 文档链接
https://learnku.com/docs/laravel/7.x/scout/7516  
https://laravel.com/docs/7.x/scout  

## 应用场景
1. 日志管理
2. github的搜索
3. 商品推荐
4. 订单搜索
5. 商城中的商品搜索
