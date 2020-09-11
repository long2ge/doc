make -j4  可以把项目在进行并行编译


### Linux 系统的最大进程数和最大文件打开数限制

/etc/security/limits.conf

        * soft noproc 11000
        * hard noproc 11000
        * soft nofile 4100
        * hard nofile 4100 
       说明：* 代表针对所有用户
            noproc 是代表最大进程数
            nofile 是代表最大文件打开数 