---
title: javascript多线程
categories:
  - fe
tags:
  - fe
  - js
  - Concurrent.Thread.js
  - webwork
date: 2016-11-11 21:04:34
updated: 2016-11-11 21:04:34
---

了解js多线程，主要分为Concurrent.Thread.js（对不支持webwork的浏览器）和WebWork两部分

[JavaScript多线程初步学习](http://www.cnblogs.com/fanfan-nancy/p/5722234.html)

[拔开云雾见明月 透析JavaScript定时机制](http://developer.51cto.com/art/201007/211468_all.htm)

[从JavaScript的单线程执行说起](http://www.raychase.net/1968)

### Concurrent.Thread.js
利用setTimeout和setInterval模拟多线程的一个库 [github备份地址](https://github.com/bringmehome/Concurrent.Thread.js)

[JavaScript 编写线程代码引用Concurrent.Thread.js](http://www.cnblogs.com/0banana0/archive/2011/06/01/2067402.html)

阅读源码需对setTimeout和setInterval有相当的理解才行！

### WebWork
H5标准规范，是真正的多线程，但是切记，不能对DOM进行操作。 

[HTML5新功能之八 《web works多线程》](http://www.cnblogs.com/couxiaozi1983/p/3799898.html)

[深入 HTML5 Web Worker 应用实践：多线程编程](https://www.ibm.com/developerworks/cn/web/1112_sunch_webworker/)



