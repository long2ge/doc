安装PHP依赖包

yum install -y gcc gcc-c++ libxml2 libxml2-devel openssl openssl-devel bzip2 bzip2-devel libcurl libcurl-devel libjpeg libjpeg-devel libpng libpng-devel freetype freetype-devel gmp gmp-devel libmcrypt libmcrypt-devel readline readline-devel libxslt libxslt-devel gd sqlite sqlite-devel net-snmp net-snmp-devel oniguruma oniguruma-devel



安装wget 

yum -y install wget


编译安装php时，如果--enable-mbstring ， 开启了mbstring扩展，需要这个正则处理库

https://pkgs.org/download/oniguruma5php
https://pkgs.org/download/oniguruma5php-devel

执行下面两句，如果失效了，参考上面两个地址获取最新的下载url

yum -y install http://rpms.remirepo.net/enterprise/7/remi/x86_64/oniguruma5php-6.9.5+rev1-2.el7.remi.x86_64.rpm
yum -y install http://rpms.remirepo.net/enterprise/7/remi/x86_64/oniguruma5php-devel-6.9.5+rev1-2.el7.remi.x86_64.rpm





如果www用户不存在，那么先添加www用户

    groupadd www

    useradd -g www www



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















安装libzip，以支持–with-zip编译选项

##下载地址  wget https://libzip.org/download/libzip-1.5.2.tar.gz  如果下载得慢，那么使用下面的地址
https://down.24kplus.com/linux/libzip-1.5.2.tar.gz

tar -xvzf libzip-1.5.2.tar.gz && cd libzip-1.5.2

    mkdir build && cd build/

	cmake -D CMAKE_BUILD_TYPE=RELEASE -D CMAKE_INSTALL_PREFIX=/usr ..


    make && make install



下载php
wget http://mirrors.sohu.com/php/php-7.4.10.tar.gz



解压
tar -zxvf php-7.4.10.tar.gz && cd  php-7.4.10


安装php7.4.10

./configure --prefix=/usr/local/php \
--with-config-file-path=/usr/local/php/etc \
--enable-fpm \
--with-fpm-user=www \
--with-fpm-group=www \
--with-mysqli \
--with-pdo-mysql \
--enable-mysqlnd \
--with-freetype \
--with-jpeg \
--with-zlib \
--with-zip \
--with-bz2 \
--enable-xml \
--disable-rpath \
--enable-bcmath \
--enable-shmop \
--enable-sysvsem \
--enable-inline-optimization \
--with-curl \
--enable-mbregex \
--enable-mbstring \
--enable-ftp \
--enable-gd \
--with-openssl \
--with-mhash \
--enable-pcntl \
--enable-sockets \
--with-xmlrpc \
--enable-soap \
--without-pear \
--with-gettext \
--disable-fileinfo \
--enable-maintainer-zts

在phh7.1时，
官方就开始建议用openssl_*系列函数代替Mcrypt_*系列的函数。



编译安装

make && make install


加入系统变量

export PATH="$PATH:/usr/local/php/bin"

让修改生效
source /etc/profile


复制php.ini 到安装目录

cp php.ini-production /usr/local/php/etc/php.ini

在php.ini里找到date.timezone这行，把值改成PRC，如date.timezone = PRC。

如果没有这一行直接加上就好。最后重启WEB服务器与PHP即可。



把php-fpm添加到服务

// 是PHP-FPM特有的配置文件
cp /usr/local/php/etc/php-fpm.conf.default /usr/local/php/etc/php-fpm.conf

// php-fpm 进程服务的扩展配置文件
cp  /usr/local/php/etc/php-fpm.d/www.conf.default /usr/local/php/etc/php-fpm.d/www.conf


vi /usr/local/php/etc/php-fpm.conf

把pid的选项打开，然后填入路径，我的路径是

/usr/local/php/etc/php-fpm.pid



chmod a+x /usr/local/php/sbin/php-fpm  给权限



vi /usr/lib/systemd/system/php-fpm.service

[Unit]
Description=php-fpm
After=network.target

[Service]
Type=forking
ExecStart=/usr/local/php/sbin/php-fpm
ExecReload=kill -USR2 `cat /usr/local/php/etc/php-fpm.pid`
ExecStop=kill -INT `cat /usr/local/php/etc/php-fpm.pid`
PrivateTmp=true

[Install]
WantedBy=multi-user.target






如果出现错误

Warning: Unit file of nginx.service changed on disk,'systemctl daemon-reload' recommended

直接systemctl daemon-reload





结束




设置开机启动

systemctl enable php-fpm.service    或者 systemctl enable php-fpm

停止开机启动

systemctl disable php-fpm.service

启动nginx服务

systemctl start php-fpm.service

查看服务当前状态

systemctl status php-fpm.service

重新启动服务

systemctl restart php-fpm.service

查看所有已启动的服务

systemctl list-units --type=service

查看进程

ps -ef | grep php-fpm

查看进程数量

ps | grep -c php-fpm

查看每个FPM的内存占用：

ps -ylC php-fpm --sort:rss

查看一下当前fastcgi进程个数

ss -napo |grep "php-fpm" | wc -l


php-fpm工作流程

php-fpm全名是PHP FastCGI进程管理器

php-fpm启动后会先读php.ini，然后再读相应的conf配置文件，conf配置可以覆盖php.ini的配置。

启动php-fpm之后，会创建一个master进程，监听9000端口（可配置），master进程又会根据fpm.conf/www.conf去创建若干子进程，子进程用于处理实际的业务。

当有客户端（比如nginx）来连接9000端口时，空闲子进程会自己去accept，如果子进程全部处于忙碌状态，新进的待accept的连接会被master放进队列里，等待fpm子进程空闲；

这个存放待accept的半连接的队列有多长，由 listen.backlog 配置。
php-fpm全局配置说明

配置里面的所有相对路径，都是相对于php的安装路径。

除了有php-fpm.conf配置文件外，通常还有其他的*.conf配置文件（也可以不要，直接在php-fpm.conf配置）用于配置进程池，不同的进程池可以用不同的用户执行，监听不同的端口，处理不同的任务；多个进程池共用一个全局配置。