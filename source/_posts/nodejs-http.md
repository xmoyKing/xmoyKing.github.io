---
title: Nodejs-HTTP模块
categories:
  - Nodejs
tags:
  - nodejs
  - http
date: 2016-09-28 10:32:45
updated: 2016-09-28 10:32:45
---

Node最重要的能力就是能非常迅速的实现HTTP和HTTPS服务，node提供内置的http和https模块。http模块是底层的，不提供路由，cookie，缓存等功能。

## 处理URL
一个URL可分解为几个不同的部分，指明了Web服务器应该如何路由和处理请求
![URL组成图](1.png)
node提供了url模块，可以将一个url字符串转变为url对象
```js
url.parse(urlStr, [parseQueryString], [salshesDenoteHost]);
```
url.parse 将URL字符串作为第一个参数，
parseQueryString 参数是一个布尔值，true表示把URL的查询字符串也解析为对象的一部分，默认为false
slashesDenoteHost 也是一个布尔值，true表示将格式为`//host/path`的URL解析为`{host: 'host', pathname: 'path'}`而不是`{pathname: '//host/path'}`,默认为false

也可以反转换，url.format(urlObj) 将一个URL对象转换为字符串

### URL 字符串与 URL 对象：
urlObject.href 完整的URL字符串
urlObject.protocol 协议，小写
urlObject.slashes 
urlObject.host 主机完整部分，包括端口，小写
urlObject.auth 身份认证部分
urlObject.hostname 主机部分的主机名，小写
urlObject.port 主机端口号
urlObject.pathname 路径部分，包括最初的斜线
urlObject.search 查询字符串部分，包括前导问号？
urlObject.path 完整路径，包括路径和搜索
urlObject.query 要么是查询字符串的参数部分，要么是一个解析后的查询对象，取决于parseQueryString的值
urlObject.hash 散列部分，包括井号#
```js
var url = require('url');
var urlStr = 'http://user:pass@host.com:80/resource/path?query=string&quer2=2#hash';
var urlObj = url.parse(urlStr, true);
urlString = url.format(urlObj);

console.log(urlObj, urlString);
```
输出：
Url {
  protocol: 'http:',
  slashes: true,
  auth: 'user:pass',
  host: 'host.com:80',
  port: '80',
  hostname: 'host.com',
  hash: '#hash',
  search: '?query=string&quer2=2',
  query: { query: 'string', quer2: '2' },
  pathname: '/resource/path',
  path: '/resource/path?query=string&quer2=2',
  href: 'http://user:pass@host.com:80/resource/path?query=string&quer2=2#hash' 
}

'http://user:pass@host.com:80/resource/path?query=string&quer2=2#hash'

### 解析URL
url模块有一个resolve方法可以用与浏览器一样的方式来解析URL的组件，即可以让服务器端操作URL字符串，并对其做出调整，比如：由于资源变动了，那么可以新添加代码在处理一个url请求之前更改url位置，而不需要对源代码做出任何修改，。
```js
url.resolve(from, to);
```
from指定原始基础url字符串，to指定被解析到的新url
```js
var url = require('url');
var originalUrl = 'http://user:pass@host.com:80/resource/path?query=string&quer2=2#hash';
var newResource = '/another/path?querynew';
console.log(url.resolve(originalUrl, newResource)); // http://user:pass@host.com:80/another/path?querynew
```

### 处理查询字符串和表单参数
url.parse方法能解析url，但是没有解析query字符串，即查询字符串
查询字符串和表单数据都是基本的键值对字符串，格式非常的规则，所以可以用querystring模块的parse方法将这种字符串转换为js对象
```js
querystring.parse(str, [spe], [eq], [options]);
```
str 为查询或参数字符串
sep 可以指定使用的分隔符，默认&
eq 可以指定解析时使用的复制运算符，默认=
options 是一个具有maxKeys属性的对象，可以限制生成对象的键的数量，即属性和方法的数量，默认为1000，设为0表示无限制
```js
var qstring = require('querystring');
var params = qstring.parse('query=string&quer2=2&quer3=mine&name=king');
console.log(params); // { query: 'string', quer2: '2', quer3: 'mine', name: 'king' }
```
以上的输出对象也可以通过`querystring.stringify(obj, [sep], [eq])`反转换对象为查询字符串

