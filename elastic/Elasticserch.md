集群的健康检查

green 每个索引的primary shared 和replica shared 都是active状态

yellow 每个索引的primary  shared 是 active， replica shared 不是active，



red 每个索引的primary shared 和 replica shared 都不是active。

请求路由是 GET /_cat/health

