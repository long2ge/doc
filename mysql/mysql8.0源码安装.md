

yum install -y gcc gcc-c++ ncurses ncurses-devel openssl openssl-devel pcre pcre-devel zlib zlib-devel  bison doxygen 


一、cmake安装
1、下载包
        wget https://cmake.org/files/v3.18/cmake-3.18.0.tar.gz
2、解包
        tar -zxvf cmake-3.18.0.tar.gz
3、编译/安装
        cd cmake-3.18.0/
        ./bootstrap       //此步可能遇到问题，见下文
        gmake             //此步需要很长时间
        gmake install
4、查看编译后的cmake版本
        /usr/local/bin/cmake --version
5、移除原来的cmake版本
        yum remove cmake -y
6、新建软连接
        ln -s /usr/local/bin/cmake /usr/bin/
7、终端查看版本
        cmake --version
出现版本表示成功！cmake编译完成



升级gcc, 为什么升级, 因为自带版本太低不能编译MySql8
yum升级gcc
升级到gcc 9.3
yum -y install centos-release-scl
yum -y install devtoolset-9-gcc devtoolset-9-gcc-c++ devtoolset-9-binutils
scl enable devtoolset-9 bash
echo "source /opt/rh/devtoolset-9/enable" >>/etc/profile
source /etc/profile




wget http://mirrors.sohu.com/mysql/MySQL-8.0/mysql-8.0.20.tar.gz



cmake .. -DCMAKE_INSTALL_PREFIX=/usr/local/mysql \
-DDEFAULT_CHARSET=utf8 \
-DDEFAULT_COLLATION=utf8_general_ci \
-DENABLED_LOCAL_INFILE=ON \
-DWITH_INNODB_MEMCACHED=ON \
-DWITH_SSL=system \
-DWITH_INNOBASE_STORAGE_ENGINE=1 \
-DWITH_FEDERATED_STORAGE_ENGINE=1 \
-DWITH_BLACKHOLE_STORAGE_ENGINE=1 \
-DWITH_ARCHIVE_STORAGE_ENGINE=1 \
-DWITHOUT_EXAMPLE_STORAGE_ENGINE=1 \
-DWITH_PERFSCHEMA_STORAGE_ENGINE=1 \
-DCOMPILATION_COMMENT="zsd edition" \
-DDOWNLOAD_BOOST=1 \
-DWITH_BOOST=/tmp \
-DMYSQL_UNIX_ADDR=/data/mysqldata/3306/mysql.sock \
-DSYSCONFDIR=/data/mysqldata/3306 > /data/software/mysql-8.0.11/Zdebug/mysql_cmake80.log 2>&1




安装了boost的可以不需要：

-DDOWNLOAD_BOOST=1 
-DWITH_BOOST
1
2
安装位置与数据位置根据需要自定义：

-DCMAKE_INSTALL_PREFIX=
-DMYSQL_DATADIR=

cmake .. \
-DDOWNLOAD_BOOST=1 \
-DWITH_BOOST=. \
-DDEFAULT_CHARSET=utf8 \
-DDEFAULT_COLLATION=utf8_general_ci \
-DENABLED_LOCAL_INFILE=ON \
-DWITH_SSL=system \
-DCMAKE_INSTALL_PREFIX=/usr/local/mysql/server \
-DMYSQL_DATADIR=/usr/local/mysql/data \
-DMYSQL_TCP_PORT=3306 \


make -j 12
make install



5.创建mysql用户&并修改相关文件

[root@mysql mysql]# groupadd mysql
[root@mysql mysql]# useradd -g mysql mysql


6.设置用户操作系统资源的限制

[root@localhost cmake-3.0.1]# vi /etc/security/limits.conf
mysql soft nproc 65536
mysql hard nproc 65536
mysql soft nofile 65536
mysql hard nofile 65536
验证limit是否生效

[root@mysql ~]# su - mysql
[mysql@mysql ~]$ ulimit -a


7.创建MySQL数据目录及赋予相应权限

#cd /data/
#mkdir -p /data/mysqldata/{3306/{data,tmp,binlog,innodb_ts,innodb_log},backup,scripts}
#chown -R mysql:mysql mysqldata
#su - mysql



sudo useradd -r -g mysql -s /bin/false mysql
5.2 修改数据目录所有者与权限
数据目录根据需要修改。

