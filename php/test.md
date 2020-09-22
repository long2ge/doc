### 问题
1. 直接读文件相比数据库查询效率更胜一筹，而且文中还没算上连接和断开的时间。
2. Opcode cache的目地是避免重复编译，减少CPU和内存开销。如果动态内容的性能瓶颈不在于CPU和内存，而在于I/O操作，
比如数据库查询带来的磁盘I/O开销，那么opcode cache的性能提升是非常有限的。
但是既然opcode cache能带来CPU和内存开销的降低，这总归是好事。
    opcache.enable = 1 // 开关打开  
    opcache.memory_consumption = 256 // 可用内存, 酌情而定, 单位 megabytes  
    opcache.max_accelerated_files = 5000 // 对多缓存文件限制, 命中率不到 100% 的话, 可以试着提高这个值  
    opcache.revalidate_freq = 240 // Opcache 会在一定时间内去检查文件的修改时间, 这里设置检查的时间周期, 默认为 2, 定位为秒  
    
3. 对于PHP来说，FFI让我们可以方便的调用C语言写的各种库。
4. Hugepage　－　PHP会把自身的text段, 以及内存分配中的huge都采用大内存页来保存, 减少TLB miss, 从而提高性能.


    opcache.huge_code_pages=1　// Opcache启用这个特性
    $ sudo sysctl vm.nr_hugepages=128
    vm.nr_hugepages = 128 // 现在让我们配置OS， 分配一些Hugepages：
    cat /proc/meminfo  | grep Huge // 查看结果 ：
    
5. 使用GCC 4.8以上PHP才会开启Global Register for opline and execute_data支持, 
这个会带来5%左右的性能提升(Wordpres的QPS角度衡量)

6. 开启Opcache File Cache  --  opcache.file_cache=/tmp

7. JSON_UNESCAPED_UNICODE, 故名思议, 就是说, Json不要编码Unicode

8. 多位数据排序　－　array_multisort

9. 异常 - PHP 中的 Exception, Error, Throwable
    
    
    
    1. PHP 中将代码自身异常(一般是环境或者语法**所致)称作错误 Error，将运行**现的逻辑错误称为异常 Exception
    错误是没法通过代码处理的，而异常则可以通过 try/catch 来处理
    2. PHP7 **现了 Throwable 接口，该接口由 Error 和 Exception 实现，用户不能直接实现 Throwable 接口，而只能通过继承 Exception 来实现接口
    3. 注意：其他级别的错误如 warning 和 notice，和之前一样不会抛出异常，只有 fatal 和 recoverable 级别的错误会抛出异常。