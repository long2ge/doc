
预安装软件 : 
yum install -y gcc gcc-c++ ncurses ncurses-devel openssl openssl-devel pcre pcre-devel zlib zlib-devel bison doxygen


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



### 升级gcc - 为什么升级, 因为自带版本太低不能编译MySql8
升级到gcc 9.3。为什么升级？因为自带版本太低不能编译MySql 8 。   
  
    yum -y install centos-release-scl
    yum -y install devtoolset-9-gcc devtoolset-9-gcc-c++ devtoolset-9-binutils
    scl enable devtoolset-9 bash
    echo "source /opt/rh/devtoolset-9/enable" >>/etc/profile
    source /etc/profile


### 安装mysql

wget http://mirrors.sohu.com/mysql/MySQL-8.0/mysql-8.0.20.tar.gz

### 成功编译
cmake . -DCMAKE_INSTALL_PREFIX=/usr/local/mysql \
-DMYSQL_DATADIR=/usr/local/mysql/data \
-DSYSCONFDIR=/etc/mysql \
-DMYSQL_UNIX_ADDR=/usr/local/mysql/mysql.sock \
-DWITH_BOOST=/root/test \
-DFORCE_INSOURCE_BUILD=1 \
-DDOWNLOAD_BOOST=1 \
-DMYSQL_TCP_PORT=3306 \
-DENABLED_LOCAL_INFILE=ON \
-DWITH_INNODB_MEMCACHED=ON \
-DWITH_INNOBASE_STORAGE_ENGINE=1 \
-DWITH_FEDERATED_STORAGE_ENGINE=1 \
-DWITH_BLACKHOLE_STORAGE_ENGINE=1 \
-DWITH_ARCHIVE_STORAGE_ENGINE=1 \
-DWITHOUT_EXAMPLE_STORAGE_ENGINE=1 \
-DWITH_PERFSCHEMA_STORAGE_ENGINE=1 \
-DWITH_SSL=system \
-DDEFAULT_CHARSET=utf8mb4 \
-DDEFAULT_COLLATION=utf8mb4_general_ci \
-DWITH_EXTRA_CHARSETS=all

  

make -j8 && make install



### 说明
安装了boost的可以不需要：
-DDOWNLOAD_BOOST=1 
-DWITH_BOOST


安装位置与数据位置根据需要自定义：
-DCMAKE_INSTALL_PREFIX=
-DMYSQL_DATADIR=



### 给文件权限
groupadd -r mysql
useradd -g mysql -r -d /usr/local/mysql/data mysql
chown -R mysql:mysql /usr/local/mysql
mkdir /usr/local/mysql/data
chown -R mysql:mysql /usr/local/mysql/data
cd /usr/local/mysql

### 初始化数据库

这个命令和mysql5.7之前的命令不一样了，之前命令是：bin/mysql_install_db --user=mysql，
但是之后的版本已经被mysqld --initialize替代 

bin/mysqld --initialize --user=mysql --basedir=/usr/local/mysql --datadir=/usr/local/mysql/data 

### 配置文件

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
# bind-address=0.0.0.0
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
 
 mysql -uroot -p
 
 flush privileges;
 
 use mysql;
 
 update user set host='%' where user='root';
 
 alter user 'root'@'%' identified by '123456' password expire never; // 不知道能不能执行
 
 alter user root@'%' identified with mysql_native_password by '123456';  // 添加远程登陆用户
 
 grant all privileges on *.* to root@'%' with grant option; // 为远程用户分配权限
 
 flush privileges;
 
 
 
 测试
 
CREATE USER aaa@'%' IDENTIFIED BY '123456!';
GRANT ALL ON *.* TO 'aaa'@'%';
ALTER USER 'aaa'@'%' IDENTIFIED WITH mysql_native_password BY '123456!';
FLUSH PRIVILEGES;

 
### 再次修改配置文件,写上生产代码

vi /etc/my.cnf

     # 关闭跳过密码登陆
     # skip-grant-tables 
 
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