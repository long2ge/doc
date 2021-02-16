DDOS是主要针对IP的攻击。
而DDOS攻击就是流量攻击，这种攻击的危害性较大，通过向目标服务器发送大量数据包，
耗尽其带宽，需要足够的带宽和硬件防火墙才能防御。

防止DOS太多连接进来,可以允许外网网卡每个IP最多15个初始连接,超过的丢弃
iptables -A INPUT -i eth0 -p tcp --syn -m connlimit --connlimit-above 15 -j DROP
iptables -A INPUT -p tcp -m state --state ESTABLISHED,RELATED -j ACCEPT


CC攻击的主要是网页。
CC攻击相对来说，攻击的危害不是毁灭性的，但是持续时间长；


(1)控制单个IP的最大并发连接数

iptables -I INPUT -p tcp --dport 80 -m connlimit  --connlimit-above 50 -j REJECT #允许单个IP的最大连接数为 30

web常见的安全
上传漏洞
    防范方法
        1. 上传扩展名需要限制
        2. 上传的文件目录不能有访问权限。
        3. 上传的文件目录不能有执行权限。
暴库
    1.数据库放在WEB目录外或将数据库连接文件放到其他虚拟目录下
    2. 使用框架
    3. 不要把数据库的信息写在代码上
旁注
    1. 不要把多个网站放到同一个服务器，真的需要的话，也需要做到物理隔离。
cookie诈骗
    1. 除了cookie，token验证，再增加浏览器指纹，IP地址，登陆时效控制等条件。
注入
    1. SQL注入，使用预插入即可。

xss注入
    1. 后端必须过滤输入（sql）和输出（xss）
    几条防止远程代码执行的TIPS： 1.禁止shell_exec()函数 2.如果你确实需要使用shell_exec()函数，建议使用escapeshellarg()和escapeshellcmd()做过滤。 3.使用WAF或 mod_security 等模块进行拦截过滤。
    # 注入的脚本如下：
if (isset($_POST["dns-lookup-php-submit-button"])){
try{
if ($targethost_validated){
  echo shell_exec(“nslookup ” . $targethost);
  $LogHandler->writeToLog($conn, “Executed operating system command: nslookup ” .     $lTargetHostText);
}else{
  echo ‘<script>document.getElementById(“id-bad-cred-tr”).style.display=”"</script>’;
}// end if ($targethost_validated){
}catch(Exception $e){
  echo $CustomErrorHandler->FormatError($e, “Input: ” . $targethost);
}// end try
}// end if (isset($_POST))
?>


CSRF攻击 - 攻击者借你的cookie（权限）让你亲自完成黑客布置的操作（增/删/改）并且你不知道这个过程。

受害者 Bob 在银行有一笔存款，通过对银行的网站发送请求 http://bank.example/withdraw?account=bob&amount=1000000&for=bob2 可以使 Bob 把 1000000 的存款转到 bob2 的账号下。通常情况下，该请求发送到网站后，服务器会先验证该请求是否来自一个合法的 session，并且该 session 的用户 Bob 已经成功登陆。

        黑客 Mallory 自己在该银行也有账户，他知道上文中的 URL 可以把钱进行转帐操作。Mallory 可以自己发送一个请求给银行：http://bank.example/withdraw?account=bob&amount=1000000&for=Mallory。但是这个请求来自 Mallory 而非 Bob，他不能通过安全认证，因此该请求不会起作用。

        这时，Mallory 想到使用 CSRF 的攻击方式，他先自己做一个网站，在网站中放入如下代码： src=”http://bank.example/withdraw?account=bob&amount=1000000&for=Mallory ”，并且通过广告等诱使 Bob 来访问他的网站。当 Bob 访问该网站时，上述 url 就会从 Bob 的浏览器发向银行，而这个请求会附带 Bob 浏览器中的 cookie 一起发向银行服务器。大多数情况下，该请求会失败，因为他要求 Bob 的认证信息。但是，如果 Bob 当时恰巧刚访问他的银行后不久，他的浏览器与银行网站之间的 session 尚未过期，浏览器的 cookie 之中含有 Bob 的认证信息。这时，悲剧发生了，这个 url 请求就会得到响应，钱将从 Bob 的账号转移到 Mallory 的账号，而 Bob 当时毫不知情。等以后 Bob 发现账户钱少了，即使他去银行查询日志，他也只能发现确实有一个来自于他本人的合法请求转移了资金，没有任何被攻击的痕迹。而 Mallory 则可以拿到钱后逍遥法外。 


要抵御 CSRF，关键在于在请求中放入黑客所不能伪造的信息，并且该信息不存在于 cookie 之中。

