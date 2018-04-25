---
title: Node 技巧笔记4 - 网络
categories: Nodejs
tags:
  - JavaScript
  - Nodejs
  - TCP
  - UDP
  - HTTP
  - HTTPS
  - DNS
date: 2018-04-10 11:15:36
updated: 2018-04-10 11:15:36
---

学习Node的网络模块，包括dgram、dns、http、net等模块。其中http基于net、stream、buffer、events等模块，不仅封装良好，且易于扩展。

### 技巧45 创建TCP服务端和客户端
net模块构成了Node网络的基础，通过net.createServer创建一个服务，然后调用server.listen绑定一个端口；连接服务端则用net.conncet创建一个客户端或可以使用telnet连接。

net.createServer方法返回一个对象，用来监听一个指定的TCP端口，当客户端连接上该server时，传递给net.createServer的回调函数将会执行，回调的参数是一个面向事件的对象。

简单TCP服务器实现
```js
const net = require('net');

let clients = 0; // 记录每一个连接的ID

let server = net.createServer(client=>{
  clients++;

  let clientId = clients;
  console.log(`Client connected: ${clientId}`);
  // 通过end事件，追踪用户断开连接
  client.on('end',()=>{
    console.log(`Client disconnected: ${clientId}`);
  });

  client.write(`Welcome client: ${clientId}`);
  client.pipe(client); // 通过管道将客户端数据直接原样返回
});

server.listen(8000, ()=>{
  console.log('Server start on port 8000');
});
```

### 技巧46 使用客户端测试TCP服务器
使用net.connect连接服务端端口。

由于TCP和UDP靠端口识别，所以完全能在一个进程中创建多个服务端和客户端。

通过创建一个进程内服务的客户端链接，然后在数据通过网络发送时运行断言。
```js
const assert = require('assert');
const net = require('net');

let clients = 0; // 记录每一个连接的ID
let expectedAssertions = 2;

let server = net.createServer(client=>{
  clients++;

  let clientId = clients;
  console.log(`Client connected: ${clientId}`);
  // 通过end事件，追踪用户断开连接
  client.on('end',()=>{
    console.log(`Client disconnected: ${clientId}`);
  });

  client.write(`Welcome client: ${clientId} \r\n`);
  client.pipe(client); // 通过管道将客户端数据直接原样返回
});

server.listen(8000, ()=>{
  console.log('Server start on port 8000');

  // 一旦服务端开始监听，则运行runTest函数，其接收预期的客户端ID，并触发回调
  runTest(1, ()=>{
    runTest(2, ()=>{
      console.log('Test finished');
      assert.equal(0, expectedAssertions);
      server.close();
    });
  });
});

// runTest函数接收一个回调，这样可以嵌套测试
function runTest(expectedId, done){
  // connect方法第一个参数为端口号，
  // 第二个参数为可选的IP地址或主机名，默认为localhost
  // TCP是双工的，所以client本身也可以接收/发送数据。
  let client = net.connect(8000);

  client.on('data', data=>{
    let expected = `Welcome client: ${expectedId} \r\n`;

    assert.equal(data.toString(), expected);

    expectedAssertions--;
    client.end();
  });

  // 当客户端断开链接时，触发注册的done回调函数，
  client.on('end', done);
}
```
输出结果如下：
Server start on port 8000
Client connected: 1
Client disconnected: 1
Client connected: 2
Client disconnected: 2
Test finished

### 技巧47 改进实时性底的应用
Node的net模块是相对高层的，其提供了一些底层的方法。比如通过TCP_NODELAY标识判断是否使用Nagle算法。

Nagle算法是指当一个连接有未确定的数据时，小片段应该保留，直到足够的数据被接收，这些小片段将被分批成能够被传输的更大的片段。

当网络中有很多小片段传输时，理想的应该将小片段集合起来一起发送减少拥堵，当若需要实时性高时，则需要关闭Nagle算法，直接传输小片段。

