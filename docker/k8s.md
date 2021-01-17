### Pods
1.  创建，调度，以及管理的最小单元
2. 共存的一组容器的集合
3. 共享网络
4. 生命周期是短暂的

### Controller
1. 确保预期的pod副本数量
2. 无状态应用部署
3. 有状态应用部署
4. 确保所有的node运行同一个pod
5. 一次性任务和定时任务

### Service
1. 定义一组pod的访问规则

### master组件
1. apiserver
集群统一入口，以restful方式，交给etcd存储
2. scheduler
节点调度，选择node节点应用部署
3. controller-manager
处理集群中常规后台任务，一个资源对应一个控制器
4. etcd 
存储系统，用于保存集群相关的数据

### worker node 
1. kubelet
2. kube-proxy
3. Pod



### 单 master 集群  
            master
    node1   node2    node3

### 多 master 集群
    node1    node2     node3
            负载均衡
    master            master


### 二进制方式搭建k8s集群
1. 创建多台虚拟机，安装linux操作系统
2. 凑走系统初始化
3. 为etcd和apiservice自签证书
4. 部署etcd集群
5. 部署master组件
    kube-apiservice， kube-controller-manager， kube-scheduler， etcd
6. 部署node组件
    kubelet， kube-proxy， docker， etcd
7. 部署集群网络
