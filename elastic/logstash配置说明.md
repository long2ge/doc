
### Logstash 配置


  
logstash.yml文件包括以下设置，如果你使用的是X-Pack，请参阅Logstash中的X-Pack设置。  
  


|  设置   | 描述  | 默认值  |
| :-----: | :----: | :----: |
| node.name  | 节点的描述性名称 | 机器的主机名  |
| path.data  | Logstash及其插件用于任何持久需求的目录 | LOGSTASH_HOME/data    |
| pipeline.id  | 管道的ID | main    |
| pipeline.workers  | 将并行执行管道的过滤和输出阶段的工人数量，如果你发现事件正在备份，或者CPU没有饱和，请考虑增加这个,更好地利用机器处理能力	   | 主机CPU核心的数量    |
| pipeline.batch.size  | 在尝试执行过滤器和输出之前，单个工作线程将从输入中收集的最大事件数，更大的批处理大小通常更高效，但代价是增加内存开销，你可能需要增加jvm.options配置文件中的JVM堆空间。	 | 125    |
| pipeline.batch.delay  | 当创建管道事件批处理时，在向管道工作人员发送一个较小的批处理之前，等待每个事件的时间为多少毫秒 | 50    |
| pipeline.unsafe_shutdown  | 当设置为true时，即使内存中仍然存在游离事件，也会在关闭期间强制Logstash退出，默认情况下，Logstash将拒绝退出，直到所有接收到的事件都被推送到输出，启用此选项可能导致关闭期间的数据丢 | false    |
| path.config  | 主管道的Logstash配置路径，如果指定目录或通配符，配置文件将按字母顺序从目录中读取 | 特定于平台的，请参阅Logstash目录布局     |
| config.string  | 包含要用于主管道的管道配置的字符串，使用与配置文件相同的语法 | None    |
| config.test_and_exit  | 当设置为true时，检查配置是否有效，然后退出，注意，在此设置中没有检查grok模式的正确性，Logstash可以从一个目录中读取多个配置文件，如果你把这个设置和log.level: debug结合起来，Logstash将对合并后的配置文件进行日志记录，并用它来自的源文件注解每个配置块 | false    |
| config.reload.automatic  | 当设置为true时，定期检查配置是否已更改，并在更改配置时重新加载配置，这也可以通过SIGHUP信号手动触发 | false    |
| config.reload.interval  | Logstash多久检查一次配置文件以查看更改 | 3s    |
| config.debug  | 当设置为true时，将完整编译的配置显示为debug日志消息，你还必须设置log.level: debug，警告：日志消息将包含传递给插件配置的任意密码选项，可能会导致明文密码出现在日志中！	 | false    |
| config.support_escapes  | 当设置为true时，引号中的字符串将处理以下转义序列：\n变成文字换行符（ASCII 10），\r变成文字回车（ASCII 13），\t变成文字制表符（ASCII 9），\\变成字面反斜杠\，\"变成一个文字双引号，\'变成文字引号 | false    |
| modules  | 当配置时，modules必须位于上表中描述的嵌套YAML结构中 | None    |
| queue.type  | 用于事件缓冲的内部队列模型，为基于内存中的遗留队列指定memory，或者persisted基于磁盘的ACKed队列（持久队列）	 | memory    |
| path.queue  | 启用持久队列时存储数据文件的目录路径（queue.type: persisted） | path.data/queue    |
| queue.page_capacity  | 启用持久队列时使用的页面数据文件的大小（queue.type: persisted），队列数据由分隔成页面的仅追加的数据文件组成 | 64mb    |
| queue.max_events  | 启用持久队列时队列中未读事件的最大数量（queue.type: persisted） | 0（无限）    |
| queue.max_bytes  | 队列的总容量（字节数），确保磁盘驱动器的容量大于这里指定的值，如果queue.max_events和queue.max_bytes都指定，Logstash使用最先达到的任何标准	 | 1024mb（1g）    |
| queue.checkpoint.acks  | 当启用持久队列时，在强制执行检查点之前的最大ACKed事件数（queue.type: persisted），指定queue.checkpoint.acks: 0设置此值为无限制 | 1024    |
| queue.checkpoint.writes  | 启用持久队列时强制执行检查点之前的最大写入事件数（queue.type: persisted），指定queue.checkpoint.writes: 0设置此值为无限制 | 1024    |
| queue.drain  | 启用时，Logstash会一直等到持久队列耗尽后才关闭 | false    |
| dead_letter_queue.enable  | 标记指示Logstash以插件支持的DLQ特性 | false    |
| dead_letter_queue.max_bytes  | 每个dead letter队列的最大大小，如果条目将增加dead letter队列的大小，超过此设置，则删除条目 | 1024mb    |
| path.dead_letter_queue  | 存储dead letter队列数据文件的目录路径 | path.data/dead_letter_queue    |
| http.host  | 指标REST端点的绑定地址 | "127.0.0.1"    |
| http.port  | 指标REST端点的绑定端口 | 9600    |
| log.level  | 日志级别，有效的选项是：fatal、error、warn、info、debug、trace | info    |
| log.format  | 日志格式，设置为json日志以JSON格式，或plain使用Object#.inspect | plain    |
| path.logs  | Logstash将其日志写到的目录 | LOGSTASH_HOME/logs    |
| path.plugins  | 哪里可以找到自定义插件，你可以多次指定此设置以包含多个路径，插件应该在特定的目录层次结构中：PATH/logstash/TYPE/NAME.rb，TYPE是inputs、filters、outputs或codecs，NAME是插件的名称	 | 特定于平台的，请参阅Logstash目录布局    |



