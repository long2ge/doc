SQL注入攻击
1. 使用预插入的方式，定义好模版来防御。


XSS攻击
１.　把敏感的词替换成 * 。
2. 使用转义函数
3. ini_set("session.cookie_httponly", 1);
设置 HttpOnly，你在浏览器的document对象中就看不到Cookie了。

这种session劫持主要靠XSS漏洞和客户端获取sessionId完成，一次防范分两步

1. 过滤用户输入，防止XSS漏洞

2. 设置sessionId的cookie为HttpOnly，使客户端无法获取


XML注入

Xss 攻击

Ldap注入


