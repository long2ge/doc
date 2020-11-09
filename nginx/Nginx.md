```rust
mermaid
graph TD
   A --> B
```


### 优化
    Nginx 配置文件里打开 sendfile on 选项能提高 web serve r性能的原因
    
    1、系统调用 sendfile() 通过 DMA 把硬盘数据拷贝到 kernel buffer，然后数据被 kernel 直接拷贝到另外一个与 socket 相关的 kernel buffer。这里没有 user mode 和 kernel mode 之间的切换，在 kernel 中直接完成了从一个 buffer 到另一个 buffer 的拷贝。
    2、DMA 把数据从 kernel buffer 直接拷贝给协议栈，没有切换，也不需要数据从 user mode 拷贝到 kernel mode，因为数据就在 kernel 里。
    
    步骤减少了，切换减少了，拷贝减少了，自然性能就提升了。