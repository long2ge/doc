MongoDB采用B树索引

Mongodb 更新失败解决方案

现象：

WriteResult res = mongoTemplate.updateFirst(query, updateObj, "ServerToAgentReq_SMS");

获取res.getN()返回值时，发现偶尔情况下该返回值为0，表示该更新操作没有更新到任何数据。并且如果是多线程并发更新，失败几率大大提高。

官网表示不能保证更新操作的成功性....

方案：

一次失败后，另起线程多次重试。