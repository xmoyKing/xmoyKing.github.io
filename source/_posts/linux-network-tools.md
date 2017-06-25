---
title: Linux Network Tools
categories:
  - it
tags:
  - linux
  - network
date: 2017-06-25 10:09:30
updated: 2017-06-25 10:09:30
---

主要总结了一些Linux下不熟悉但是却常用的网络工具，包括nc（NetCat）、iptables（linux下的防火墙）、raw socket（原始套接字）、 sniffer（嗅探器）、以及ip/icmp报头格式。（主要是英语，未做翻译）。


### Some instructs

#### `ifconfig`	
Network configuration and status
`ifconfig` – status of all network interfaces
`ifconfig eth0` – status of ethernet 0 connection
`ifconfig eth0 down` – shuts ethernet 0 down
`ifconfig eth0 up` – starts ethernet 0
`ifconfig eth0 172.16.13.97` – assigns IP address to ethernet 0
`man ifconfig` – more info

#### `route` 
Configure or report status of host's routing table
```
route -n

Kernel IP routing table
Destination     Gateway         Genmask         Flags Metric Ref    Use Iface
192.168.0.0     0.0.0.0         255.255.255.0   U     0      0        0 vmnet8
127.0.0.0       0.0.0.0         255.0.0.0       U     0      0        0 lo
```
`route -?` / `man route` - get help information

#### `traceroute` 
Determines connectivity to a remote host
Uses UDP
Options
-f	set initial ttl
-F	set don't frag bit
-I	use echo request instead of UDP
-t	set type of service
-v	verbose output

#### `Tracert` in Windows
#### `nslookup` 
online web tool to lookup and find IP address information in the DNS (Domain Name System)
#### `host` 
Forward and reverse DNS lookups 
#### `whois` 
whois is to discover who owns a website or domain name by searching WHOIS database.
When you register a domain name, the Internet Corporation for Assigned Names and Numbers (ICANN) requires your domain name registrar to submit your personal contact information to the WHOIS database. Then the information will be public.
#### `netstat`
Show the status of all network connections
Shows all listening ports
```
netstat -s statistic
netstat -p with pid;
netstat -a list all ports;
netstat -at list all tcp port;
netstat -au list all udp ports;
netstat -l list all listening ports;
netstat -lt; 
netstat -lu;
netstat -r display routing information;
netstat -i interface information;
```

#### `tcpdump`    
Packet sniffer
Installed with Linux
Commonly used
Often used as the data file for GUI backends

Syntax:
```
tcpdump (options) –I (interface) –w (dump file)
  eg: tcpdump –c 1000 –i eth0 –w etho.dmp

OPTIONS:
-n		do not convert host addresses to names
-nn		do not convert protocols and ports to names
-i ethn		listen on interface eth0, eth1, eth2
-c xx		exit after xx packets
-e		print link level info
-f file_name	read packets from file file_name
-v		slightly verbose
-vv		verbose
-vvv		very verbose
-w file_name	write packets to file file_name
-x		write packets in hex
-X		write packets in hex and ASCII
-S		write absolute sequence and acknowledgment numbers
```

#### netcat
Reference In Chinese：[Linux nc命令用法收集](http://www.cnblogs.com/jnxb/p/3940593.html)

Copies data across network connections.
Uses UDP or TCP.
Reliable and robust.
Used directly at the command level.
Can be driven by other programs and scripts.
Very useful in forensic capture of a live system.

Simple paradigm
  On the remote collecting system open a listening port.
  On current/compromised system pipe data to remote system.
  Connection is closed automatically after data transfer has completed.

nc the swiss armyknife
```
nc -l 1234  (listen)
nc localhost 1234
```
which will establish a communication tunnel; 
which is convenient way to talk to each other;when combined with redirection, it can be used to transfer file:
```
nc -l 1234 > test
cat file | nc localhost 1234
```

```
echo -e "GET / HTTP/1.0\n\n" | nc localhost 80
```
which will show the homepage with header; nc doesn't do httpsmeans it will show success with `nc -vv localhost 443`; but not homepage

#### `nmap`
Nmap is the most popular scanning tool used on the Internet.
```
nmap  localhost
nmap  localhost 192.168.137.221
nmap  192.168.137.216-221

nmap –O 192.168.137.221
nmap –O 192.168.137.1

```

#### `arp`	
Address Resolution Protocol: ARP and RARP
```
32 bit Internet address
  ↓ ARP         ↑ RARP
48 bit ethernet address
```

ARP Protocol Flow：
1. Machine A wants to send a packet to B, knowing only B’s IP address
2. Machine A broadcasts ARP request with B’s IP address
3. All machines on the local network receive the broadcast
4. Machine B replies with its physical address
5. Machine A adds B’s address information to its ARP table
6. Machine A deliver packet directly to B

ARP caching: 
- To reduce communication cost, ARP maintain a cache of recently acquired IP-to-physical address bindings.
- Each entry has a timer (usually 20 minutes)
- Sender’s IP-to-address binding is included in every broadcast; 
- receivers update the IP-to-physical address binding information in the cache before processing ARP packet
- ARP is stateless: system will update with a reply, regardless of request

`arp –a` example:
```
  Internet Address      Physical Address      Type
  192.168.0.9         00-0b-cd-d3-6e-91     dynamic
  192.168.0.142       00-1e-90-be-ec-93     dynamic
  192.168.0.254       00-0b-45-f6-98-00     dynamic
```

ARP Cache Poisoning：
Sending a forged ARP reply, a target system would send frames destined for the victim to the attacker;
There are various ways to conduct cache poisoning: broadcast, reply, gratuitous ARP message

ARP: an attack example：
![ARP Attack.png](./arp_attack.png)

ARP poisoning:
- Attacker impersonates a gateway, intercept the traffic, either send it to the actual default gateway (passive sniffing) or modify the data before forwarding it (man-in-the middle attack)
- DoS: by associating a nonexistent MAC address to the IP address of the victim’s default gateway


#### Netwox
Tool to send out network packets of different types and with different contents (Netwag is the GUI version)
Netwox consists 222 tools, each with a specific number, some should work with root privilige
`Netwox number [parameters …]`

```
netwox 72 --help
```
Title: Scan ARP (EthIp spoof)
Usage: `netwox 72 -i ips [-d device] [-E eth] [-I ip]`
Parameters:
```
 -i|--ips ips                   list/range of IP addresses {1.2.3.4,5.6.7.8}
 -d|--device device             spoof device {Eth0}
 -E|--src-eth eth               source ethernet address {0:a:a:a:a:a}
 -I|--src-ip ip                 source IP address {1.2.3.4}
 --help2                        display help for advanced parameters
 ```


```
netwox 80 –eth –ip –eth-dst  --ip-dst
```
Title: Periodically send ARP replies
Usage: `netwox 80 -e eth -i ip [-d device] [-E eth] [-I ip] [-s uint32]`
Parameters:
```
 -e|--eth eth                   ethernet address {00:0C:29:26:7F:F0}
 -i|--ip ip                     IP address {192.168.206.161}
 -d|--device device             device for spoof {Eth0}
 -E|--eth-dst eth               to whom answer {0:8:9:a:b:c}
 -I|--ip-dst ip                 to whom answer {5.6.7.8}
 -s|--sleep uint32              sleep delay in ms {1000}
 --help2                        display full help
```