
 yum install -y wget

    // 安装yum管理工具
    yum install -y yum-utils \
  device-mapper-persistent-data \
  lvm2
     

2. 关闭 selinux
1.查看 selinux 是否关闭
运行指令 getenforce  
输出 Enforcing(未关闭)
2.设置临时关闭
setenforce 0
3.永久关闭
vi /etc/sysconfig/selinux
修改 SELINUX=disabled



3. 关闭 swap （交换分区，虚拟内存）
K8s要求安装前必须禁用swap


临时禁用 :swapoff -a
永久禁用：sed -i.bak '/swap/s/^/#/' /etc/fstab ##注释掉/etc/fstab中 swap 那一行


192.168.1.13 k8s-master
192.168.37.2 k8s-node

4.配置 ip_forward 转发
ip_forward 配置文件当前内容为 0，表示禁止数据包转发，将其修改为 1 表 示允许
 echo "1" > /proc/sys/net/ipv4/ip_forward


hostnamectl set-hostname k8s-master
hostnamectl set-hostname k8s-node

3、分别在192.168.73.138、192.168.73.139、192.168.73.140上设置主机名及配置hosts

$ hostnamectl set-hostname k8s-master（192.168.73.138主机打命令）
$ hostnamectl set-hostname k8s-node01（192.168.73.139主机打命令）



cd /etc/yum.repos.d/
rm -rf *
wget -O /etc/yum.repos.d/CentOS-Base.repo http://mirrors.aliyun.com/repo/Centos-7.repo 
wget -P /etc/yum.repos.d/ http://mirrors.aliyun.com/repo/epel-7.repo
wget https://mirrors.aliyun.com/docker-ce/linux/centos/docker-ce.repo
cat <<EOF > /etc/yum.repos.d/kubernetes.repo 
[kubernetes]
 name=Kubernetes
 baseurl=https://mirrors.aliyun.com/kubernetes/yum/repos/kubernetes-el7-x86_64
 enabled=1
 gpgcheck=0
 EOF
yum clean all && yum makecache fast


    // 添加镜像源



	sudo yum-config-manager --add-repo http://mirrors.aliyun.com/docker-ce/linux/centos/docker-ce.repo

// 如果报错 443，修改下面数据
[docker-ce-stable]
name=Docker CE Stable - $basearch
baseurl=https://mirrors.aliyun.com/docker-ce/linux/centos/7/$basearch/stable
enabled=1
gpgcheck=1
gpgkey=https://mirrors.aliyun.com/docker-ce/linux/centos/gpg



     
    // 查看docker版本
    yum list docker-ce --showduplicates|sort -r
     
    // 安装
yum install -y docker-ce-19.03.9-3.el7 docker-ce-cli-19.03.9-3.el7 containerd.io


mkdir /etc/docker/
vi /etc/docker/daemon.json


{
  "registry-mirrors": [
    "https://registry.docker-cn.com",
    "http://hub-mirror.c.163.com",
    "https://docker.mirrors.ustc.edu.cn"
  ],
  "insecure-registries": [],
  "exec-opts": ["native.cgroupdriver=systemd"],
  "debug": true,
  "experimental": true
}


systemctl enable docker && systemctl restart docker

# systemctl daemon-reload
# systemctl restart docker

docker info | grep Cgroup


k8s
cat > /etc/yum.repos.d/kubernetes.repo << EOF
[kubernetes]
name=Kubernetes
baseurl=https://mirrors.aliyun.com/kubernetes/yum/repos/kubernetes-el7-x86_64
enabled=1
gpgcheck=0
repo_gpgcheck=0
gpgkey=https://mirrors.aliyun.com/kubernetes/yum/doc/yum-key.gpg https://mirrors.aliyun.com/kubernetes/yum/doc/rpm-package-key.gpg
EOF




yum install -y kubelet-1.20.4 kubeadm-1.20.4 kubectl-1.20.4

systemctl enable kubelet
systemctl restart kubelet

 开放这两个端口
firewall-cmd --zone=public --add-port=6443/tcp --permanent && firewall-cmd --zone=public --add-port=10250/tcp --permanent && firewall-cmd --reload


关闭防火墙及selinux
 systemctl stop firewalld && systemctl disable firewalld​
 sed -i 's/^SELINUX=.*/SELINUX=disabled/' /etc/selinux/config  && setenforce 0

内核调整,将桥接的IPv4流量传递到iptables的链
vi /etc/sysctl.d/k8s.conf
net.bridge.bridge-nf-call-ip6tables = 1
net.bridge.bridge-nf-call-iptables = 1

sysctl -p /etc/sysctl.d/k8s.conf

6、设置系统时区并同步时间服务器
# yum install -y ntpdate

# ntpdate time.windows.com


