## 搭建 canal 环境

#### 1. 打开mysql二进制配置

    [mysqld]
    log-bin=mysql-bin # 开启 binlog
    binlog-format=ROW # 选择 ROW 模式
    server_id=1 # 配置 MySQL replaction 需要定义，不要和 canal 的 slaveId 重复

    // 重启 mysql 配置才能生效
    systemctl restart mysql

#### 2. 在mysql创建canal账号，给同步权限

    FLUSH PRIVILEGES;
    CREATE USER canal IDENTIFIED BY 'canal';  
    GRANT SELECT, REPLICATION SLAVE, REPLICATION CLIENT ON *.* TO 'canal'@'%';
    -- GRANT ALL PRIVILEGES ON *.* TO 'canal'@'%' ;
    FLUSH PRIVILEGES;

#### 3. 安装 canal.deployer 软件( canal 的 service 端 )

    下载
    wget https://github.com/alibaba/canal/releases/download/canal-1.1.4/canal.deployer-1.1.4.tar.gz
    
    解压    
    tar -zxvf canal.deployer-1.1.4.tar.gz
    
    cd  canal.deployer-1.1.4
    
    修改配置
    vi conf/example/instance.properties

#### 4. 修改 canal 配置

    # mysql serverId
    canal.instance.mysql.slaveId = 1
    
    # position info
    canal.instance.master.address=127.0.0.1:3306
    
    # username/password
    canal.instance.dbUsername=canal
    canal.instance.dbPassword=canal
    canal.instance.connectionCharset = UTF-8


#### 5. 维护 canal 命令

    启动canal
    sh bin/startup.sh
    
    停止canal
    sh bin/stop.sh
    
    查看状态
    ps -ef | grep canal


#### 6. 下载 php-canal ( canal 客户端 ), 写同步代码
        composer require xingwenge/canal_php
    或者
        git clone https://github.com/xingwenge/canal-php.git
        cd canal-php
        composer update

#### 7 测试

    1. 运行 php /canal-php/src/sample/client.php
    
    2. 去mysql 执行 新增， 更新，删除命令就可以在执行client.php的面板上看到效果了

#### docker 搭建

    docker pull canal/canal-server:v1.1.4
    
    docker run --name canal-server \
    -e canal.instance.master.address=127.0.0.1:3306 \
    -e canal.instance.dbUsername=canal \
    -e canal.instance.dbPassword=canal \
    -p 998:11111 \
    -d canal/canal-server:v1.1.4

#### 模块说明
    canal.adapter    canal 1.1.1版本之后, 增加客户端数据落地的适配及启动功能
    canal.example     例子
    canal.admin       引入canal-admin工程，支持面向WebUI的canal管理能力
    canal.deployer   服务端
    canal-canal     全家桶 代码





