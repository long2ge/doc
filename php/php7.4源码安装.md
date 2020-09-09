### php7.4源码编译安装之生产篇

    安装扩展包并更新系统内核：
    
    $ yum install epel-release -y
    
    $ yum update
    
    安装php依赖组件（包含Nginx依赖）：
    
    $ yum -y install wget vim pcre pcre-devel openssl openssl-devel libicu-devel gcc gcc-c++ autoconf libjpeg libjpeg-devel libpng libpng-devel freetype freetype-devel libxml2 libxml2-devel zlib zlib-devel glibc glibc-devel glib2 glib2-devel ncurses ncurses-devel curl curl-devel krb5-devel libidn libidn-devel openldap openldap-devel nss_ldap jemalloc-devel cmake boost-devel bison automake libevent libevent-devel gd gd-devel libtool* libmcrypt libmcrypt-devel mcrypt mhash libxslt libxslt-devel readline readline-devel gmp gmp-devel libcurl libcurl-devel openjpeg-devel
    
    创建用户和组，并下载php安装包解压：
    
    $ cd /tmp
    
    $ groupadd www
    
    $ useradd -g www www
    
    $ wget https://www.php.net/distributions/php-7.4.3.tar.gz
    
    $ tar xvf php-7.4.3.tar.gz
    
    $ cd php-7.4.3
    
    设置变量并开始源码编译：
    
    $ cp -frp /usr/lib64/libldap* /usr/lib/
    
    ./configure --prefix=/usr/local/php \
    
    --with-config-file-path=/usr/local/php/etc \
    
    --enable-fpm \
    
    --with-fpm-user=www \
    
    --with-fpm-group=www \
    
    --with-curl \
    
    --with-freetype-dir \
    
    --enable-gd \
    
    --with-gettext \
    
    --with-iconv-dir \
    
    --with-kerberos \
    
    --with-libdir=lib64 \
    
    --with-libxml-dir \
    
    --with-mysqli \
    
    --with-openssl \
    
    --with-pcre-regex \
    
    --enable-mysqlnd \
    
    --with-mysqli=mysqlnd \
    
    --with-pdo-mysql=mysqlnd \
    
    --enable-mysqlnd-compression-support \
    
    --with-pdo-sqlite \
    
    --with-pear \
    
    --with-png-dir \
    
    --with-jpeg-dir \
    
    --with-xmlrpc \
    
    --with-xsl \
    
    --with-zlib \
    
    --with-bz2 \
    
    --with-mhash \
    
    --enable-fpm \
    
    --enable-bcmath \
    
    --enable-libxml \
    
    --enable-inline-optimization \
    
    --enable-mbregex \
    
    --enable-mbstring \
    
    --enable-opcache \
    
    --enable-pcntl \
    
    --enable-shmop \
    
    --enable-soap \
    
    --enable-sockets \
    
    --enable-sysvsem \
    
    --enable-sysvshm \
    
    --enable-xml \
    
    --enable-zip
    
    =======所遇问题=========
    
    这里会提示 configure: error: Please reinstall the libzip distribution，我们需要溢出libzip,手动安装最新版本,
    
    先编译安装最新版cmake
    
    cd /usr/local/src
    
    wget https://github.com/Kitware/CMake/releases/download/v3.14.3/cmake-3.14.3.tar.gz
    
    tar -zxvf cmake-3.14.3.tar.gz
    
    cd cmake-3.14.3
    
    ./bootstrap
    
    make && make install
    
    再编译安装libzip （下载慢，就去github上下载）
    
    yum remove libzip -y
    
    cd /usr/local/src
    
    wget https://libzip.org/download/libzip-1.5.2.tar.gz  或者 github下载 （https://github.com/nih-at/libzip/releases）
    
    tar -zxvf libzip-1.5.2.tar.gz
    
    cd libzip-1.5.2
    
    mkdir build
    
    cd build
    
    cmake ..
    
    make && make install
    
    再次编译php7.4,继续报错 error: off_t undefined; check your library configuration
    
    执行以下命令
    
    vi /etc/ld.so.conf
    
    #添加如下几行
    
    /usr/local/lib64
    
    /usr/local/lib
    
    /usr/lib
    
    /usr/lib64
    
    #保存退出
    
    :wq
    
    ldconfig -v # 使之生效
    
    再次编译php7.4,继续报错 error：Please reinstall the BZip2 distribution
    
    可以直接用命令进行安装,也可以下载源码进行安装 。
    
    yum install bzip2 bzip2-devel
    
    开始安装：
    
    $ make -j 4 && make install
    
    完成安装后配置php.ini文件：
    
    $ cp php.ini-development /usr/local/php/etc/php.ini
    
    $ cp /usr/local/php/etc/php-fpm.conf.default /usr/local/php/etc/php-fpm.conf
    
    $ cp /usr/local/php/etc/php-fpm.d/www.conf.default /usr/local/php/etc/php-fpm.d/www.conf
    
    修改 php.ini 相关参数：
    
    $ vim /usr/local/php/etc/php.ini
    
    expose_php = Off
    
    short_open_tag = ON
    
    max_execution_time = 300
    
    max_input_time = 300
    
    memory_limit = 128M
    
    post_max_size = 32M
    
    date.timezone = Asia/Shanghai
    
    extension = ldap.so
    
    设置 OPcache 缓存：
    
    [opcache]
    
    zend_extension=opcache.so
    
    opcache.memory_consumption=128
    
    opcache.interned_strings_buffer=8
    
    opcache.max_accelerated_files=4000
    
    opcache.revalidate_freq=60
    
    opcache.fast_shutdown=1
    
    opcache.enable_cli=1
    
    安装启动文件 或 安装服务启动
    
    install -v -m755 ./sapi/fpm/init.d.php-fpm  /etc/init.d/php-fpm
    
    /etc/init.d/php-fpm start
    
    把systemctl文件加入开机启动服务
    
    cp sapi/fpm/php-fpm.service /usr/lib/systemd/system/php-fpm.service
    
    systemctl start php-fpm.service
    
    systemctl enable php-fpm.service