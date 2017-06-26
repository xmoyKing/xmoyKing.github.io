---
title: 信息安全小结(简要涉及Web、网络、Linux)
categories:
  - it
tags:
  - security
date: 2017-06-26 18:59:09
updated: 2017-06-26 18:59:09
---


### Web
1. 什么是盒模型
  网页设计中常听的属性名：内容(content)、填充(padding)、边框(border)、边界(margin)， CSS盒子模式都具备这些属性。
  这些属性我们可以用日常生活中的常见事物——盒子作一个比喻来理解，所以叫它盒子模式。
  CSS盒子模型就是在网页设计中经常用到的CSS技术所使用的一种思维模型。

2. CSS定位方式及其特性
  [百度百科：CSS定位](http://baike.baidu.com/item/CSS%E5%AE%9A%E4%BD%8D)
  [HTML和CSS高级指南之二——定位详解](http://www.w3cplus.com/css/advanced-html-css-lesson2-detailed-css-positioning.html)

  static 没有特别的设定，遵循基本的定位规定，不能通过z-index进行层次分级。
  relative 不脱离文档流，参考自身静态位置通过 top(上),bottom（下）,left（左）,right（右） 定位，并且可以通过z-index进行层次分级。
  absolute 脱离文档流，通过 top,bottom,left,right 定位。选取其最近的父级定位元素，当父级 position 为 static 时，absolute元素将以body坐标原点进行定位，可以通过z-index进行层次分级。
  fixed 固定定位，这里他所固定的对像是可视窗口而并非是body或是父级元素。可通过z-index进行层次分级。

3. 点击劫持/利用CSS盗取浏览历史
  利用不可见的iframe覆盖在伪装页面上，骗取用户对实际页面进行操作。
  利用浏览器对超链接不同状态下（未访问状态、鼠标hover/focus状态、已访问状态）链接的颜色的不同，可以知道用户的访问历史。

### HTTPS
1. HTTPS的作用
  在HTTP协议的基础上添加了SSL层（Secure Socket Layer），提供数据加密和身份验证功能，保证数据传输的安全性和完整性。
  SSL协议位于TCP/IP协议与各种应用层协议之间，为数据通讯提供安全支持。SSL协议可分为两层：SSL记录协议（SSL Record Protocol）：它建立在可靠的传输协议（如TCP）之上，为高层协议提供数据封装、压缩、加密等基本功能的支持。SSL握手协议（SSL Handshake Protocol）：它建立在SSL记录协议之上，用于在实际的数据传输开始前，通讯双方进行身份认证、协商加密算法、交换加密密钥等。

  具体作用：
  - 保障用户隐私信息安全：SSL证书让网站实现加密传输，可以很好的防止用户隐私信息如用户名、密码、交易记录、居住信息等被窃取和纂改。比如电商网站安装SSL证书，就可以有效保障你登录电商网站支付时提交的用户名密码的安全。
  - 帮助用户识别钓鱼网站：SSL证书可以认证服务器真实身份，可以有效的区别钓鱼网站和官方网站。网站部署全球信任的SSL证书后，浏览器内置安全机制，实时查验证书状态，通过浏览器向用户展示网站认证信息，让用户轻松识别网站真实身份，防止钓鱼网站仿冒。
  - 利于网站SEO优化：因为部署了SSL证书的网站相比没有部署SSL证书的网站更加可信，更加安全，可以有效的保障用户的利益不受侵害。因此搜索引擎如谷歌，百度站在确保用户信息安全的角度，都在大力倡导网站部署SSL证书实现https加密访问。在搜索、展现、排序方面也给予部署了SSL证书网站优待。
  - 提升公司品牌形象和可信度：网站部署SSL证书，让您的网站与其他网站与众不同。部署了SSL证书的网站会在浏览器地址栏显示https绿色安全小锁，如果是部署的EV SSL证书还会显示绿色地址栏和单位名称。可告诉用户其访问的是安全、可信的站点，可以放心的进行操作和交易，有效提升公司的品牌信息和可信度。

2. CSRF是什么，如何利用HTTPS防御CSRF
  [了解XSS/CSRF](http://xmoyking.github.io/2017/06/06/xss-csrf/)

  CSRF攻击的主要目的是让用户在不知情的情况下攻击自己已登录的一个系统，类似于钓鱼。如用户当前已经登录了邮箱，或bbs，同时用户又在使用另外一个，已经被你控制的站点，我们姑且叫它钓鱼网站。这个网站上面可能因为某个图片吸引你，你去点击一下，此时可能就会触发一个js的点击事件，构造一个bbs发帖的请求，去往你的bbs发帖，由于当前你的浏览器状态已经是登陆状态，所以session登陆cookie信息都会跟正常的请求一样，纯天然的利用当前的登陆状态，让用户在不知情的情况下，帮你发帖或干其他事情。

  CSRF防御
  - 通过 referer、token 或者 验证码 来检测用户提交。
  - 尽量不要在页面的链接中暴露用户隐私信息。
  - 对于用户修改删除等操作最好都使用post操作 。
  - 避免全站通用的cookie，严格设置cookie的域。

### XSS
1. XSS原理、如何防御
  [了解XSS/CSRF](http://xmoyking.github.io/2017/06/06/xss-csrf/)
  恶意攻击者往Web页面里插入恶意Script代码，当用户浏览该页之时，嵌入其中Web里面的Script代码会被执行，从而达到恶意攻击用户的特殊目的。

  XSS防御：
  - 编码：不能对用户所有输入保持原样对用户输入的数据写入HTML文档
  - 过滤：把输入不合法的过滤掉，保持安全性

2. HTTPONLY属性作用
  [利用HTTP-only Cookie缓解XSS之痛](http://netsecurity.51cto.com/art/200902/111143.htm)
  特性是为Cookie提供了一个新属性，用以阻止客户端脚本访问Cookie，缓解跨站点脚本攻击带来的信息泄露风险。
  使用HTTP-only Cookie后，Web 站点就能排除cookie中的敏感信息被发送给黑客的计算机或者使用脚本的Web站点的可能性。

3. Ajax异步特性
  [Ajax知识体系大梳理](http://louiszhai.github.io/2016/11/02/ajax/)*非常全，而且讲到浏览器四种线程之间的配合*
  [AJAX工作原理及其优缺点](http://www.cnblogs.com/SanMaoSpace/archive/2013/06/15/3137180.html)*简版*
  Ajax其核心有JavaScript、XMLHTTPRequest、DOM对象组成，通过XmlHttpRequest对象来向服务器发异步请求，从服务器获得数据，然后用JavaScript来操作DOM而更新页面。

  与传统的web应用比较
  传统的Web应用交互由用户触发一个HTTP请求到服务器,服务器对其进行处理后再返回一个新的HTHL页到客户端, 每当服务器处理客户端提交的请求时,客户都只能空闲等待,并且哪怕只是一次很小的交互、只需从服务器端得到很简单的一个数据,都要返回一个完整的HTML页,而用户每次都要浪费时间和带宽去重新读取整个页面。这个做法浪费了许多带宽，由于每次应用的交互都需要向服务器发送请求，应用的响应时间就依赖于服务器的响应时间。这导致了用户界面的响应比本地应用慢得多。
  与此不同，AJAX应用可以仅向服务器发送并取回必需的数据，它使用SOAP或其它一些基于XML的Web Service接口，并在客户端采用JavaScript处理来自服务器的响应。因为在服务器和浏览器之间交换的数据大量减少，结果我们就能看到响应更快的应用。同时很多的处理工作可以在发出请求的客户端机器上完成，所以Web服务器的处理时间也减少了。


### nc（NetCat）
1. `nc -l 1234`是什么意思
  作为服务器监听本机的1234端口

### Linux权限
1. `chmod 4755 some_binary`的作用
  改变some_binary文件的权限，文件所有者（owner）权限为可读可写可执行，用户组（group）和其他人（others）的权限为可读不可写可执行，同时执行者有SUID权限，即执行该文件时临时拥有文件所有者（owner）的权限。
  参考：
  Linux权限入门：[Linux下用户组、文件权限详解](http://www.cnblogs.com/123-/p/4189072.html)
  Linux特殊权限标志位：[ Linux文件权限标志uid gid](http://blog.csdn.net/u011191259/article/details/50316581)

### IP/ICMP
1. 通常的IP头最开始的01000101表示什么意思
  表示IP版本和头部长度，0100表示ipv4，0101表示头部长度为5，单位为4字节，即头部长度为20字节

2. 为什么IP需要分片
  [IP/ICMP Security](http://xmoyking.github.io/2017/06/23/ip-icmp/)
  在IP层一个IP报文包括头部最大长度为65535字节，但是在其他层，比如链路层以太网最大的帧长度为1500，这种情况下为了保存数据的完整，就必须将IP进行分片。

3. TearDrop的原理（IP分片攻击）
  [什么是Teardrop攻击](http://blog.csdn.net/nny715/article/details/7354961)

4. ICMP重定向攻击原理


### Nmap
1. nmap工具判断对方操作系统等信息原理
2. nmap connect() 与nmap syn的异同
3. nmap idle scan原理

### iptables
1. NAT原理
2. 熟知端口
3. iptables 4个表及其作用
4. 利用iptables禁止外部访问，内部可访问https网站

### Rootkit
1. 什么是Rootkit
2. 系统调用劫持过程
3. `/proc`作用，`ps`命令工作原理


### Buffer Overflow
1. 发生函数调用时的堆栈结构（通用）
2. 如何利用Buffer Overflow获取Rootshell


[PS: SYN Foold ，IP欺骗DOS ，UDP洪水，Ping洪流 ，teardrop ，Land ，Smurf ，Fraggle 攻击 原理](http://blog.csdn.net/zhangnn5/article/details/6525442)