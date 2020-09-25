$ yum groupinstall "development tools"

$ yum install -y gcc gcc-c++ autoconf libjpeg libjpeg-devel libpng libpng-devel freetype \
freetype-devel libpng libpng-devel libxml2 libxml2-devel zlib zlib-devel glibc glibc-devel \
glib2 glib2-devel bzip2 bzip2-devel ncurses curl curl-devel openssl-devel gdbm-devel db4-devel \
libXpm-devel libX11-devel gd-devel gmp-devel readline-devel libxslt-devel expat-devel xmlrpc-c xmlrpc-c-devel



wget http://mirrors.sohu.com/php/php-7.0.9.tar.gz


$ tar xvf php-7.0.9.tar.gz
$ cd php-7.0.9



./configure \
--prefix=/usr/local/php \
 --with-curl \
 --with-freetype-dir \
 --with-gd \
 --with-gettext \
 --with-iconv-dir \
 --with-kerberos \
 --with-libdir=lib64 \
 --with-libxml-dir \
 --with-mysqli \
 --with-openssl \
 --with-pcre-regex \
 --with-pdo-mysql \
 --with-pdo-sqlite \
 --with-pear \
 --with-png-dir \
 --with-xmlrpc \
 --with-xsl \
 --with-zlib \
 --enable-fpm \
 --enable-bcmath \
 --enable-libxml \
 --enable-inline-optimization \
 --enable-gd-native-ttf \
 --enable-mbregex \
 --enable-mbstring \
 --enable-opcache \
 --enable-pcntl \
 --enable-shmop \
 --enable-soap \
 --enable-sockets \
 --enable-sysvsem \
 --enable-xml \
 --enable-zip
 
 
 $ make
 $ make install
 
 
 