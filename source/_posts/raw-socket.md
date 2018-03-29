---
title: Raw Socket 原始套接字
categories:
  - linux
tags:
  - linux
  - 网络
  - 安全
  - IP
  - ICMP
  - raw socket
  - ping
date: 2017-06-17 15:26:35
updated: 2017-06-17 15:26:35
---

### Problems: Socket
"sockets“ like that does not fit all our needs. lack some functionality some of which is given below:
- cannot read/write ICMP or IGMP protocols with normal sockets, ping(8) tool cannot be written using them.
- For IPv4 protocols other than ICMP, IGMP, TCP or UDP, What if we have a proprietary protocol that we want to handle? How do we send/receive data using that protocol?
- For hackers, how to construct special kind of packets for special purposes?

#### What is raw socket?
- A different mechanism is needed: packets WITHOUT TCP/IP processing -- raw packet
- allow user to bypass partly how computer handles TCP/IP. the packets were sent to the raw sockets user defines rather than the TCP/IP stack
- User should write program to wrap the data, e.g. to fill the headers, instead of kernel
- Raw sockets provide "privileged users" with the ability to directly access raw protocol

#### Why Use Raw Socket?
To conclude:
- Using higher-layer socket programming (connect, bind, listen, read, write), user has no control over the packets
- Raw sockets enables user to send spoofed IP packets, thus to build scanners etc.

#### 4 layer network
```
----------------------------------------------------------
| 4. Application	 | telnet, ftp, dns etc. 	  |
--------------------------------------------------------
| 3. Transport 	 | TCP UDP	 		  |
----------------------------------------------------------
| 2. Network	 	 | IP ICMP IGMP 		  |
----------------------------------------------------------
| 1. Link 		 | device driver, network adapter |
----------------------------------------------------------
```

#### Link Layer
Very first of the TCP/IP layers. When the packet is received off the wire, the early processing is done in here. Duties include:
  1. send/receive datagrams for the IP protocol
  2. send/receive ARP requests and replies for the ARP protocol
  3. send/receive RARP request and replies for the RARP protocol

#### IP header
`/usr/include/netinet/ip.h`:
```c
struct ip {
        u_int   ip_hl:4,                /* header length */
                ip_v:4;                 /* ip version */
        u_char  ip_tos;                 /* type of service */
        u_short ip_len;                 /* total length */
        u_short ip_id;                  /* identification */
        u_short ip_off;                 /* fragment offset */
        u_char  ip_ttl;                 /* time to live */
        u_char  ip_p;                   /* protocol */
        u_short ip_sum;                 /* checksum */
        struct  in_addr ip_src,ip_dst;  /* source and dest address */
};
```

IP header example
```
Field                         	  Length         	 Example
------------------------------	---------------	-------------------
Version                        	   4 bits        	  4
Header length               	   4 bits      	  5
Type of Service                 	   8 bits       	  0
Total length of the whole         16 bits      	  45
datagram
Identification                  	   16 bits      	  43211
Flags                           	    3 bits	       	  0
Fragment Offset	                   13 bits	        	  0
Time to Live (a.k.a TTL)            8 bits	        	  64
Layer III Protocol                       8 bits	       	  6 [TCP]
Checksum                        	    16 bits	       	  0x3a43
Source IP address              	     32 bits         	 192.168.1.1
Destination IP address              32 bits        	 192.168.1.2
```

#### ICMP header
`/usr/include/netinet/ip_icmp.h`:
```c
struct icmphdr
{
  u_int8_t type;                /* message type */
  u_int8_t code;                /* type sub-code */
  u_int16_t checksum;
  union
  {
    struct
    {
      u_int16_t id;
      u_int16_t sequence;
    } echo;                     /* echo datagram */
    u_int32_t   gateway;        /* gateway address */
    struct
    {
      u_int16_t __unused;
      u_int16_t mtu;
    } frag;                     /* path mtu discovery */
  } un;
};
```

ICMP header Example
```c
icmp->icmp_type = ICMP_ECHO;
icmp->icmp_code = 0;
icmp->icmp_cksum = 1;
icmp->icmp_id = 2;
icmp->icmp_seq = 3;
```

### RAW SOCKET API
Just like normal sockets, we create raw sockets with the socket(2) system call:
- int socket(int domain, int type, int protocol)
- type and protocol parameters are set to SOCK_RAW and protocol name accordingly:
```c
if ((sd = socket(AF_INET, SOCK_RAW, IPPROTO_ICMP)) < 0) {
   ..
}
```

Write a simple ping:
```c
#include <sys/types.h>
#include <unistd.h>
#include <errno.h>
#include <signal.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <arpa/inet.h>
#include <linux/tcp.h>
#include <netinet/ip_icmp.h>
#include <strings.h>
#include <stdio.h>
#include <stdlib.h>

int sockfd;
struct sockaddr_in target;

unsigned short in_cksum(unsigned short *addr, int len)
{
        int sum=0;
        unsigned short res=0;
        while( len > 1)  {
                sum += *addr++;
                len -=2;
               // printf("sum is %x.\n",sum);
        }
        if( len == 1) {
                *((unsigned char *)(&res))=*((unsigned char *)addr);
                sum += res;
        }
        sum = (sum >>16) + (sum & 0xffff);
        sum += (sum >>16) ;
        res = ~sum;
        return res;
}


int main(int argc, char * argv[]){
	unsigned short seq=0;
	if(inet_aton(argv[1],&target.sin_addr)==0){
		printf("bad ip address %s\n",argv[1]);
		exit(1);
	}

	struct packet{struct iphdr ip; struct icmphdr icmp;}packet;
        bzero(&packet, sizeof(packet));


	if((sockfd=socket(AF_INET,SOCK_RAW,IPPROTO_RAW))<0)
		{perror("socket()\n"); exit(1);}

	      packet.ip.version=4;
        packet.ip.ihl=5;
        packet.ip.tos=0;
        packet.ip.tot_len=htons(28);
        packet.ip.id=getpid();
        packet.ip.frag_off=0;
        packet.ip.ttl=255;
        packet.ip.protocol=IPPROTO_ICMP;
        packet.ip.check=0;
        packet.ip.daddr=target.sin_addr.s_addr;

//      packet.ip.check=in_cksum((unsigned short*)&packet.ip,20);

        packet.icmp.type=ICMP_ECHO;
        packet.icmp.code=0;
        packet.icmp.checksum=0;
        packet.icmp.un.echo.id=0;
        packet.icmp.un.echo.sequence=0;
        packet.icmp.un.echo.id=getpid()&0xffff;


while(1){
	packet.icmp.un.echo.sequence = seq++;
	packet.icmp.checksum = 0;
	packet.icmp.checksum = in_cksum((unsigned short *)&packet.icmp,8);
	sendto(sockfd, &packet, 28,0,(struct sockaddr *)&target,sizeof(target));
	sleep(1);
}
	return 0;
}
```