## 请求、响应、服务器对象
请求和响应对象提供了信息流入/流出HTTP客户端和服务器的相关功能

### http.ClientRequest
http.request方法可以用node新建一个HTTP客户端，然后就可以如浏览器一样向一个HTTP服务器端发起http请求，
当调用http.request()的时候，即会在request方法内部自动创建一个ClientRequest对象，这个对象即表示请求，可以用这个对象来启动、监控、处理来自服务器的请求。
ClientRequest对象其实是一个Writable流
```js
http.request(options,callback);
```
options 参数对象定义了如何把客户端的HTTP请求打卡并发送到服务器，callback回调的参数是一个IncomingMessage对象，表示来自服务器的响应。
![options参数](2.png)

```js
var http = require('http');
var options = {
    hostname: 'httpbin.org',
    path: '/get',
    port: '80',
    method: 'GET'
};

var req = http.request(options, function(response){
    var str = '';
    response.on('data', function(chunk){
        str += chunk;
    });
    response.on('end', function(){
        console.log(str);
    });
});

req.end();
```
输出：
{
  "args": {},
  "headers": {
    "Connection": "close",
    "Host": "httpbin.org"
  },
  "origin": "211.86.158.25",
  "url": "http://httpbin.org/get"
}

通过ClientRequest事件能，当在请求中遇到的一些状态的时候，可以进行处理
![ClientRequest事件](3.png)

ClientRequest方法可以将数据写入请求，中止请求等
![ClientRequest方法](4.png)

### http.ServerResponse
当HTTP服务器接收到一个request请求的时候，它会在内部创建一个ServerResponse对象，这个对象作为第二个参数被传递到request事件处理程序，可以通过ServerResponse将响应发还到客户端。
ServerResponse也是一个Writable流，该对象提供了如下的一些属性和事件、方法：
![ServerResponse属性和事件、方法](5.png)

### http.IncomingMessage
无论是HTTP客户端还是HTTP服务器，都会创建IncomingMessage对象，表示从对应方接收到的消息（数据），它之所以能同时用在客户端和服务器是因为两端的功能基本相同，所以可以复用同一类型。
IncomingMessage是一个Readable流，对象提供了如下的一些属性和事件、方法：
![IncomingMessage属性和事件、方法](6.png)

### HTTP Server对象
HTTP Server对象能提供了实现HTTP服务器的基本框架，如监听端口底层套接字和接收请求，发送响应到客户端。
Server对象实现了EventEmitter，并实现了如下的事件：
![Server对象的事件](7.png)

要启动一个HTTP服务器
1. 先用createServer方法创建一个Server对象，
```js
http.createServer([requestListener]);
```
requestListener参数表示在请求事件被出发时执行的函数，接受两个参数，第一个表示客户端请求的IncomingMessage对象，第二个表示响应的ServerResponse对象
2. 创建Server对象后，可以通过Server对象的listen方法监听
```js
listen(port, [hostname], [backlog], [callback]);
```
port 表示监听端口
hostname 表示可接受链接的主机名，若省略则表示接受任何ipv4地址的链接
backlog（积压） 表示运行排队等待链接的数量，即等待处理的请求链接数，默认511
callback 表示监听开始时执行回调的函数
```js
var http = require('http');
http.createServer(function (req, res){
 // ....  处理请求和响应
}).listen(8080); // 监听8080端口

// 也可以监听文件系统的连接
listen(path, [callback]); // 监听文件路径
listen(handle, [callback]); // 监听文件描述符

// 停止监听
close([callback]);
```

## HTTP客户端和服务器示例
以下的示例为处理一般的http请求所需的基本流程
### 静态文件服务

