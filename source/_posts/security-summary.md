---
title: 信息安全小结(简要涉及Web、网络、Linux)
categories:
  - it
tags:
  - security
date: 2017-06-26 18:59:09
updated: 2017-06-26 18:59:09
---

### nc（NetCat）
1. `nc -l 1234`是什么意思

### Linux权限
1. `chmod 4755 some_binary`的作用

### HTTPS
1. HTTPS的作用
2. CSRF是什么，如何利用HTTPS防御CSRF

### Web
1. 什么是盒模型
2. CSS定位方式及其特性
3. 点击劫持/利用CSS盗取浏览历史

### XSS
1. XSS原理、如何防御
2. HTTPONLY属性作用
3. Ajax异步特性

### IP/ICMP
1. 通常的IP头最开始的01000101表示什么意思
2. 为什么IP需要分片
3. TearDrop的原理（IP分片攻击）
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