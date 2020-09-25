系统环境：CentOS7最小化安装

软件：nginx-1.18.0.tar.gz

nginx所需软件:  
1. openssl-xxx.tar.gz（https服务）,
2. zlib-xxx.tar.gz（gzip压缩功能）,
3. pcre-xxx.tar.gz（rewrite功能）
4. perl-xxxx.tar.gz（安装openssl需要先安装perl5）

安装方式：源码编译安装

安装位置：/usr/local/nginx


下载地址：http://nginx.org/en/download.html


预安装gcc和g++  yum install -y gcc gcc-c++



### 安装perl5


wget https://www.cpan.org/src/5.0/perl-5.32.0.tar.gz
tar -xzf perl-5.32.0.tar.gz
cd perl-5.32.0
./Configure -des -D prefix=/usr/local/perl
make && make test && make install


### 安装openssl
下载地址：https://www.openssl.org/source/
wget https://www.openssl.org/source/openssl-1.1.1g.tar.gz
tar -zxfv openssl-1.1.1g.tar.gz
cd openssl-1.1.1g
./config --prefix=/usr/local/openssl --openssldir=/usr/local/openssl/conf
make && make install



### 安装pcre
下载地址： http://www.pcre.org/
https://ftp.pcre.org/pub/pcre/pcre-8.44.tar.gz
tar -zxfv pcre-8.44.tar.gz
cd pcre-8.44
./configure --prefix=/usr/local/pcre/
make && make install



### 安装zlib
wget https://sourceforge.net/projects/libpng/files/zlib/1.2.11/zlib-1.2.11.tar.gz
tar -zxfv zlib-1.2.11.tar.gz
cd zlib-1.2.11
./configure --prefix=/usr/local/zlib/
make && make install


### 添加www用户和组
groupadd nginx
useradd -g nginx nginx


### 安装nginx软件
wget http://nginx.org/download/nginx-1.18.0.tar.gz
tar -zxfv nginx-1.18.0.tar.gz
cd nginx-1.18.0


### 配置(使用openssl、pcre、zlib的源码路径)

./configure \
--user=nginx \
--group=nginx \
--prefix=/usr/local/nginx \
--with-http_ssl_module \
--with-openssl=/root/nginx/openssl-1.1.1g \ (软件包解压的位置)
--with-pcre=/root/nginx/pcre-8.44 \ (软件包解压的位置)
--with-zlib=/root/nginx/zlib-1.2.11 \ (软件包解压的位置)
--with-http_stub_status_module \
--with-threads





make && make install


可以修改配置文件: vi /usr/local/nginx/conf/nginx.conf
location ~ \.php$ {
        root            html;
        fastcgi_pass   127.0.0.1:9000;
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
        include        fastcgi_params;
    }

把下面代码打开
location ~ \.php$ {
	root           /usr/local/nginx/html;
	fastcgi_pass   127.0.0.1:9000;
	fastcgi_index  index.php;
	fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
	include        fastcgi_params;
}



firewall-cmd --zone=public --add-port=80/tcp --permanent  开启80端口

firewall-cmd --complete-reload        更新防火墙规则(立即生效)


### 设置systemctl维护命令

vi /usr/lib/systemd/system/nginx.service

[Unit]
Description=nginx
After=network.target

[Service]
Type=forking
ExecStart=/usr/local/nginx/sbin/nginx
ExecReload=/usr/local/nginx/sbin/nginx -s reload
ExecStop=/usr/local/nginx/sbin/nginx -s quit
PrivateTmp=true

[Install]
WantedBy=multi-user.target


设置开机启动： systemctl enable nginx.service


启动nginx服务: systemctl start nginx.service


查看nginx进程
ps -ef | grep nginx







测试php是否和nginx互通了

vi /usr/local/nginx/html/test.php

<?php
echo phpinfo();

然后访问 xxx.com/test.php









刚安装好nginx一个常见的问题是无法站外访问，很可能是被CentOS的防火墙把80端口拦住了，尝试执行以下命令，打开80端口：

打开80端口有两种方法

firewall-cmd方法

firewall-cmd --zone=public --add-port=80/tcp --permanent  开启80端口

firewall-cmd --zone= public --remove-port=80/tcp --permanent  关闭80端口

firewall-cmd --complete-reload        更新防火墙规则(立即生效)

systemctl restart firewalld.service  重启防火墙






iptables方法  /sbin/iptables 或者 iptables

iptables -I INPUT -p tcp --dport 80 -j ACCEPT

iptables -L INPUT --line-numbers    查看现在的规则

iptables -D INPUT 3  删除第三条

chkconfig iptables on  永久生效



这里用的是firewall-cmd方法




以下是其他的一些操作命令

停止开机启动: systemctl disable nginx.service

启动nginx服务: systemctl start nginx.service

查看服务当前状态: systemctl status nginx.service

重新启动服务: systemctl restart nginx.service

查看所有已启动的服务: systemctl list-units --type=service




### 修改 nginx 配置