


使用 Docker 仓库进行安装
sudo yum install -y yum-utils device-mapper-persistent-data lvm2


使用以下命令来设置稳定的仓库。
sudo yum-config-manager --add-repo https://download.docker.com/linux/centos/docker-ce.repo


安装 Docker Engine-Community

sudo yum install docker-ce docker-ce-cli containerd.io





 docker run ubuntu:15.10 /bin/echo "Hello world"
 
 ubuntu:15.10 指定要运行的镜像
 /bin/echo "Hello world": 在启动的容器里执行的命令
 
 
 
 运行交互式的容器
 
 docker run -i -t ubuntu:15.10 /bin/bash
 
 -t: 在新容器内指定一个伪终端或终端。

 -i: 允许你对容器内的标准输入 (STDIN) 进行交互。
 
 
 
 启动容器（后台模式）
 docker run -d ubuntu:15.10 /bin/sh -c "while true; do echo hello world; sleep 1; done"
 
 确认容器有在运行，可以通过 docker ps 来查看：
 
 
 STATUS: 容器状态。

状态有7种：

created（已创建）
restarting（重启中）
running（运行中）
removing（迁移中）
paused（暂停）
exited（停止）
dead（死亡）
 
 
 
 
 查看容器内的标准输出：
 docker logs 2b1b7a428627
 
 
 
 
 
 停止容器
 docker stop mysql-test/2b1b7a428627
 
 
 
 
 docker stats 命令用来返回运行中的容器的实时数据流
 
 
 
 
 获取镜像
 
 如果我们本地没有 ubuntu 镜像，我们可以使用 docker pull 命令来载入 ubuntu 镜像：
 docker pull ubuntu
 
 
 
 
 
 启动容器
 以下命令使用 ubuntu 镜像启动一个容器，参数为以命令行模式进入该容器：
 docker run -it ubuntu /bin/bash
 
 
 -i: 交互式操作。
-t: 终端。
ubuntu: ubuntu 镜像。
/bin/bash：放在镜像名后的是命令，这里我们希望有个交互式 Shell，因此用的是 /bin/bash。
 
 
 
 
 
 启动已停止运行的容器
查看所有的容器命令如下：
 docker ps -a
 
 
 
 使用 docker start 启动一个已停止的容器：
 docker start b750bbbcfd88 
 
 
 
 
 停止的容器可以通过 docker restart 重启：
 
 
 
 
 
 
 
 
 
进入容器
在使用 -d 参数时，容器启动后会进入后台。此时想要进入容器，可以通过以下指令进入：

docker attach

docker exec：推荐大家使用 docker exec 命令，因为此退出容器终端，不会导致容器的停止。

attach 命令

下面演示了使用 docker attach 命令。  
docker attach 1e560fca3906 

exec 命令

下面演示了使用 docker exec 命令。

docker exec -it 243c32535da7 /bin/bash
 
 
 
 
 导出和导入容器
 docker export 1e560fca3906 > ubuntu.tar
 
 
 
 导入容器快照
 cat docker/ubuntu.tar | docker import - test/ubuntu:v1
 docker import http://example.com/exampleimage.tgz example/imagerepo
 
 
 删除容器
删除容器使用 docker rm 命令：

$ docker rm -f 1e560fca3906
 
 
 
 
列出镜像列表
我们可以使用 docker images 来列出本地主机上的镜像。
 
 
 
 
 网络端口的快捷方式
 docker port bf08b7f2cd89
 
 
 
 
 
 查看 WEB 应用程序日志
 docker logs -f bf08b7f2cd89
 
 
 
 
 
 
 
 
 
 
 查看WEB应用程序容器的进程
	docker top bf08b7f2cd89
	
	
	
	
	移除WEB应用容器

	docker rm wizardly_chandrasekhar  
 删除容器时，容器必须是停止状态，否则会报如下错误
 
 
 
 正在运行的容器，我们可以使用 docker restart 命令来重启。
 
 
 
 
 
 查找镜像
docker search httpd



删除镜像
镜像删除使用 docker rmi 命令，比如我们删除 hello-world 镜像：

$ docker rmi hello-world


拖取镜像
docker run httpd







过命令 docker commit 来提交容器副本。
docker commit -m="has update" -a="runoob" e218edb10161 runoob/ubuntu:v2
-m: 提交的描述信息

-a: 指定镜像作者

e218edb10161：容器 ID

runoob/ubuntu:v2: 指定要创建的目标镜像名







设置镜像标签
docker tag 860c279d2fec runoob/centos:dev











我们可以指定容器绑定的网络地址，比如绑定 127.0.0.1。
docker run -d -p 127.0.0.1:5001:5000 training/webapp python app.py
这样我们就可以通过访问 127.0.0.1:5001 来访问容器的 5000 端口。
 
 
 
 自定义网络
$ docker network create NAME
不指定 -d 参数，默认创建 bridge 驱动模式的 network。
自定义的 bridge network 会有自己专属的一个网段，与其他 network 隔离。
可以通过 docker network connect 指令将容器连接到一个 network，也可以在起容器（docker run 指令）时加入 --network 参数指定即将创建的容器加入到哪个 network，还可以通过 docker network disconnect 命令将容器移出自定义的 network。
加入到同一个自定义 bridge network 的容器间可以通过容器名进行通信，会自动进行 DNS 解析，但前提是需要给容器指定名称，随机分配的容器名无法被解析。也可以通过 IP 进行通信，因为属于同一个网段。
同一个容器可以同时加入到多个 network 下，此时该容器拥有多个网络接口，分别连接到不通的 bridge 上（可以通过 ip a 查看）。







docker Enginx configuration json

{
  "registry-mirrors": [
    "https://registry.docker-cn.com",
    "http://hub-mirror.c.163.com",
    "https://docker.mirrors.ustc.edu.cn"
  ],
  "insecure-registries": [],
  "debug": true,
  "experimental": true
}


OSI七层模型中的网桥 是在数据链路层的





docker cp mysql-php:/etc/mysql /Users/long/workpace/environments/evaluating-system/mysql/temp