---
title: JavaScript高级程序设计-24-Ajax和Comet
categories: js
tags:
  - js
  - js-pro
  - ajax
  - comet
date: 2016-09-14 19:07:57
updated:
---

ajxa是基于XHR的，熟练使用XHR非常重要。

### XMLHttpRequest对象
IE7+以及其他浏览器都支持XHR1.0，XHR现在是2.0版本。通过`new XMLHttpRequest()`创建XHR对象，然后调用open方法指定要发起请求的类型（GET、POST），url，是否异步（true为异步，false为同步）。

然后调用send将要发送的数据传入即可，不传入数据时传入null，send执行后，发起请求后才会收到返回的status数据

xhr在创建后，其对象内有一个readyState属性用于表示当前请求过程的阶段：
- 0 为初始化、尚未调用xhr.open方法
- 1 启动，open执行，但send未执行
- 2 发生，send执行，但尚未收到响应
- 3 接收，收到部分数据
- 4 完成，接受到全部数据

每当readyState发生改变时，会触发readystatechange事件，此时可以对onreadystatechange事件进行监听（必须在open之前，而且只能使用DOM0级的方式）。
```js
xhr.onreadystatechange = function(){   // 此处没有event对象，返回的数据在xhr对象中
  xhr.responseText; // 响应数据
  xhr.responseXml; // xml格式的响应数据，必须是xml格式，若不是则为null
  xhr.status; // 状态码
  xhr.statusText; // 状态名
};
```

在send后，可以执行abort方法取消请求。因为xhr内存对象的问题，建议在停止请求后取消对xhr对象的引用。


#### HTTP头部信息
每次发起xhr请求时都会同时将一些HTTP头部信息发送到服务器：
- Accept 浏览器能够处理的内容类型
- Accept-Charset 浏览器能够显示的字符集
- Accept-Encoding 浏览器能够处理的压缩编码
- Accept-Language 浏览器当前的语言
- Connection 浏览器当前与服务器之间链接的类型
- Cookie 当前页面的Cookie
- Host 请求页面的域
- Referer 请求页面的URL（HTTP规范拼写错误，正确的应该时referrer，但将错就错了）
- User-agent 浏览器用户代理字符串

使用setRequestHeader(headerName, value)可以设置自定义的请求头信息。getRequestHeader('headerName')可以获取对应头部信息，getAllRequestHeaders可以获取所有的头部信息
```js
xhr.setRequestHeader('MyHeader', 'value');
```

#### GET和POST请求
当发起GET请求时，需要对查询字符串的每个值和键名用encodeURIComponent进行编码才能放到URL末尾，键值对之间用&符号分割。

当发起POST请求时，默认情况下服务器对XHR请求和Web表单的请求不是一视同仁的，所以一般情况下为了兼容，除非特殊情况，否则对XHR请求伪装一下，模仿表单提交，将Content-Type头部设置为application/x-www-form-urlencoded, 其实Web的POST数据格式和查询字符串格式相同，所以可以用serialize方法将表单序列化然后传递给服务器。

#### XHR2.0