#### logstash.yml
    path.config: /usr/share/logstash/conf.d/*.conf
    path.logs: /usr/share/logstash/output-logs
    
    
    
#### test.conf
    # 读取的文件
      input {
         file {
             path => ["/usr/share/logstash/logs/*"]
             type => "test-info"
             start_position => "beginning"
         }
         file {
             path => ["/usr/share/logstash/logs/test.error.log"]
             type => "test-error"
             start_position => "beginning"
         }
    	 beats {
    		 port => 5044
             codec => "json"
         }
         tcp {
             port => 10086
         }
      }
    # 过滤
      filter {
          date {
             match => ["timestamp","yyyy-MM-dd HH:mm:ss"]
             remove_field => "timestamp"
          }
          mutate { 
              add_field => { "show" => "This data will be in the output" } // 添加字段
              replace => {"field1"=>"v1"}  // 更新字段。如果字段不存在，则不做处理
          }
      }
    # 输出
     output {
         if [type] == "test-info" {
    		elasticsearch {
    			hosts  => ["elasticsearch:9200"]
                index  => "certification-info-%{+YYYY.MM.dd}"
            }
         } else {
    		elasticsearch { hosts => ["elasticsearch:9200"] }
    	    stdout { codec => rubydebug }
    	 }
     }
     
     
## input 属性说明
     
file   从文件读取数据

    input {
    
        # path  可以用/var/log/*.log,/var/log/**/*.log，如果是/var/log则是/var/log/*.log
        # type 通用选项. 用于激活过滤器
        # start_position 选择logstash开始读取文件的位置，begining或者end。

        file {
            path => ["/usr/share/logstash/logs/*"]
            type => "test-info"
            start_position => "beginning"
        }
        
    }
    
    
beats   从 filebeat 中读取数据

    input {
         beats {
             port => 5044
              codec => "json"
          }
    }
           
          
tcp   从 tcp 中读取数据
              
    input {
          tcp {
              port => 10086
          }
    }
           
           
syslog   从 syslog 中读取数据
    
        input {
            syslog{
                port =>"514" 
                type => "syslog"
            }   
        }   
    
    
kafka   从 kafka 中读取数据
           
       input {
       
          # bootstrap_servers 用于建立群集初始连接的Kafka实例的URL列表。
          # topics  要订阅的主题列表，kafka topics
          # group_id 消费者所属组的标识符，默认为logstash。kafka中一个主题的消息将通过相同的方式分发到Logstash的group_id
          # codec 通用选项，用于输入数据的编解码器。
                      
           kafka {
               bootstrap_servers=> "kafka01:9092,kafka02:9092,kafka03:9092"
               topics => ["access_log"]
               group_id => "logstash-file"
               codec => "json"
           }
        } 

           
## filter 属性说明


|  设置   | 描述  |
| :-----: | :----: |
| date  | 日期解析  解析字段中的日期，然后转存到@timestam  |
| grok  | 解析文本并构造 。把非结构化日志数据通过正则解析成结构化和可查询化官方提供了很多正则的grok pattern可以直接使用: https://github.com/logstash-plugins/logstash-patterns-core/blob/master/patterns |
| mutate  | 对字段做处理 重命名、删除、替换和修改字段 |
| mutate.covert  | 类型转换。类型包括：integer，float，integer_eu，float_eu，string和boolean   |  
| mutate.split  | 使用分隔符把字符串分割成数组     |  
| mutate.merge  | 合并字段  。数组和字符串 ，字符串和字符串   |  
| mutate.rename  | 对字段重命名    |  
| mutate.remove_field  | 用分隔符连接数组，如果不是数组则不做处理     |  
| mutate.gsub  | 用正则或者字符串替换字段值。仅对字符串有效   |  
| mutate.update  | 更新字段。如果字段不存在，则不做处理   |  
| mutate.replace  | 更新字段。如果字段不存在，则创建     |   
| ruby  | ruby插件可以执行任意Ruby代码     |   
| urldecode  | 用于解码被编码的字段,可以解决URL中 中文乱码的问题      |   
| kv  | 通过指定分隔符将字符串分割成key/value      |   
| useragent  | 添加有关用户代理(如系列,操作系统,版本和设备)的信息      |   
 
       
kv 通过指定分隔符将字符串分割成key/value

    kv{
        prefix => "url_"   #给分割后的key加前缀  
        target => "url_ags"    #将分割后的key-value放入指定字段  
        source => "message"   #要分割的字段  
        field_split => "&"    #指定分隔符  
        remove_field => "message"  
    }  
    
useragent 添加有关用户代理(如系列,操作系统,版本和设备)的信息

    if语句，只有在agent字段不为空时才会使用该插件  
    
    source 为必填设置,目标字段  
    
    target 将useragent信息配置到ua字段中。如果不指定将存储在根目录中  

    if [agent] != "-" {  
        useragent {  
            source => "agent"  
            target => "ua"  
            remove_field => "agent"  
        }  
    }
    

  

### filter ruby demo

filter{

    # field :指定urldecode过滤器要转码的字段,默认值是"message"
    # charset(缺省): 指定过滤器使用的编码.默认UTF-8

    urldecode{
        field => "message"
    }
    
    ruby {
        init => "@kname = ['url_path','url_arg']"
        code => " 
            new_event = LogStash::Event.new(Hash[@kname.zip(event.get('message').split('?'))]) 
            event.append(new_event)"
    }
    
    if [url_arg]{
        kv{
            source => "url_arg"
            field_split => "&"
            target => "url_args"
            remove_field => ["url_arg","message"]
        }
    }
    
}

### filter mutate demo

    filter{
        mutate{
            add_field => {"field1"=>"value1"}
        }
        mutate{ 
              split => {"message"=>"."}   #把message字段按照.分割
        }
        mutate{
            merge => {"message"=>"field1"}   #将filed1字段加入到message字段
        }
        mutate{
            rename => {"message"=>"info"}
        }
        mutate {
            remove_field => ["message","datetime"]
        }
        mutate{
            join => {"message"=>","}
        }
        mutate{
            gsub => ["message","/","_"]
        }
        mutate{
            update => {"field2"=>"v2"}
        }     
        mutate{
            replace => {"field2"=>"v2"}
        }
    }

### filter date demo

    date{
        match => ["raw_datetime","YYYY-MM-dd HH:mm:ss,SSS"]
        remove_field =>["raw_datetime"]
    }

### filter grok demo

    grok {
        match => ["message", "%{IP:clientip} - %{USER:user} \[%{HTTPDATE:raw_datetime}\] \"(?:%{WORD:verb} %{URIPATHPARAM:request} HTTP/%{NUMBER:httpversion})\" (?:\"%{DATA:body}\" )?(?:\"%{DATA:cookie}\" )?%{NUMBER:response} (?:%{NUMBER:bytes:int}|-) \"%{DATA:referrer}\" \"%{DATA:agent}\" (?:(%{IP:proxy},? ?)*|-|unknown) (?:%{DATA:upstream_addr} |)%{NUMBER:request_time:float} (?:%{NUMBER:upstream_time:float}|-)"]
        match => ["message", "%{IP:clientip} - %{USER:user} \[%{HTTPDATE:raw_datetime}\] \"(?:%{WORD:verb} %{URI:request} HTTP/%{NUMBER:httpversion})\" (?:\"%{DATA:body}\" )?(?:\"%{DATA:cookie}\" )?%{NUMBER:response} (?:%{NUMBER:bytes:int}|-) \"%{DATA:referrer}\" \"%{DATA:agent}\" (?:(%{IP:proxy},? ?)*|-|unknown) (?:%{DATA:upstream_addr} |)%{NUMBER:request_time:float} (?:%{NUMBER:upstream_time:float}|-)"]       
    }


## output 属性说明

codec  本质上是流过滤器    

file   将事件写入文件  

    output {  
        file {  
           path => "/data/logstash/%{host}/{application}
           codec => line { format => "%{message}"} }
        }

        stdout {  
            codec => "rubydebug" //  标准输出。将事件输出到屏幕上
        }
    }
    
kafka  将事件发送到kafka

    output {  
        kafka{   
            bootstrap_servers => "localhost:9092"
            topic_id => "test_topic"   // 必需的设置。生成消息的主题
        }
    }
    
elasticseach  在es中存储日志

    output {  
        elasticsearch {  
            hosts => "localhost:9200"
            index => "nginx-access-log-%{+YYYY.MM.dd}"   // index 事件写入的索引
        }
    }


## logstash 比较运算符  

　　等于:   ==, !=, <, >, <=, >=  
　　正则:   =~, !~ (checks a pattern on the right against a string value on the left)  
　　包含关系:  in, not in  
　　支持的布尔运算符：and, or, nand, xor  
　　支持的一元运算符: !  