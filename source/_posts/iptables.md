---
title: IPtables Practice
categories:
  - it
tags:
  - network
  - iptables
date: 2017-06-25 15:46:48
updated: 2017-06-25 15:46:48
---

Reference In Chinese:[iptables超全详解](http://www.linuxidc.com/Linux/2016-09/134832.htm)

Stateful firewall
- Traditional: 
  To allow outgoing website visiting and to drop other communication
  To allow input tcp with source port 80 and ack
  Can’t visit websites on ports other than 80
- To use stateful firewall
  State tracking

```
sudo iptables -A OUTPUT -p tcp --dport 80 -j DROP
sudo iptables -A INPUT -p udp -j ACCEPT (DNS)

sudo iptables -A INPUT -i lo -j ACCEPT
sudo iptables -A INPUT -p tcp --sport 80 --tcp-flags SYN,ACK,RST,FIN ACK -j ACCEPT
```

Iptables Examples:
`iptables -A OUTPUT -p tcp --dport 23 -j DROP` 
Prevent a machine from telneting to other machines

`iptables –A INPUT –p tcp –dport 23 –j DROP` 
Prevent a telnet server from being connected by other machines

`iptables –A INPUT –p tcp –d 1.2.3.4 –j DROP` 
Prevent inner network from connecting a social network 1.2.3.4

```
sudo iptables -A INPUT -p icmp --icmp-type 8 -j DROP
iptables -P INPUT DROP
iptables -A INPUT -I eth0 -p icmp -m state --state ESTABLISH,RELATED -j ACCEPT
```
Disable to be pinged, enable to ping

```
iptables -A INPUT -p icmp --icmp-type echo-request -m limit --limit 30/min --limit-burst 8 -j ACCEPT
iptables -A INPUT -p icmp --icmp-type echo-request -j DROP
```
To limit the number of pings

`sudo iptables -t nat -A POSTROUTING -p icmp --icmp-type 8 -j SNAT --to-source 192.168.137.131`
To change the source IP of a ping packet sent out from our machine

---- 


`sudo sysctl –p /etc/sysctl.conf`
Find  /etc/sysctl.conf

To act as a firewall (protect inner network)
To enable packet forward
  to redirect the input packet to a specific website
    To change the source and dst
  to change the reply packet to a specific source and port
    To change the source and dst
```
sudo iptables -t nat -A OUTPUT -p tcp --dport 80 -j DNAT --to-destination 202.38.64.3:80

iptables -t nat -A PREROUTING -i ppp0 -p tcp --dport 80 -j DNAT --to-destination 192.168.1.200:80

Iptables –t nat –A PREROUTING –p tcp –dport 8123 –j DNAT –to 192.168.141.235:80

sudo iptables -t nat -A PREROUTING -p tcp --dport 8123 -j DNAT --to 192.168.141.235:80
sudo iptables -t nat -A POSTROUTING -p tcp -s 192.168.141.1 -j SNAT --to 192.168.141.226
sudo iptables -t nat -A PREROUTING -p tcp -s 192.168.141.235 --sport 80 -j DNAT --to 192.168.141.1
sudo iptables -t nat -A POSTROUTING -p tcp -s 192.168.141.235 --sport 80 -j SNAT --to 192.168.141.226:8123
```

To stop conntrack
```
sudo iptables -t raw -A OUTPUT -j NOTRACK
sudo iptables -t raw -A PREROUTING -j NOTRACK
```

To act as a firewall (protect inner network)
To enable packet forward
Change the .1 machine to the firewall itself
```
sudo iptables -t nat -A PREROUTING -p tcp --dport 8123 -j DNAT --to 192.168.141.235:80
sudo iptables -t nat -A POSTROUTING -p tcp -s 192.168.141.1 -j SNAT --to 192.168.141.226
sudo iptables -t nat -A PREROUTING -p tcp -s 192.168.141.235 --sport 80 -j DNAT --to 192.168.141.1
sudo iptables -t nat -A POSTROUTING -p tcp -s 192.168.141.235 --sport 80 -j SNAT --to 192.168.141.226:8123

sudo iptables -t nat -A OUTPUT -p tcp --dport 8123 -j DNAT --to 192.168.141.235:80
```

To disable traffic
To enable ftp
```
Must enable ip_conntrack_ftp 

Modprobe ip_conntrack_ftp
```
You should use ESTABLISHED and RELATED at the same time. Otherwise, either the command or the data connection can’t be established. 


the secure version of telnet: ssh
Besides encryption, ssh has another function: port forwarding
Using ssh port forwarding, firewall rules can be bypassed
```
sudo iptables -A INPUT -i lo -j ACCEPT

sudo iptables –t nat –A OUTPUT –p tcp –dport 21 –j DEDIRECT –to-ports 7001
```

ftp server: only allows localhost ftp service
  Also demonstrate ftp data and control connections
  On the server, ftp is blocked
  On the client, we try to do ssh port forwarding
```
sudo iptables -t nat -A OUTPUT -p tcp --dport 21 -j DNAT --to-destination 127.0.0.1:7002

ssh -L 7002:localhost:21 guoyan@192.168.137.151
```

Support squid to act as a web proxy
`iptables -t nat -A PREROUTING  -p tcp --dport 80 -j REDIRECT --to-ports 3128`