sudo chown mysql:mysql /usr/local/mysql/data
sudo chmod 777 /usr/local/mysql/data
这里官网的文档写的是750权限，但是后面会出现不可写错误，755也不行，所以直接改成了777。



8.配置my.cnf文件


9.初始化MySQL数据库

$/usr/local/mysql/bin/mysqld --defaults-file=/data/mysqldata/3306/my.cnf --initialize --user=mysql
10.启动mysql服务

$/usr/local/mysql/bin/mysqld_safe --defaults-file=/data/mysqldata/3306/my.cnf --user=mysql &






















MYSQL8.0 源码安装调试环境

环境
CentOS 7.6 64位

下载源码文件
下载地址
https://downloads.mysql.com/archives/community/

下载方式
Product Version:8.0.18

Operating System: Source Code

OS Version:Generic Linux (Architecture Independent)

安装
创建用户和组（root用户执行）
/usr/sbin/groupadd mysql

/usr/sbin/useradd -g mysql mysql

解压源码文件（root用户授权后mysql用户执行）
tar xvfz mysql-8.0.19.tar.gz

安装必要软件（root用户执行）
GCC 5.4
下载
http://ftp.tsukuba.wide.ad.jp/software/gcc/releases/gcc-5.4.0/gcc-5.4.0.tar.gz

安装
yum install gcc //gcc安装前，需要先安装一个C compiler ，所以需要先借助yum安装一个gcc

yum install gmp gmp-devel zip mpfr gcc-c++ libstdc++-devel mpfr-devel libmpc libmpc-devel //gcc make时的依赖包

tar xvfz gcc-5.4.0.tar.gz

cd gcc-5.4.0

./configure --disable-multilib

make

make install

CMAKE
下载
https://github.com/Kitware/CMake/releases/download/v3.17.0/cmake-3.17.0.tar.gz

安装
tar xvfz cmake-3.17.0.tar.gz

yum install openssl-devel

cd cmake-3.17.0

./bootstrap

make

make install

Boost
下载
https://dl.bintray.com/boostorg/release/1.70.0/source/boost_1_70_0.tar.gz

安装
tar xvfz boost_1_70_0.tar.gz

cd boost_1_70_0

./bootstrap.sh

./b2 install

ncurses
下载
ftp://ftp.gnu.org/gnu/ncurses/ncurses-6.2.tar.gz

安装
tar xvfz ncurses-6.2.tar.gz

cd ncurses-6.2

./configure

make

make install

编译安装MYSQL（ROOT用户执行）
rpm -e gcc-c++-4.8.5-39.el7.x86_64 //卸载之前yum安装的gcc-c++

rpm -e gcc-4.8.5-39.el7.x86_64 //卸载之前yum安装的gcc

chmod 775 /usr/local

cd mysql-8.0.19

mkdir blddebug

cd blddebug

cmake .. -DDOWNLOAD_BOOST=1 -DWITH_BOOST=/opt/boost_1_70_0 -DCMAKE_BUILD_TYPE=Debug

ln -sf /usr/local/lib64/libstdc++.so.6 /lib64/libstdc++.so.6

ln -sf /usr/local/lib64/libstdc++.so.6 /usr/lib64/libstdc++.so.6

make

make install

cd /usr/local/mysql

mkdir mysql-files

chown mysql:mysql mysql-files

chmod 750 mysql-files

bin/mysqld --initialize --user=mysql

bin/mysql_ssl_rsa_setup

启动MYSQL
mkdir -p /var/log/mariadb/
chmod 775 /var/log/mariadb/
chown mysql:mysql /var/log/mariadb/

mkdir -p /var/run/mariadb/
chmod 775 /var/run/mariadb
chown mysql:mysql /var/run/mariadb

bin/mysqld_safe --user=mysql &

[root@mysql8 mysql]# ps -ef | grep mysql
root 4306 13508 0 22:36 pts/0 00:00:00 /bin/sh bin/mysqld_safe --user=mysql
mysql 4441 4306 23 22:36 pts/0 00:00:02 /usr/local/mysql/bin/mysqld --basedir=/usr/local/mysql --datadir=/var/lib/mysql --plugin-dir=/usr/local/mysql/lib/plugin --user=mysql --log-error=/var/log/mariadb/mariadb.log --pid-file=/var/run/mariadb/mariadb.pid --socket=/var/lib/mysql/mysql.sock
root 4534 13508 0 22:36 pts/0 00:00:00 grep --color=auto mysql