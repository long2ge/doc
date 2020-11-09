使用iptables抵抗常见攻击


1.  防止syn攻击

思路一：限制syn的请求速度（这个方式需要调节一个合理的速度值，不然会影响正常用户的请求）

iptables -N syn-flood 
 
iptables -A INPUT -p tcp --syn -j syn-flood 
 
iptables -A syn-flood -m limit --limit 1/s --limit-burst 4 -j RETURN 
 
iptables -A syn-flood -j DROP 

思路二：限制单个ip的最大syn连接数
iptables –A INPUT –i eth0 –p tcp --syn -m connlimit --connlimit-above 15 -j DROP 




2.  防止DOS攻击
利用recent模块抵御DOS攻击

iptables -I INPUT -p tcp -dport 22 -m connlimit --connlimit-above 3 -j DROP 

单个IP最多连接3个会话
iptables -I INPUT -p tcp --dport 22 -m state --state NEW -m recent --set --name SSH  

只要是新的连接请求，就把它加入到SSH列表中
Iptables -I INPUT -p tcp --dport 22 -m state NEW -m recent --update --seconds 300 --hitcount 3 --name SSH -j DROP  

5分钟内你的尝试次数达到3次，就拒绝提供SSH列表中的这个IP服务。被限制5分钟后即可恢复访问。


3.  防止单个ip访问量过大

iptables -I INPUT -p tcp --dport 80 -m connlimit --connlimit-above 30 -j DROP 

4.  木马反弹

iptables –A OUTPUT –m state --state NEW –j DROP 

5.  防止ping攻击

iptables -A INPUT -p icmp --icmp-type echo-request -m limit --limit 1/m -j ACCEPT 