在请求地址中添加 token 并验证




SSRF(Server-Side Request Forgery:服务器端请求伪造) 是一种由攻击者构造形成由服务端发起请求的一个安全漏洞。一般情况下，SSRF攻击的目标是从外网无法访问的内部系统。（正是因为它是由服务端发起的，所以它能够请求到与它相连而与外网隔离的内部系统）



XSS 在目标网站中执行我们自己的写的JS代码
反射型XSS
  例如 ： <script>alter(123)</script>
  把构造好的URL发给受害者，是受害者点击触发，而且只执行一次，非持久化。
DOM型XSS

存储型XSS
  攻击者把恶意的js代码放到服务器中，只要受害者浏览包含恶意的代码的页面就会执行恶意代码。

防止方法
过滤script字符串

  nmap 初步认识



  HTML注入
    前端展示数据，全部都是用字符串的方式展示，后端输出也需要过滤。
      php可以使用htmlspecialchars（）
  XSS跨站
  CSRF客户端请求伪造
  SSRF服务端请求伪造

  SQL注射
  怎样修复SQL注入？
  另类注入
  报错注入
  逻辑漏洞 - 通常是指网站业务功能方面的缺陷。
      1. 通过暴力破解密码
      2. 支付漏洞，修改支付价格成0，然后支付。
      3.越权。修改用户id，访问他人敏感信息或者冒充他人发布信息。
      4. cookie和session验证。攻击者知道用户id，构建cookie和session等于true，就可以绕过认证。
      5. 顺序执行缺陷。果然支付过程，直接从放入购物车进去填写收获地址步骤，这样就可以0元购买了。
  越权漏洞
  未授权访问
  
  文件读取
  文件上传
  文件包含
  文件下载

  代码注入
  命令执行
  威胁情报

  弱口令



  逻辑漏洞
  1. web安全漏洞之逻辑漏洞，常用是之网站业务功能方面的缺陷，设计者
  或者开发者在思考过程中做出的特殊假设存在明显或隐含的错误。
  挖掘逻辑漏洞有两个重点，就是业务流程和抓包改包。

  漏洞分类
  1.密码找回，任意手机号注册
  程序根据一个验证码来确认用户本人，但是攻击者可以暴力破解验证码。
  2. 支付漏洞
  测试人员修改商品数量为负数，使得支付价格为负数直接0元购买，或者有修改金额数目。
  3. 越权
  攻击者更改数据包中指示用户的id，来访问他人敏感信息或者冒充他人发布信息。
  4. Cookies和session验证问题
  攻击者在知道用户id的前提下，然后构造一个cookies或者让session值为true就可以绕过这样的认证。
  5. 顺序执行
  攻击者在网购的过程中绕过支付宝的过程，直接从放入购物车步骤进入填写
  收获地址的步骤，这样的话就可以0元购物了。



1. 暴力破解密码
  增加验证码
  错误锁定限制

2. xss跨站脚本漏洞
  不要在客户端保存敏感信息
  自动登陆超时
  修改账号需要判断旧密码
  使用post，不是get
  通过http头部中的referer来限制原来页面（一般用referer来检测CSRF攻击)
  对关键操作增加token参数，token值需要随机，每次不一样


  3. 上传漏洞
    多条件组合检查，例如文件的大小，路径，扩展名，文件类型
    对上传的文件在服务器上存储进行重命名。
    对服务端上传文件的目录进行权限执行，例如只读。

  4. 文件包含漏洞

    入侵方法
      制作一个图片木马，通过文件上传漏洞上传
      通过文件包含漏洞对该图片木马进行包含
      获取执行结果。

    防范方法
      需要包含的文件不能让前端输入。


  5. commad Inject 漏洞危害
  对于输入的命令，需要进行严格的过滤。



2. jwt 增加浏览器指纹、




条件竞争漏洞， 利用网站并发处理不当的漏洞。例如用户剩下1元，通过脚本并发转账给其他人。



SSRF
1.禁止跳转

2.过滤返回信息，验证远程服务器对请求的响应是比较容易的方法。如果web应用是去获取某一种类型的文件。那么在把返回结果展示给用户之前先验证返回的信息是否符合标准。

3.禁用不需要的协议，仅仅允许http和https请求。可以防止类似于file://, gopher://, ftp:// 等引起的问题

4.设置URL白名单或者限制内网IP（使用gethostbyname()判断是否为内网IP）

5.限制请求的端口为http常用的端口，比如 80、443、8080、8090

6.统一错误信息，避免用户可以根据错误信息来判断远端服务器的端口状态。