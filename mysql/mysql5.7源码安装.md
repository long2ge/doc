mysql5.7源码编译安装

安装mysql前的准备：一、安装依赖的库： 

yum install -y  gcc-c++ ncurses-devel perl-Data-Dumperpython-devel openssl openssl-devel

### cmake安装
    wget https://cmake.org/files/v3.18/cmake-3.18.0.tar.gz
    tar -zxvf cmake-3.18.0.tar.gz
    cd cmake-3.18.0/
    ./bootstrap       //此步可能遇到问题，见下文
    gmake             //此步需要很长时间
    gmake install
    
    /usr/local/bin/cmake --version  // 查看编译后的cmake版本
    yum remove cmake -y   // 移除原来的cmake版本
    ln -s /usr/local/bin/cmake /usr/bin/      // 新建软连接
    cmake --version     // 终端查看版本


### 安装boost (选须)

可以再编译mysql的时候设置自动下载安装

    wget http://downloads.sourceforge.net/project/boost/boost/1.59.0/boost_1_59_0.tar.gz
    
    unzip boost_1_59_0.zip
    mv  boost_1_59_0  /usr/local/boots
    cd /usr/local/boost
    ./bootstrap.sh
    ./b2
    sudo ./b2 install
    vi /etc/profile
    
    写入下面的内容
        BOOST_ROOT=/usr/local/boost
        BOOST_LIB=/usr/local/boost/stage/lib
        BOOST_INCLUDE=/usr/local/include/boost
        export BOOST_ROOT BOOST_INCLUDE BOOST_LIB
    结束
    
    source /etc/profile

### 编译安装mysql
    源码编译：          wget http://mirrors.sohu.com/mysql/MySQL-5.7/mysql-5.7.30.tar.gz  
    自带boost源码编译： wget http://mirrors.sohu.com/mysql/MySQL-5.7/mysql-boost-5.7.30.tar.gz  

    tar -zxvf mysql-5.7.30.tar.gz
    
    cd mysql-5.7.30

编译参数  
cmake . -DCMAKE_INSTALL_PREFIX=/usr/local/mysql \
-DMYSQL_DATADIR=/usr/local/mysql/data \
-DSYSCONFDIR=/etc \
-DWITH_INNOBASE_STORAGE_ENGINE=1 \
-DWITH_ARCHIVE_STORAGE_ENGINE=1 \
-DWITH_BLACKHOLE_STORAGE_ENGINE=1 \
-DWITH_SSL=system \
-DWITH_ZLIB=system \
-DWITH_LIBWRAP=0 \
-DMYSQL_UNIX_ADDR=/tmp/mysql.sock \
-DDEFAULT_CHARSET=utf8 \
-DDEFAULT_COLLATION=utf8_general_ci \
-DDOWNLOAD_BOOST=1 \
-DWITH_BOOST=/usr/local/boost \
-DWITH_PARTITION_STORAGE_ENGINE=1 \
-DENABLED_LOCAL_INFILE=1 \
-DENABLED_PROFILING=0

make && make install

    说明 ： 
    mysql5.7的编译需指定boost,
    -DWITH_BOOST=/usr/local/boost
    -DDOWNLOAD_BOOST=1
    -DWITH_BOOST=/usr/local/boost

    加上-DWITH_SYSTEMD=1可以使用systemd控制mysql服务，
    默认是不开启systemd的。但是如果不支持，cmake的时候回出错


### 添加mysql用户和组：

groupadd -r mysql
useradd -g mysql -r -d /usr/local/mysql/data mysql
chown -R mysql:mysql /usr/local/mysql
mkdir /usr/local/mysql/data
chown -R mysql:mysql /usr/local/mysql/data
cd /usr/local/mysql

### 初始化数据库

bin/mysqld --initialize --user=mysql --basedir=/usr/local/mysql  --datadir=/usr/local/mysql/data


## 写配置

vi /etc/my.cnf

[client]
port=3306
socket =/usr/local/mysql/mysql.sock
default-character-set=utf8

[mysqld]
basedir=/usr/local/mysql
datadir=/usr/local/mysql/data
port=3306
server_id=1
socket =/usr/local/mysql/mysql.sock
pid-file=/usr/local/mysql/data/mysql.pid
# bind-address=localhost
skip-grant-tables

### systemctl 管理

cp /usr/local/mysql/support-files/mysql.server /etc/init.d/mysqld

chmod +x /etc/init.d/mysqld

systemctl enable mysqld

systemctl start mysqld


### service 管理

cp support-files/mysql.server /etc/init.d/mysqld

chmod +x /etc/init.d/mysqld

chkconfig --add mysqld

chkconfig mysqld on

service mysqld start

### 加到环境变量里

echo -e '\n\nexport PATH=/usr/local/mysql/bin:$PATH\n' >> /etc/profile && source /etc/profile


### 修改数据库密码和权限

/usr/local/mysql/bin/mysql -uroot -p   #直接按回车，这时不需要输入root密码。

flush privileges;  #刷新系统授权表

update mysql.user set authentication_string=password('123456') where user='root';

grant all on *.* to 'root'@'localhost' identified by '123456' with grant option;

update mysql.user set host = '%' where user = 'root';

flush privileges;  #刷新系统授权表


### 再次修改配置文件,写上生产代码

vi /etc/my.cnf

     # 关闭跳过密码登陆
     # skip-grant-tables 

systemctl restart mysqld
 
### 打开端口

查看是否开放3306端口
firewall-cmd --list-ports

开放3306端口
firewall-cmd --zone=public --add-port=3306/tcp --permanent

刷新
firewall-cmd --reload
 
###  修改系统配置
     
     [root@localhost cmake-3.0.1]# vi /etc/security/limits.conf
     mysql soft nproc 65536
     mysql hard nproc 65536
     mysql soft nofile 65536
     mysql hard nofile 65536
     验证limit是否生效
     
     [root@mysql ~]# su - mysql
     [mysql@mysql ~]$ ulimit -a
 
### 维护命令

ps -ef | grep mysql

关闭防火墙
systemctl stop firewalld.service 

禁止防火墙开机自启
systemctl disable firewalld.service

开启防火墙
systemctl start firewalld.service 