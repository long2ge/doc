安装PHP依赖包

yum install -y autoconf libjpeg libjpeg-devel libpng libpng-devel freetype freetype-devel libpng libpng-devel libxml2 libxml2-devel glibc glibc-devel glib2 glib2-devel bzip2 bzip2-devel curl curl-devel gdbm-devel db4-devel libXpm-devel libX11-devel gd-devel gmp-devel readline-devel libxslt-devel expat-devel

安装Libmcrypt库

https://sourceforge.net/projects/mcrypt/files/Libmcrypt/2.5.8/libmcrypt-2.5.8.tar.gz

tar zxvf libmcrypt-2.5.8.tar.gz

cd libmcrypt-2.5.8

./configure && make && make && make install

export LD_LIBRARY_PATH=/usr/local/lib: LD_LIBRARY_PATH



安装mhash库

https://ncu.dl.sourceforge.net/project/mhash/mhash/0.9.9.9/mhash-0.9.9.9.tar.gz

./configure && make && make install



安装mcrypt库

https://ncu.dl.sourceforge.net/project/mcrypt/MCrypt/2.6.8/mcrypt-2.6.8.tar.gz

./configure && make && make install



安装php5.6

./configure --prefix=/usr/local/php \

--with-config-file-path=/usr/local/php/etc \

--with-mysql=mysqlnd \

--with-mysqli=mysqlnd \

--with-pdo-mysql=mysqlnd \

--enable-fpm \

--enable-soap \

--with-libxml-dir \

--with-openssl \

--with-mcrypt \

--with-mhash \

--with-pcre-regex \

--with-zlib \

--enable-bcmath \

--with-iconv \

--with-bz2 \

--enable-calendar \

--with-curl \

--with-cdb \

--enable-dom \

--enable-exif \

--enable-fileinfo \

--enable-filter \

--with-pcre-dir \

--enable-ftp \

--with-gd \

--with-openssl-dir \

--with-jpeg-dir \

--with-png-dir \

--with-zlib-dir \

--with-freetype-dir \

--enable-gd-native-ttf \

--enable-gd-jis-conv \

--with-gettext \

--with-gmp \

--with-mhash \

--enable-json \

--enable-mbstring \

--disable-mbregex \

--disable-mbregex-backtrack \

--with-libmbfl \

--with-onig \

--enable-pdo \

--with-pdo-mysql \

--with-zlib-dir \

--with-readline \

--enable-session \

--enable-shmop \

--enable-simplexml \

--enable-sockets \

--enable-sysvmsg \

--enable-sysvsem \

--enable-sysvshm \

--enable-wddx \

--with-libxml-dir \

--with-xsl \

--enable-zip \

--enable-mysqlnd-compression-support \

--with-pear



如果有下面这个错误

------------------------------------------------------------------------------------------

Please reinstall the libcurl distribution -easy.h should be in/include/curl/

解决方法

yum install curl curl-devel

重新configure

---------------------------------------------------------------------------------------



复制php.ini 到安装目录

cp php.ini-production /usr/local/php/etc/php.ini

在php.ini里找到date.timezone这行，把值改成PRC，如date.timezone = PRC。

如果没有这一行直接加上就好。最后重启WEB服务器与PHP即可。



把php-fpm添加到服务

cp /usr/local/php/etc/php-fpm.conf.default /usr/local/php/etc/php-fpm.conf



vi /usr/local/php/etc/php-fpm.conf

把pid的选项打开，然后填入路径，我的路径是/usr/local/php/etc/php-fpm.pid



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



设置开机启动

systemctl enable php-fpm.service 或者 systemctl enable php-fpm

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