```js
const net = require('net');

let server = net.createServer((c)=>{
  c.setNoDelay(true); // 关闭Nagle算法
  c.write('1231321231313', 'binary'); // 强制客户端使用二进制模式

  console.log('server connected');
  c.on('end', ()=>{
    console.log('server disconnected');
    server.unref(); // 使用unref确保最后一个客户端断开连接
  });

  c.on('data', data=>{
    // 将客户端的数据打印出来
    process.stdout.write(data.toString());
    c.write(data.toString());
  });
});

server.listen(8000, ()=>{
  console.log('server bound');
})
```

### 技巧48 通过UDP传输文件
使用dgram创建数据报socket并使用socket.send发送数据。

因为UDP是无状态协议，所以客户端需要一次性写入一个数据报，而且数据报的大小必须小于65507字节，即小于最大传输单元MTU（Maximum Transimission Unit），其最大为64KB。

```js
const fs = require('fs');
const dgram = require('dgram');

let port = 34535;
let defaultSize = 16;

// 客户端
function Client(ip){
  // 从当前文件创建一个可读流
  let inStream = fs.createReadStream(__filename);
  // 创建一个新的数据流socket作为客户端
  let socket = dgram.createSocket('udp4');

  inStream.on('readable', ()=>{
    sendData();
  });

  function sendData(){
    let message = inStream.read(defaultSize);

    if(!message){
      return socket.unref();
    }

    socket.send(message, 0, message.length, port, ip, (err, bytes)=>{
      sendData();
    });
  }
}

// 服务端
function Server(){
  let socket = dgram.createSocket('udp4');

  socket.on('message', (msg, rinfo)=>{
    process.stdout.write(msg.toString());
  });

  socket.on('listening', ()=>{
    console.log(`Server ready: ${socket.address()}`);
  });

  socket.bind(port);
}

// 检查命令行选项来确定是运行服务端还是客户端
if(process.argv[2] === 'client'){
  new Client(process.argv[3]); // 可选择接受其他可选的IP地址
}else{
  new Server();
}
```

### 技巧49 UDP客户端服务应用
UDP常用于查询-响应协议，比如DNS、DHCP，且都需要将消息发送会客户端。

UDP和TCP不一样，TCP是双工的，所以一旦连接则可以使用client.write写入消息，通过监听data事件获取消息。而UDP是非面向连接的，即不需要连接就可以接收消息。

下例实现了一个简单的客户端-服务端聊天室，允许客户端通过UDP连接到一个中心服务器，并相互通信，服务端在一个数组里保存了每一个客户端，因此能独立映射每一个。通过保存的客户端地址和端口，甚至可以在一个机器运行多个客户端，在同一个电脑多次运行这个程序。

```js
const assert = require('assert');
const fs = require('fs');
const dgram = require('dgram');

let port = 34535;
let defaultSize = 16;

// 客户端
function Client(ip) {
    const readline = require('readline'); // 使用readline模块处理用户输入
    let rl = readline.createInterface(process.stdin, process.stdout);

    let socket = dgram.createSocket('udp4');
    socket.send(new Buffer('<JOIN>'), 0, 6, port, ip);

    rl.setPrompt('Message> ');
    rl.prompt();

    rl.on('line', line => {
        sendData(line);
    }).on('close', () => { // 每当用户回车就将消息发送出去
        process.exit(0);
    });

    // 监听其他用户消息
    socket.on('message', (msg, rinfo) => {
        console.log(`\n<${rinfo.address}:${rinfo.port}> ${msg.toString()}`);
        rl.prompt();
    });

    function sendData(message) {
        socket.send(
            new Buffer(message), 0, message.length, port, ip, (err, bytes) => {
            console.log(`Send: ${message}`);
            rl.prompt();
        });
    }
}


// 服务端
function Server(){
    let clients = {};
    let server = dgram.createSocket('udp4');

    server.on('message', (msg, rinfo)=>{
        let clientId = `${rinfo.address}:${rinfo.port}`;
        msg = msg.toString();

        // 若不存在当前clientId，则保存其连接信息
        if(!clients[clientId]){
            clients[clientId] = rinfo;
        }

        // 若消息没有使用尖括号包裹，则表示是控制信息
        if(msg.match(/^</)){
            console.log(`Control message: ${msg}`);
            return;
        }

        // 将消息发送给其他所有客户端
        for(let client in clients){
            if(client !== clientId){
                client = clients[client];
                server.send(
                    new Buffer(msg), 0, msg.length, client.port, client.address, (err, bytes) => {
                        if(err) console.error(err);
                        console.log(`Bytes send: ${bytes}`);
                });
            }
        }
    });

    server.on('listening', ()=>{
        console.log(`Server ready: ${server.address().port}`);
    });

    server.bind(port);
}

module.exports = {Client, Server};

if(!module.parent){
    switch(process.argv[2]){
        case 'client':
            new Client(process.argv[3]);
            break;
        case 'server':
            new Server();
            break;
        default:
            console.log('Unknown option');
    }
}
```

