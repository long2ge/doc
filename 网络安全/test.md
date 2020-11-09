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