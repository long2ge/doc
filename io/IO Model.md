# 网络数据输入的两个阶段

1. 网络数据到达网卡 然后 到达 内核缓冲区

2. 内核缓冲区 到 用户空间

第一阶段：等待数据 wait for data

第二阶段：从内核复制数据到用户 copy data from kernel to user

例如 ：程序读取磁盘数据

磁盘的数据被读到内核空间，用户空间再读取内核空间的数据。

# io的五种模型

1. blocking I/O 

![Image text](https://images0.cnblogs.com/blog/405877/201411/142330286789443.png)  

用户线程通过系统调用read发起IO读操作，由用户空间转到内核空间。内核等到数据包到达后，
然后将接收的数据拷贝到用户空间，完成read操作。

用户线程使用同步阻塞IO模型的伪代码描述为：

{

read(socket, buffer);

process(buffer);

}
即用户需要等待read将socket中的数据读取到buffer后，才继续处理接收的数据。
整个IO请求的过程中，用户线程是被阻塞的，这导致用户在发起IO请求时，不能做任何事情，对CPU的资源利用率不够。




2. noblocking I/O  这种模型第一阶段使用轮询的方式实现，第二阶段还是block。
![Image text](https://images0.cnblogs.com/blog/405877/201411/142332004602984.png)  
由于socket是非阻塞的方式，因此用户线程发起IO请求时立即返回。但并未读取到任何数据，
用户线程需要不断地发起IO请求，直到数据到达后，才真正读取到数据，继续执行。

用户线程使用同步非阻塞IO模型的伪代码描述为：

{

while(read(socket, buffer) != SUCCESS)

;

process(buffer);

}

即用户需要不断地调用read，尝试读取socket中的数据，直到读取成功后，才继续处理接收的数据。整个IO请求的过程中，
虽然用户线程每次发起IO请求后可以立即返回，但是为了等到数据，仍需要不断地轮询、重复请求，消耗了大量的CPU的资源。
一般很少直接使用这种模型，而是在其他IO模型中使用非阻塞IO这一特性。

3. signal blocking I/O 数据处理完毕后调用回调函数。
![Image text](https://images0.cnblogs.com/blog/405877/201411/142332004602984.png)
由于socket是非阻塞的方式，因此用户线程发起IO请求时立即返回。
但并未读取到任何数据，用户线程需要不断地发起IO请求，直到数据到达后，才真正读取到数据，继续执行。

用户线程使用同步非阻塞IO模型的伪代码描述为：

{

while(read(socket, buffer) != SUCCESS)

;

process(buffer);

}

即用户需要不断地调用read，尝试读取socket中的数据，直到读取成功后，才继续处理接收的数据。
整个IO请求的过程中，虽然用户线程每次发起IO请求后可以立即返回，但是为了等到数据，仍需要不断地轮询、重复请求，
消耗了大量的CPU的资源。一般很少直接使用这种模型，而是在其他IO模型中使用非阻塞IO这一特性。  

4. I/O multiplexing 属于blocking I/O, 但是可以同时对多个文件描述符进行阻塞监听，所以效率高。
IO多路复用模型是建立在内核提供的多路分离函数select基础之上的，使用select函数可以避免同步非阻塞IO模型中轮询等待的问题。
![Image text](https://images0.cnblogs.com/blog/405877/201411/142332187256396.png)  



同一个线程内同时处理多个IO请求的目的

IO多路复用模型是建立在内核提供的多路分离函数select基础之上的，使用select函数可以避免同步非阻塞IO模型中轮询等待的问题。

在多路复用IO模型中，会有一个线程不断去轮询多个socket的状态，只有当socket真正有读写事件时，
才真正调用实际的IO读写操作。因为在多路复用IO模型中，只需要使用一个线程就可以管理多个socket，
系统不需要建立新的进程或者线程，也不必维护这些线程和进程，并且只有在真正有socket读写事件进行时，
才会使用IO资源，所以它大大减少了资源占用。

而多路复用IO模式，通过一个线程就可以管理多个socket，只有当socket真正有读写事件发生才会占用资源来进行实际的读写操作。
因此，多路复用IO比较适合连接数比较多的情况。

　另外多路复用IO为何比非阻塞IO模型的效率高是因为在非阻塞IO中，不断地询问socket状态时通过用户线程去进行的，
而在多路复用IO中，轮询每个socket状态是内核在进行的，这个效率要比用户线程要高的多。
 
不过要注意的是，多路复用IO模型是通过轮询的方式来检测是否有事件到达，并且对到达的事件逐一进行响应。
因此对于多路复用IO模型来说，一旦事件响应体很大，那么就会导致后续的事件迟迟得不到处理，并且会影响新的事件轮询。



![Image text](https://images0.cnblogs.com/blog/405877/201411/142333254136604.png)
通过Reactor的方式，可以将用户线程轮询IO操作状态的工作统一交给handle_events事件循环进行处理。
用户线程注册事件处理器之后可以继续执行做其他的工作（异步），
而Reactor线程负责调用内核的select函数检查socket状态。
当有socket被激活时，则通知相应的用户线程（或执行用户线程的回调函数），
执行handle_event进行数据读取、处理的工作。由于select函数是阻塞的，
因此多路IO复用模型也被称为异步阻塞IO模型。注意，这里的所说的阻塞是指select函数执行时线程被阻塞，
而不是指socket。一般在使用IO多路复用模型时，socket都是设置为NONBLOCK的，
不过这并不会产生影响，因为用户发起IO请求时，数据已经到达了，用户线程一定不会被阻塞。      


epoll在内核中会维护一个红黑树和一个双向链表


5. asynchronous I/O
![Image text](https://images0.cnblogs.com/blog/405877/201411/142333511475767.png)


异步IO模型才是最理想的IO模型，在异步IO模型中，当用户线程发起read操作之后，立刻就可以开始去做其它的事。而另一方面，从内核的角度，
当它受到一个asynchronous read之后，它会立刻返回，说明read请求已经成功发起了，因此不会对用户线程产生任何block。然后，内核会等待数据准备完成，
然后将数据拷贝到用户线程，当这一切都完成之后，内核会给用户线程发送一个信号，告诉它read操作完成了。也就说用户线程完全不需要关心实际的整个IO操作是如何进行的，
只需要先发起一个请求，当接收内核返回的成功信号时表示IO操作已经完成，可以直接去使用数据了。


也就说在异步IO模型中，IO操作的两个阶段都不会阻塞用户线程，这两个阶段都是由内核自动完成，然后发送一个信号告知用户线程操作已完成。
用户线程中不需要再次调用IO函数进行具体的读写。这点是和信号驱动模型有所不同的，在信号驱动模型中，当用户线程接收到信号表示数据已经就绪，
然后需要用户线程调用IO函数进行实际的读写操作；而在异步IO模型中，收到信号表示IO操作已经完成，不需要再在用户线程中调用iO函数进行实际的读写操作。


 select和epoll都是多路复用的实现。
 
 select：
 
 调用select(fds)，把fds（最多1024个）从用户空间拷贝到内核空间，进程阻塞，
 
 当socket缓冲区有数据，唤醒进程，遍历fds，处理。
 
 每次调用select，都需要把fd_set集合从用户态拷贝到内核态，如果fd_set集合很大时，那这个开销也很大
 同时每次调用select都需要在内核遍历传递进来的所有fd_set，如果fd_set集合很大时，那这个开销也很大
 为了减少数据拷贝带来的性能损坏，内核对被监控的fd_set集合大小做了限制，并且这个是通过宏控制的，大小不可改变(限制为1024)
 
 epoll：
 
 epoll_create在内核空间创建eventpoll对象（包括红黑树和就绪链表），
 
 调用epoll_clt(fds)把fds加入到eventpoll的红黑树中，
 
 给每个fd都向底层注册回调，
 
 调用epoll_wait，进程阻塞，
 
 当socket缓冲区有数据时，通过回调把红黑树对应的fd加入到就绪链表，
 
 epoll_wait就会得到就绪链表中就绪的fds，处理。
 
 水平触发（LT）：只要缓冲区有数据，就会触发（epoll_wait结束阻塞得到就绪列表），是默认模式。（select也是水平触发）
 
 边缘触发（ET）：只有新数据到达缓冲区才会触发，不管缓冲区有无旧数据。
 
 实现：LT模式每次把就绪链表清空，下次有新数据到来才会调用回调把新数据的fd加入到就绪链表中。
 
 　　　 ET模式则不会把还有数据的fd从就绪链表删掉，所以下次调epoll_wait还会触发。
 
 因此，ET模式实际上把保证数据处理完的逻辑交给了用户，只会触发一次所以提高了效率。
 
 

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





epoll它的核心就是三个函数两个数据结构

![Image text](https://img-blog.csdnimg.cn/20200102153320411.png?x-oss-process=image/watermark,type_ZmFuZ3poZW5naGVpdGk,shadow_10,text_aHR0cHM6Ly9ibG9nLmNzZG4ubmV0L0RhY2hhbzA3MDc=,size_16,color_FFFFFF,t_70) 

epoll_create:这个函数就是创建一个epoll对象，
调用这个函数的时候内核会帮我们创建一个epoll实例数据结构，
就是一个用于存放fd的红黑树和一个用于存储就绪事件的链表，
这些是在内核中存放了一下块缓存中。

epoll_ctl:这个函数就是负责管理fd的增加和删除的,也就是在红黑树上进行删除和增加。

epoll_wait(),它是用来阻塞等待注册事件发生的，返回值是发生事件的数目，并且把事件写入到events数组中

参考
https://www.cnblogs.com/fanzhidongyzby/p/4098546.html
https://www.cnblogs.com/lankerenf3039/p/12116142.html
https://blog.csdn.net/m0_37962600/article/details/81407442
https://blog.csdn.net/Dachao0707/article/details/103805224