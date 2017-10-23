---
title: JavaScript高级程序设计-24-Ajax和Comet
categories: js
tags:
  - js
  - js-pro
  - ajax
  - comet
  - web sockets
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
XHR2级规范不是所有浏览器都实现了，但基本的内容都实现了。

XHR2级规范了FormData类型，为序列化表单以及创建与表单格式相同的数据提供遍历，XHR对象能自动识别传入的数据类型是FormData的实例，所以可以不必明确在XHR对象上设置头部。
```js
var data = new FormData();
data.append('name', 'value');

// 也可以通过传入表单初始化数据
var data = new FormData(document.forms[0])

// 最后使用send发送即可
xhr.send(data);
```

XHR2.0规范添加了对timeout超时事件的处理，但只有IE8+支持。

#### 进度
W3C有一个Progress Events规范的草案，用于定义客户端服务器之间通信的进度事件。分为以下几种：
- loadstart 接收到响应数据第一个字节时触发
- progress 接收响应期间不断触发
- error 发生错误时触发
- abort 因调用abort方法而终止时触发
- load 接收完成时触发
- loadend 通信结束触发（包括abort、load、error），目前没有浏览器支持

在onprogress事件处理过程中，event对象中有三个额外的属性：
- lengthComputable 表示进度信息是否可用的布尔值
- position 表示已经接收到的字节数
- totalSize 表示预期字节数（根据响应头部的Content-Length得出）

```js
xhr.onprogress = function(event){
  if(event.lengthComputable){
    console.log('received'+ event.position + ' of ' + event.totalSize+ ' bytes!')
  }
}
```

### 跨域资源共享
CORS（Cross-Origin Resource Sharing）是W3C的一个草案，定义为在必须访问跨域资源时，浏览器与服务器应该如何沟通，CORS的基本思想是：使用自定义的HTTP头部让服务器和浏览器进行交互，从而决定请求或响应是否应该继续。

比如，一个请求（主体内容是text/plain）发送时浏览器会自动添加一个额外的Origin头部，表示此请求的页面的源信息（即协议、域名、端口，例如`Origin: 'http://www.xxx.com'`），让服务器知道是谁发起的请求，然后确定是否给与响应,若服务器认为此请求是合法的则在响应中添加一个自定义头部信息，内容与Origin相同,`Access-Control-Allow-Origin: 'http://www.xxx.com'`，浏览器会自动解析响应中的头部，若响应头部的`Access-Control-Allow-Origin`和请求的`Origin`不匹配则浏览器会忽略响应同时报错。

XHR标准原生支持跨域，但有一些限制：
- 不能使用setRequestHeader方法设置自定义头部
- 不带Cookie
- 调用getAllResponseHeaders方法返回空字符串

这样的限制是为了安全，尽量避免CSRF(Cross-Site Request Forgery 跨站请求伪造)和XSS(Cross-Site Scripting 跨站脚本)问题、

IE8+引入的XDR(XDomianRequest)类型部分实现了CORS规范，与XHR类似，但有一些不同：
- 只能是GET/POST
- 只能设置Content-Type字段
- 都是异步的，没有同步
- 出错时只能用onerror事件监听到

CORS虽然不能通过setRequestHeader方法设置自定义头部，但通过一个Preflighted Request（透明服务器验证机制）可以支持自定义头部、使用GET/POST之外的方法、不用类型的主体内容。使用时需要用到OPTION方法，除了Origin，同时会发送一些额外头部：
- Access-Control-Request-Method， 请求使用的方法
- Access-Control-Request-Headers, 自定义的头部信息，多个则用逗号分隔

服务器接收到请求后也需要将上述的两个额外方法返回，同时会设置一个Access-Control-Max-Age用于表示此Preflight请求缓存时长

