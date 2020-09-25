集群的健康检查

green 每个索引的primary shared 和replica shared 都是active状态

yellow 每个索引的primary  shared 是 active， replica shared 不是active，



red 每个索引的primary shared 和 replica shared 都不是active。

请求路由是 GET /_cat/health



　　es执行更新操作的时候，ES首先将旧的文档标记为删除状态，然后添加新的文档，旧的文档不会立即消失，但是你也无法访问，

ES会在你继续添加更多数据的时候在后台清理已经标记为删除状态的文档。

全部更新，是直接把之前的老数据，标记为删除状态，然后，再添加一条更新的。

局域更新，只是修改某个字段。


