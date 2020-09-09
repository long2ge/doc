### 源码编译安装swoole4 - 1

第一步：下载swoole4源码

命令wget https://github.com/swoole/swoole-src/archive/v4.3.1.tar.gz

第二步：解压swoole源码包

命令：tar xzvf v4.3.5.tar.gz


第三步：使用phpize在swoole源码目录下生成编译swoole扩展的configure文件

命令：/usr/local/php21/bin/phpize（这个命令是php专门用来安装扩展的）


现在看到源码文件中包含了configure文件，后续的安装就跟普通编译基本一致了。

第四步：进行swoole的编译安装过程

1、加载php配置php-config

命令：./configure --with-php-config=/usr/local/php21/bin/php-config


2、make编译源码


3、make test排错


4、make install安装编译后的文件到系统


5、编辑php.ini文件中添加一行 extension=swoole.so


6、验证swoole是否安装成功，php21 -m查看扩展中是否有swoole

### 源码编译安装swoole4 - 2
$ git clone https://github.com/swoole/swoole-src.git
        # 进入源码目录
        $ cd swoole-src
        
        $ phpize  #使用默认php
        $ /usr/local/php/bin/phpize # 若有多版本 php 存在，可使用指定路径的 phpize 
        
        # 设置编译选项
        ./configure \
        --with-php-config=/usr/local/php/bin/php-config \
        --enable-sockets \
        --enable-openssl  \
        --with-openssl-dir=/usr/local/openssl  \
        --enable-http2  \
        --enable-mysqlnd
        
        # 编译
        $ make
        
        # 安装
        $ make install
        
        
        # 请将路径改为正确的
        extension="/usr/local/php/pecl/20170718/swoole.so"
        
        $ php -m|grep swoole
        
        
### pecl 安装

php安装是要装php-pear

yum install php-pear

然后通过pear命名安装swoole

 pecl install swoole
 
配置php.ini

添加　　extension=swoole.so

查看命令　　php -m