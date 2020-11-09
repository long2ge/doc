make -j4  可以把项目在进行并行编译


### Linux 系统的最大进程数和最大文件打开数限制

    shell级限制
    通过ulimit -n修改，如执行命令ulimit -n 1000，则表示将当前shell的当前用户所有进程能打开的最大文件数量设置为1000.
    
    用户级限制  
    ulimit -n是设置当前shell的当前用户所有进程能打开的最大文件数量，但是一个用户可能会同时通过多个shell连接到系统，所以还有一个针对用户的限制，通过修改 /etc/security/limits.conf实现，例如，往limits.conf输入以下内容：
    root soft nofile 1000
    root hard nofile 1200
    soft nofile表示软限制，hard nofile表示硬限制，软限制要小于等于硬限制。上面两行语句表示，root用户的软限制为1000，硬限制为1200，即表示root用户能打开的最大文件数量为1000，不管它开启多少个shell。
    
    系统级限制
    修改cat /proc/sys/fs/file-max


/etc/security/limits.conf

        * soft noproc 11000
        * hard noproc 11000
        * soft nofile 4100
        * hard nofile 4100 
       说明：* 代表针对所有用户
            noproc 是代表最大进程数
            nofile 是代表最大文件打开数 
            
#### ss -nutl
    - t 代表tcp协议     
    - u 是显示UDP 
    - u -a 是显示所有UDP Sockets套接字
    - l 列出所有打开的网络连接端口
    - p 显示出那个端口对应的那个应用程序，和进程编号
    - a 显示所有
    
#### screen
    创建一个新的窗口 screen -S xxxx
    展示窗口 screen -ls 
    恢复窗口 screen -r 12865
    
#### ip
    查看本机IP地址 ip addr