mysql5.7源码编译安装

安装mysql前的准备：一、安装依赖的库： 

yum install -y  gcc-c++ ncurses-devel perl-Data-Dumperpython-devel openssl openssl-devel

二、安装cmake（因为mysql5.7的编译由cmake来实现）

安装cmake：

https://github.com/Kitware/CMake/releases/download/v3.16.2/cmake-3.16.2.tar.gz

cd cmake-3.16.2/

预编译和安装：

./bootstrap

Make && make install

三、安装boost:

下载源码包：

wget https://dl.bintray.com/boostorg/release/1.72.0/source/boost_1_72_0.zip

解压

unzip boost_1_72_0.zip

mv  boost_1_59_0  /usr/local/boots

cd /usr/local/boost

编译安装：

./bootstrap.sh

./b2

sudo ./b2 install

配置环境变量：

vim /etc/profile

BOOST_ROOT=/usr/local/boost

BOOST_LIB=/usr/local/boost/stage/lib

BOOST_INCLUDE=/usr/local/include/boost

export BOOST_ROOT BOOST_INCLUDE BOOST_LIB

source /etc/profile

四、编译安装mysql:

下载源码包：      5.7.30.tar.gz   http://mirrors.sohu.com/mysql/MySQL-5.7/mysql-5.7.30.tar.gz

wget

http://mirrors.sohu.com/mysql/MySQL-5.7/mysql-5.7.23.tar.gz

tar zxvf mysql-5.7.23.tar.gz

cd mysql-5.7.23/

我的配置

cmake. -DCMAKE_INSTALL_PREFIX=/usr/local/mysql \

-DMYSQL_DATADIR=/usr/local/mysql/data\

-DSYSCONFDIR=/etc\

-DWITH_INNOBASE_STORAGE_ENGINE=1\

-DWITH_ARCHIVE_STORAGE_ENGINE=1\

-DWITH_BLACKHOLE_STORAGE_ENGINE=1\

-DWITH_SSL=system\

-DWITH_ZLIB=system\

-DWITH_LIBWRAP=0\

-DMYSQL_UNIX_ADDR=/tmp/mysql.sock\

-DDEFAULT_CHARSET=utf8\

-DDEFAULT_COLLATION=utf8_general_ci\

-DDOWNLOAD_BOOST=1 \

-DWITH_BOOST=/usr/local/boost\

-DWITH_PARTITION_STORAGE_ENGINE=1\

-DENABLED_LOCAL_INFILE=1\

-DENABLED_PROFILING=0

备注：mysql5.7的编译需指定boost,即：DWITH_BOOST=/usr/local/boost或者-DDOWNLOAD_BOOST=1 -DWITH_BOOST=/usr/local/boost

加上-DWITH_SYSTEMD=1可以使用systemd控制mysql服务，默认是不开启systemd的。但是如果不支持，cmake的时候回出错

make && make install

make clean



2、添加mysql用户和组：

groupadd -r mysql

useradd -g mysql -r -d /usr/local/mysql/data mysql

chown -R mysql:mysql /usr/local/mysql'

mkdir /usr/local/mysql/data

chown -R mysql:mysql /usr/local/mysql/data

Cd/usr/local/mysql

初始化数据库

bin/mysqld --initialize --user=mysql

--basedir=/usr/local/mysql  --datadir=/usr/local/mysql/data

cp support-files/my-default.cnf /etc/my.cnf

[client]

port=3306

socket=/tmp/mysql.sock

default-character-set=utf8

[mysqld]

basedir=/usr/local/mysql

datadir=/usr/local/mysql/data

port=3306

server_id=1

socket =/tmp/mysql.sock

pid-file=/usr/local/mysql/data/mysql.pid

bind-address=localhost

#skip-grant-tables

..........................................................................servicemysqld start

cp support-files/mysql.server /etc/init.d/mysqld

chmod +x /etc/init.d/mysqld

chkconfig --add mysqld

chkconfig mysqld on

...................................................systemctl管理

cp /usr/local/mysql/support-files/mysql.server /etc/init.d/mysqld

chmod +x /etc/init.d/mysqld

systemctl enable mysqld

4、启动mysql服务

service mysqld start

systemctl start mysqld

5、为了直接使用，加到环境变量里

echo -e '\n\nexport PATH=/usr/local/mysql/bin:$PATH\n' >> /etc/profile && source /etc/profile

6、此时需要修改root用户密码，在初始化数据库是会随机生成一个密码

方法如下：

1、vim /etc/my.cnf    #编辑文件，找到[mysqld]，在下面添加一行skip-grant-tables

[mysqld]

skip-grant-tables

:wq!

#保存退出

service mysqld restart

#重启MySQL服务

2、进入MySQL控制台

mysql -uroot -p   #直接按回车，这时不需要输入root密码。

3、修改root密码

update

update mysql.user set authentication_string=password('123456') where user='root' and Host = 'localhost';

flush

privileges;  #刷新系统授权表

grant

all on *.* to 'root'@'localhost' identified by '123456' with grant option;

4、取消/etc/my.cnf中的skip-grant-tables

vi

/etc/my.cnf编辑文件，找到[mysqld]，删除skip-grant-tables这一行

:wq!

#保存退出

5、重启mysql

service

mysqld restart    #重启mysql，这个时候mysql的root密码已经修改为123456

至此mysql5,7安装好了

7、启用远程连接：

mysql数据库远程访问设置方法

1、修改localhost

更改"mysql"数据库里的"user"表里的"host"项，从"localhost"改成"%"

mysql>use mysql;

mysql>update user set host = '%' where

user = 'root';

mysql>select host, user from user;

mysql>FLUSH PRIVILEGES;