HTTP静态文件服务器
```js
const http = require('http');
const fs = require('fs');
const url = require('url');

const ROOT_DIR = 'html/'; // 此处为根目录，需设定

http.createServer(function (req, res){
    var urlObj = url.parse(req.url, true);
    fs.readFile(ROOT_DIR + urlObj.pathname, function(err, data){
        if(err){
            res.writeHead(404);
            res.end(JSON.stringify(err));
            return;
        }else{
            res.writeHead(200);
            res.end(data);
        }
    });
}).listen(8080);
```
在localhost:8080 即可打卡网站，然后输入对应的文件名称即可获取对应的文件，比如localhost:8080/index.html 

### 静态HTTP客户端
模拟浏览器发出http get请求，用on监听data读取服务器的响应
```js
const http = require('http');
var options = {
    hostname: 'localhost',
    port: '8000',
    path: '/hello.html'
};

function handleResponse(response){
    var serverData = '';
    response.on('data', function(chunk){
        serverData += chunk;
    }); 
    response.on('end', function(){
        console.log(serverData);
    });
}
http.request(options, function(response){
    handleResponse(response);
}).end();
```

### 实现动态响应服务器
#### 处理get请求的服务器
```js
const http = require('http');
var message = [
    'helloworld',
    'from a basic nodejs server',
    'king'
];

http.createServer(function(req, res){
    res.setHeader('Content-type', 'text/html');
    res.writeHead(200);
    res.write('<html><body>');
    for(var i in message){
        res.write(message[i] + '<br/>');
    }
    res.end('</body></html>');
}).listen(8000);
```

对应的Http get客户端
```js
const http = require('http');
var options = {
    hostname: 'localhost',
    port: '8000',
};

function handleResponse(response){
    var serverData = '';
    response.on('data', function(chunk){
        serverData += chunk;
    }); 
    response.on('end', function(){
        console.log('response status: ', response.statusCode);
        console.log('response headers: ', response.headers);
        console.log(serverData);
    });
}
http.request(options, function(response){
    handleResponse(response);
}).end();
```
输出：
```
response status:  200
response headers:  { 'content-type': 'text/html',
  date: 'Sat, 29 Apr 2017 07:05:09 GMT',
  connection: 'close',
  'transfer-encoding': 'chunked' }
<html><body>helloworld<br/>from a basic nodejs server<br/>king<br/></body></html>
```

#### 处理post请求的服务器
上面处理与get请求的示例类似
```js
const http = require('http');

http.createServer(function(req, res){
    var jsonData = '';
    req.on('data', function(chunk){
        jsonData += chunk;
    });
    req.on('end', function(){
        var reqObj = JSON.parse(jsonData);
        var resObj = {
            message: 'hello ' + reqObj.name,
            question: 'are you a good '+ reqObj.occupation + ' ?'
        };
        res.writeHead(200);
        res.end(JSON.stringify(resObj));
    });
}).listen(8000);
```

对应的客户端
```js
const http = require('http');
var options = {
    hostname: 'localhost',
    port: '8000',
    path: '/',
    method: 'POST'
};

function readJSONResponse(response){
    var responseData = '';
    response.on('data', function (chunk) {
        responseData += chunk;
    });
    response.on('end', function(){
        var dataObj = JSON.parse(responseData);
        console.log('Raw responseData: ' + responseData);
        console.log('Message: ' + dataObj.message);
        console.log('Question: ' + dataObj.question);        
    });
}

var req = http.request(options, readJSONResponse);
req.write('{"name":"bob", "occupation":"teacher"}');
req.end();
```
输出：
Raw responseData: {"message":"hello bob","question":"are you a good teacher ?"}
Message: hello bob
Question: are you a good teacher ?


#### 与外部源交互
HTTP服务常需要访问外部系统获得数据来满足客户端，这种时候就需要在Web服务器实现一个本地Web客户端连接到外部服务(这个外部服务一般是数据库，也可以是其他http服务器提供的接口)，并获得数据然后处理, 完成响应。