默认情况下CORS是不需要发生凭据的（例如Cookie、HTTP认证信息、SSL证明等都视为凭据）,但通过将withCredentials属性为true可以指定某个请求发生凭据（IE10-不支持），若服务器接收此带凭据的请求则响应中带有`Access-Control-Allow-Crendentials:true`。若服务器没有返回此头部则浏览器不会将响应交给请求事件。

其实，还有很多方式可以不依赖XHR对象，直接实现跨域请求，而且不用修改浏览器端代码。

图像Ping：img标签可以从任何地址加载图像，不用担心跨域的问题，这也是在线广告跟踪浏览量的主要方式，动态创建img标记，通过onload和onerror事件来确定是否接收到了响应。
图像Ping是与服务器进行简单、单向的跨域通信的一种方式，请求的数据是通过查询字符串形式发送的，而响应可以是任意内容，但通常是像素图或204响应，通过图像Ping，浏览器得不到任何具体的数据，但通过监听load和error事件，能指定响应是什么时候接收到的。
缺点是只能发生GET请求，而且无法访问服务器的响应文本，即只能用于简单的单向通信。
```js
var img = new Image();
img.onload = function(){
  // ...
}
img.src = 'http://www.xx.com/test?name=value';
```

JSONP,填充式JSON/参数式JSON(JSON with Padding)，是应用JSON的一种方法，JSONP其实就是服务器返回一个包含在函数调用同时附带的参数为JSON格式。一般情况下，请求由2部分组成：url和回调函数名,其中回调函数名即响应数据中执行语句的函数名。
JSONP是通过动态script元素来使用的，使用时为src指定一个跨域URL，因为script和img标签类似，都无视域的限制。
缺点是想要知道请求是否成功比较麻烦，虽然能通过onerror事件，但其支持不足。
```js
function responseCallBackFunc(res){
  // ...
}

var script = document.createElement('script');
script.src = 'http://www.xx.com/test/json?callback=responseCallBackFunc';
document.body.insertBefore(script, document.body.firstChild);
```

#### Comet
Comet是Alex Russll发明的一个词，指的是一种使用Ajax的技术，与Ajax从页面向服务器请求数据不同，Comet是一种服务器向页面推送数据的技术，Comet基本上能达到实时推送，这类技术被称为“服务器推送技术”。

有两种Comet实现方式：***长轮询**和**HTTP流**

长轮询是传统轮询（短轮询）的改版，即浏览器向服务器定时请求，看看有没有更新，优势是浏览器都支持，而且使用XHR对象和setTimeout即可实现。
- 短轮询，每次请求服务器立即返回，连接关闭，然后一段时后页面又请求
- 长轮询，每次请求服务器不立即返回，而是直到有数据才返回，连接关闭，页面处理完后立即再次请求
长轮询与短轮询都需要在浏览器接收数据之前发起请求，而两者的主要区别是：服务器如何响应，短轮询是服务器立即响应，无论是否有效，而长轮询是等待发生响应。

HTTP流不同于轮询，因为它在页面的整个生命周期内只是用一次HTTP连接，具体就是浏览器向服务器发生一个请求，而服务器保存连接打开，然后周期性地向浏览器发生数据，即输出缓存然后刷新（将输出缓存中的内容一次性全部发生到客户端是后端语言都支持的）。
浏览器通过XHR对象实现流，对readystatechange事件检测readyState的值，当readyState的值为3时则表示responseText属性接收到了所有的数据，此时比较以前接收到的数据即可知道从何处开始是新数据，当readyState为4则表示连接关闭。
```js
var received = 0,
    xhr = new XMLHttpRequest();

xhr.onreadystatechange = function(){
  var result;

  if(xhr.readyState == 3){
    result = xhr.responseText.substring(received);
    received += result.length;
    // ...
  }else if(xhr.readyState == 4){
    // ...
  }
}
```

#### 服务器推送技术
有新的标准为Comet创建了2个新的接口（IE不支持） ：
SSE（Server-Send Events 服务器推送事件）API用于创建到服务器的单向链接，服务器通过这个链接可以发生任意数据，服务器响应的MIME类型必须是text/event-stream，而浏览器中的js API能解析格式输出，SSE支持短轮询、长轮询、HTTP流，而且能在断开连接时自动重连。