### 技巧50 HTTP服务器
HTTP协议基于TCP协议，也是无状态的，而Node的http模块则更新是直接基于TCP模块构造的。

HTTP服务器被扩展成为支持多种HTTP协议的元素，解析头部信息，处理响应码，并且设置sockets上的各种事件，Node HTTP处理响应码的重点是解析，本质是一个经过C++封装后的C解析库，这个库能提取头部信息和值，Content-Length、请求方法、响应状态码等。

可以在一个进程中使用http.createServer、http.createClient构建HTTP服务器并进行测试。
```js
const assert = require('assert');
const http = require('http');

let server = http.createServer((req, res) => {
    res.writeHead(200, {'Content-Type': 'text/plain'});
    res.write('Hello, world~\r\n');
    res.end();
});

server.listen(8000, () => {
    console.log('Listening on port 8000');
});

// 使用http.request创建请求
let req = http.request({port: 8000}, res => {
    console.log(`HTTP headers: ${res.headers}`);
    res.on('data', data => {
        console.log(`Body: ${data.toString()}`);

        assert.equal('Hello, world~\r\n', data.toString());
        assert.equal(200, res.statusCode);

        server.unref();
    });
});

req.end();
```

### 技巧51 重定向
Node http模块提供了处理HTTP请求的API，但其无法处理重定向，比如下载页面。如何维护跨多个请求的状态，即重定向如何被正确执行而无需创建重定向循环或其他什么。

HTTP标准定义了表示重定向发生时的状态码，而且它也指出客户端应该检测无限循环。

通过一个简单的类来保留每个请求的状态、重定向和重定向检测循环。
```js
const https = require('https');
const http = require('http');
const url = require('url');

class Request{
    constructor(){
        this.maxRedirects = 10;
        this.redirects = 0;
    }

    get(href, cb){
        let uri = url.parse(href);
        let opt = {host: uri.host, path: uri.path};
        let httpGet = uri.protocol === 'http:' ? http.get : https.get;

        console.log(`GET: ${href}`);

        function processResponse(res) {
            if(res.statusCode >= 300 && res.statusCode < 400){
                if(this.redirects >= this.maxRedirects){
                    this.error = new Error(`Too many redirects for: ${href}`);
                }else{
                    this.redirects++;
                    href = url.resolve(opt.host, res.headers.location);
                    return this.get(href, cb);
                }
            }

            res.url = href;
            res.redirects = this.redirects;

            console.log(`Redirects: ${href}`);

            function end(){
                console.log('Connection ended');
                cb(this.error, res);
            }

            res.on('data', data => {
                console.log(`Got data, length: ${data.length}`);
            });

            res.on('end', end.bind(this));
        }

        httpGet(opt, processResponse.bind(this))
        .on('error', err => {
            cb(err);
        });
    }
}

// 测试访问
let request = new Request();
request.get('http://baidu.com/', (err, res) => {
    if(err) {
        console.error(err);
    }else{
        console.log(`Fetched URL: ${res.url} with ${res.redirects} redirects`);
        process.exit();
    }
});
```