# 网络数据输入的两个阶段

1. 网络数据到达网卡 然后 到达 内核缓冲区

2. 客户缓冲区 到 用户空间

例如 ：程序读取磁盘数据

磁盘的数据被读到内核空间，用户空间再读取内核空间的数据。

# io的五种模型

1. blocking I/O 
2. noblocking I/O  这种模型第一阶段使用轮询的方式实现，第二阶段还是block。
3. signal blocking I/O 数据处理完毕后调用回调函数。
4. I/O multiplexing 属于blocking I/O, 但是可以同时对多个文件描述符进行阻塞监听，所以效率高。
5. asynchronous I/O



## I/Omultiplexing 详解

1 使用select / epoll 的web service 不一定比使用 multi-threading + blocking io 的web service 性能

更好，可能演示还要大。

2，select / epoll 的优势并不是对单个链接能处理得更快，而是在于能处理更多的链接。

3 注意，实际上，对于每个socker， 一般都设置成non-blocking， 但是process一直是block。只不过

process是被select 这个函数block，而不是被socket io 给block。

4 epoll使用一个文件描述符管理多个文件描述符，将用户关系的文件描述符的时间存放在内核的一个事件表中，这样在用户空间和内核空间的copy只需一次。

5， select 的方式 通过遍历fd set找到就绪的描述符。

优点：几乎所有平台支持。

缺点：linux上一般1024个链接是最大的支持了。

6 poll和select差不多。都是链表结构。

不同 ： 没有最大数量限制。但是数量过大性能会下降。

7 epoll包括两个成员

就绪队列 ： 放就绪时间的描述符

红黑树 ： 作为内核时间表用来收集描述符。

epoll 默认LT模式

nginx 默认ET模式

8 LT 事件发生将通知应用可以不立即处理，下次调用epoll_wait时，还会响应。

9 ET 事件发生将通知，下次不会响应。只支持非堵塞读写，为了数据完整性。

10 epoll 使用两个system call ，select 和 recvfrom。性能比blocking I/O还要差一些。但是

优势在于可以同时处理多个connection。