SSE的API与其他消息API类似，先创建一个EventSource对象并传入一个连接地址，此地址必须要同源，然后监听EventSource对象的事件即可。
EventSource有3个事件，open/message/error分别对应连接建立、收到响应数据、建立失败的情况。
EventSource对象有一个readyState属性，当值为0表示正在连接、1表示打开连接成功、2表示关闭了连接。响应的数据保存在event.data中。
最后可以调用close方法关闭连接而且不再自动重连。
```js
var source = new EventSource('xxx.php');

source.onmessage = function(event){
  event.data; //
}
```

当响应的MIME类型为text/event-stream时，则表示此时为事件流，响应的格式是纯文本，且每个数据项都带有前缀`data:`,例如：
```js
data: foo
data: bar
data: faa
```
则第一个message事件中event.data值为foo，第二个message事件中event.data值为bar，第三个message事件中event.data值为faa。
通过`id:`前缀可给指定的事件指定一个关联ID，这个ID位于`data:`行前后都可，
```js
data: foo
id: 1
```
通过id，EventSource对象会自动跟踪上次触发的事件，主要用在断开时向服务器发生一个包含名为Last-Event-ID的特殊HTTP头部请求，告知服务器下一次触发那个事件，在多次连接的事件流中，这种机制可以确保浏览器以正确的顺序收到连接的数据段。

#### Web Sockets
Web Sockets的目标是在一个单独的持久链接上提供全双工、双向同学，在js创建Web Socket后会有一个HTTP请求发生到服务器，在取得服务器响应后，建立的连接会从HTTP变为Web Socket协议，因为标准的HTTP链接无法实现Web Sockets。

由于WebSockets使用自定义的协议，所以URL模式和普通连接不用，未加密的连接是`ws://`，加密的连接是`wss://`，自定义协议的好处是能够在客户端和服务器之间交换少量的数据，而不必担心HTTP字节的开销，WebSocket非常适合移动应用，因为传递的数据包很小。但缺点是协议制定周期太长。

WebSocket构造函数需要一个绝对地址，同时同源策略对其不适用，因此可以打开任何站点的链接，是否与某个域通信取决于服务器。在实例化Web Socket对象后浏览器会立即尝试创建链接，与XHR类似，有一个表示当前状态的readyState属性，但与XHR不同：
- WebSocket.OPENING，值为0，表示正在建立连接
- WebSocket.OPEN，值为1，表示已经建立连接
- WebSocket.CLOSING，值为2，表示正在关闭连接
- WebSocket.CLOSE，值为3，表示已经关闭连接
WebSocket的send方法用于发送数据（只能是文本数据），没有readystatechange事件，对应的是message事件，关闭连接为close方法，关闭后readyState值为2，成功关闭后为3。

WebSocket对象的还有3个事件对应连接生命周期的不同阶段触发：
- open，在成功建立时触发
- error，发生错误时触发
- close，在连接关闭时触发
close事件中event对象有额外的信息，分别是：
- wasClean，布尔值，表示链接是否已经明确的关闭
- code，服务器返回的数值状态码
- reason，字符串，包含服务器发回的消息，一般用于记录日志或显示给用户

WebSocket不支持DOM2级事件监听，只支持DOM0级。
```js
var socket = new WebSocket('ws://www.xxx.com/server');
socket.onclose = function(event){
  event.data;
}
socket.send('{name:king}');
socket.onmessage = function(event){
  event.data;
}

socket.close();
```

SSE和WebSocket都能完成服务器推送的功能，如何选择需要考虑几个因数：
1. 由于WS协议不同于HTTP，所以需要服务器支持该协议
2. 是否需要双向通信，若只是读取服务器数据则SSE即可，但若需要双向通信则使用WS更好。而组合SSE和XHR也可以实现双向通信