master节点
kubeadm init \
--apiserver-advertise-address=192.168.1.13 \
--image-repository registry.aliyuncs.com/google_containers \
--kubernetes-version v1.20.4 \
--service-cidr=10.10.10.10/16 \
--pod-network-cidr=10.244.0.0/16



[root@k8s-master ~]# mkdir -p $HOME/.kube
[root@k8s-master ~]# sudo cp -i /etc/kubernetes/admin.conf $HOME/.kube/config
[root@k8s-master ~]# sudo chown $(id -u):$(id -g) $HOME/.kube/config
默认token的有效期为24小时，当过期之后，该token就不可用了，

如果后续有nodes节点加入，解决方法如下：



kubeadm token create
atcsqo.zl7l7o8hyx1lbcht




kubeadm token list



获取ca证书sha256编码hash值

openssl x509 -pubkey -in /etc/kubernetes/pki/ca.crt | openssl rsa -pubin -outform der 2>/dev/null | openssl dgst -sha256 -hex | sed 's/^.* //'
openssl x509 -pubkey -in /etc/kubernetes/pki/ca.crt | openssl rsa -pubin -outform der 2>/dev/null | openssl dgst -sha256 -hex | sed 's/^.* //'

665d5a04e0ab07965bdc0d444e6e708b4175e3404527773b3c66b0f816f994eb


在节点上先执行如下命令，清理kubeadm的操作，然后再重新执行join 命令：

kubeadm reset

节点加入集群
kubeadm join 192.168.1.13:6443 --token pz1432.3qi55gaxgl682xf8 --discovery-token-ca-cert-hash 8677e94f32b7575ac622f3b6e451f85efa662678ca28766b4f3e984d06998015


 
  

kubeadm join 192.168.1.10:6443 --token sl6rir.zjpmthadcayy6opx --discovery-token-ca-cert-hash sha256:9cc8c66dfd6b3be73c5eb1107795118f63abaf361f973b072d24e99d3511b148




至此，k8s集群就搭建成功，但我们可以看到多有节点的STATUS均为NotReady，这是因为还没安装网络插件。此外，node节点的ROLES为<none>


kubectl apply -f https://raw.githubusercontent.com/coreos/flannel/v0.12.0/Documentation/kube-flannel.yml

kubectl apply -f https://raw.githubusercontent.com/coreos/flannel/master/Documentation/kube-flannel.yml




创建网络：
wget https://raw.githubusercontent.com/coreos/flannel/master/Documentation/k8s-manifests/kube-flannel-legacy.yml
kubectl create -f https://raw.githubusercontent.com/coreos/flannel/master/Documentation/k8s-manifests/kube-flannel-legacy.yml
##记得修改网段

kubectl apply -f https://raw.githubusercontent.com/coreos/flannel/master/Documentation/k8s-manifests/kube-flannel-rbac.yml


kubectl get nodes
journalctl -u kubelet | tail -n 300


kubectl apply -f https://raw.githubusercontent.com/coreos/flannel/master/Documentation/kube-flannel.yml

wget https://raw.githubusercontent.com/coreos/flannel/master/Documentation/kube-flannel.yml

kubectl apply -f ./kube-flannel.yml

cat kube-flannel.yml |grep image|uniq

docker pull registry.cn-hangzhou.aliyuncs.com/clover/flannel:v0.13.1-rc2



journalctl -f -u kubelet

 在master上查看pods发现flannel镜像下载失败
kubectl get pods -n kube-system

Unable to update cni config: No networks found in /etc/cni/net.d
该错误意思是 CNI插件还未安装，所以状态会是NotReady。

方法一：

​ 编辑 /etc/systemd/system/kubelet.service.d/10-kubeadm.conf文件（有的是/usr/lib/systemd/system/kubelet.service.d/10-kubeadm.conf文件)，
​ 删除最后一行里的$KUBELET_NETWORK_ARGS 即可。
​ （该方法治标不治本，没能解决我的问题）

方法二：

​ 考虑到node节点的kubelet报错Unable to update cni config: No networks found in /etc/cni/net.d，并且master节点的/etc/cni/net.d目录里拥有10-flannel.conflist文件，
​ 我们可以把该文件从master节点复制到node节点的对应目录中，然后重启kubelet服务即可。
​ (该方法亡羊补牢，对我的问题也没用)

方法三：

​ 注意到master节点的/etc/cni/net.d/10-flannel.conflist文件是经过 安装flannel插件 才生成的，而node节点则是在master节点安装完flannel插件后才加入集群的，
​ 所以，我们不妨 先加入node节点，再执行 flannel插件的安装。
​ (该方法有取巧的嫌疑，但解决了我的问题)



在master节点上执行kubeadm token create --print-join-command重新生成加入命令，并使用输出的新命令在工作节点上重新执行即可。

