---
title: Express4-1入门配置
categories:
  - express
tags:
  - node
  - express4
date: 2017-04-30 11:53:55
updated: 2017-04-30 11:53:55
---

Express提供了轻量级模块，将Node的http模块封装并扩展，能处理路由，响应，cookie等

## 安装
```js
npm install express@4.0.0
// 使用
const express = require('express');
var app = express();
```

## 设置
Express提供了控制服务器的一些设置，比如如何解析JSON，路由和视图，使用set(setting, value),enable(setting)和disable(setting)方法来设置这些值。
```js
// 设置信任代理模式，设置模版引擎为jade
app.enable('trust proxy');
app.diable('strict routing');
app.set('view engine', 'jade');
```
相对应的，使用get(setting)，enabled(setting)和disabled(setting)获取已经设置的值
```js
app.enabled('trust proxy'); // true
app.diabled('strict routing'); // true
app.get('view engine'); // jade
```

常用的设置有：
- env 定义模式字符串，如development表示开发，testing表示测试，production表示生产，默认为process.env.NODE_ENV
- trust proxy 禁用/启用反向代理，默认disabled禁用
- jsonp callback name 定义JSONP请求的默认回调名称，默认值是?callback=
- json replacer 定义JSON replacer回调函数，默认null
- json space 指定格式化JSON响应时使用的空格数量，默认开发模式是2，生产模式是0
- case sensitive routing 禁用/启用区分大小写，默认disabled，如/home和/Home是不一样的
- strict routing 禁用/启用严格路由，默认disabled，如/home和/home/是不一样的
- view cache 禁用/启用视图模版编译缓存，开启后保留编译模版的缓存，默认enabled
- view engine 指定模版引擎，若视图省略文件扩展名，则应为默认
- views 知道模版引擎查找视图模版时的目录(路径)，默认`./views`

## 启动Express服务器
使用Express启动服务器时，需要创建一个实例，并监听端口，如下
```js
const express = require('express');
var app = express();
app.listen(8000); 
```
listen(port)调用底层的HTTP绑定连接到port端口上，然后监听，底层的HTTP连接直接利用了http模块中server对象的listen()方法产生的连接。
express()方法执行返回值是一个回调函数，它映射了传递到http.createServer()和https.createServer()方法的回调函数。
如下代码用express实现HTTP和HTTPS服务器
```js
const express = require('express');
const https = require('https');
const http = require('http');
const fs = require('fs');

var app = express();
var options = {
  host: 'localhost',
  key: fs.readFileSync('ssl/server.key');
  cert: fs.readFileSync('ssl/server.cert');
};
http.createServer(app).listen(80);
https.createServer(options, app).listen(443);
app.get('/', function(req, res){
  res.send('Hello Express');
});
```

## 配置路由
路由可以分为两部分，一个是请求方法method（POST,GET，PUT等），一个URL中的路径，如`/`表示根目录，`/login`表示登录页面

express提供了一些便捷的方法设置路由
```js
// 语法
app.[method](path, [callback……], callback);
// 实例
app.get('/', [middleware……], callback);

app.post('/save',function(req, res){
  res.send('Saved');
});
```
app.all方法会调用指定路径的每个请求，不管是什么method，同时也接受通配符*，在对记录日志等方面非常有用
```js
app.all('*',function(req, res){
  // 处理所有请求……
});
app.all('/users/*',function(req, res){
  // 处理所有users路径上的请求……
});
```

复杂的系统，会有许多的路由，为了减少路由数量，可以使用参数来定义如何处理一类路由，express提供了四个方法来实现参数化的路由
### 查询字符串
在URL路径后可以使用标准的HTTP查询字符串如`?key=value&key2=value2`,这个是最简单常用的方法
```js
const express = require('express');
const url = require('url');

var app = express();
app.get('/find', function(req, res){
  var urlparts = url.parse(req.url, true);
  var query = urlparts.query;
  res.send('author:'+ query.author + ' title:'+ query.title);
});
```
上述路由对应的url路径为：`/find?author=king&title=nodejs`

### POST参数
当使用表单或POST方法时，一般是这种方法，在请求body正文中传递参数

### 正则
可以定义一个正则表达式作为路由的路径，express会自动使用这个表达式解析URL
```js
app.get('/^\/book\/(\w+)\:(\w+)?$/', function(req, res){
  res.send('chapter:'+ req.params[0] + ' page:'+ req.params[1]);
});
```
上述路由对应的url路径为：`/book/12:15`


### 已定义的参数
可以在路径部分使用一个参数，当解析路径时，自动为该参数分配名称
```js
app.get('/user/:userid', function(req, res){
  res.send('User:'+ req.params('userid'));
});
```
上述路由对应的url路径为：`/user/9527`

使用已定义的参数可指定服务器执行的回调函数，若express发现某个参数注册了回调函数，则express会在调用路由处理程序之前调用参数的回调函数，一个路由可注册多个回调函数, 使用app.param()方法。
```js
app.param(param, function(req, res, next, value));
```
next参数是注册的喜爱一个回调函数，如注册了多个回调函数的话，必须在回调函数中调用next(),否则回调链会被破坏，value参数是从url路径中解析的参数的值。
```js
app.param('userid', function(req, res, next, value){
  console.log('Userid: ' + value);
  next();
});
```


## Request对象
Request对象即路由回调函数中的第一个参数req，它提供了请求的数据和元数据，包括URL，header，查询字符串等等
```js
const express = require('express');
const url = require('url');

var app = express();
app.listen(80);
app.get('*', function(req, res){
    console.log(req);
    res.send(JSON.stringify(req));
});
```

## Response对象
对应Request对象，Response对象的功能也很强大，提供了很多便捷的方法。

### 响应header
res.set(header, value) 设置单个header，
res.get(header) 获取header的值
res.set(headerObj)
res.location(path) 
res.type()
res.attachment([filepath])

### 设置status
res.status(200) //设置正确返回码

### 发送响应
res.send(body) body是一个字符串或buffer对象
res.send(status, body)

### 发送JSON
res.json(body)
res.json(status, object) 
res.jsonp(body) 发送jsonp，需要请求URL中有知道回调函数参数`?callback=fun`
res.jsonp(status, object)
```js
var express = require('express');
var url = require('url');
var app = express();
app.listen(80);
app.get('/json', function (req, res) {
  app.set('json spaces', 4);
  res.json({name:"Smithsonian", built:'1846', items:'137M',
            centers: ['art', 'astrophysics', 'natural history',
                      'planetary', 'biology', 'space', 'zoo']});
});
app.get('/error', function (req, res) {
  res.json(500, {status:false, message:"Internal Server Error"});
});
app.get('/jsonp', function (req, res) {
  app.set('jsonp callback name', 'cb');
  res.jsonp({name:"Smithsonian", built:'1846', items:'137M',
            centers: ['art', 'astrophysics', 'natural history',
                      'planetary', 'biology', 'space', 'zoo']});
}); 

// http://localhost/json
// http://localhost/error
// http://localhost/jsonp?cb=handleJSONP
```

### 发送文件
res.sendfile()完成将文件发送到客户端需要做的所有操作和设置，该方法内部执行了如下操作：
- 基于文件扩展名设置Content-type类型
- 设置其他header，如Content-length等
- 设置响应status
- 将文件内容发到客户端
```js
// res.sendfile(path, options, callback);

var express = require('express');
var url = require('url');
var app = express();
app.listen(80);
app.get('/image', function (req, res) {
  res.sendfile('arch.jpg', 
               { maxAge: 24*60*60*1000,
                 root: './views/'},
               function(err){
    if (err){
      console.log("Error");
    } else {
      console.log("Success");
    }
  });
});
```

### 发生下载响应
res.download(path, filename, callback);
与sendfile类似，但res.download方法将文件作为HTTP响应的附件发送，即自动设置了Content-Disposition

### 重定向响应
将客户端请求重定向到一个新的位置处理
```js
var express = require('express');
var url = require('url');
var app = express();
app.listen(80);
app.get('/google', function (req, res) {
  res.redirect('http://google.com');
});
app.get('/first', function (req, res) {
  res.redirect('/second');
});
app.get('/second', function (req, res) {
  res.send("Response from Second");
});
app.get('/level/A', function (req, res) {
  res.redirect("/level/B");
});
app.get('/level/B', function (req, res) {
  res.send("Response from Level B");
});
```

## 使用模版引擎
借助模版引擎生成HTML，能做到简单化，不用从头开始编写HTML文件，也能优化构建HTML文档的过程，大多数模版引擎都会将编译后的模版缓存起来。

Express可以使用多种模版引擎，常用的是jade和ejs（内嵌的javascript）,jade的优点是模版文件小，采用速记符号模版，但是缺点是需要重新学习。
ejs的有点就是与现在的html和js语言相兼容，即在html中嵌入js，但是缺点是比html复杂，不如jade简洁。
```js
npm install jade
npm install ejs
```

在express中使用模版引擎需要先定义一个默认的模版引擎，以及设置模版文件的目录
```js
var app = express();
app.set('view engine', 'jade');
app.set('views', './views');
```
然后使用app.engine(ext, callback)注册模版文件扩展名，callback是支持Express的呈现功能函数
```js
app.engine('jade', require('jade').__express);
```
这里的__express方法表示使用默认，也可以为HTML文件扩展名注册EJS,使用renderFile函数使用其他函数执行回调，
```js
app.engine('html', require('ejs').renderFile)
```
一旦扩展名注册后，引擎回调函数就会被调用来解析模版文件并呈现模版。

### 在模版中加入locals对象
在转换一个模版为HTML文件时，常需要包含动态数据，比如数据库中的用户信息，这种情况下，可以生成一个locals对象，将它映射到模版中的对应变量的属性名上即可
```js
// 两种方式——直接定义属性
app.locals.title = 'KING APP'
app.locals.version = 1;
// 通过对象赋值
app.locals({title:'KING APP', version:'1'});
```
以上的title和version都可以自定义，但是不能覆盖原生JS对象的一些属性或方法名称，比如name，apply，bind，call，arguments，length，constructor等

### 创建模版文件
在创建模版文件时，需要注意可重用性，尽量将模版的重用性提高，使其在不同页面都能够使用，因为大多数模版引擎都会缓存模版来加速生成HTML文件，所以，模版越多，缓存可能越多，这种时候，最好将模版根据功能类型分类，比如菜单栏，表格，下拉框等

用于显示用户信息的EJS模版
```js
<!DOCTYPE html>
<html lang="en">
<head>
<title>EJS Template</title>
</head>
<body>
	<h1>User using EJS Template</h1>
	<ul>
		<li>Name: <%= uname %></li>
		<li>Vehicle: <%= vehicle %></li>
		<li>Terrain: <%= terrain %></li>
		<li>Climate: <%= climate %></li>
		<li>Location: <%= location %></li>
	</ul>
</body>
</html>
```

同样功能的jade模版，同时重用了主模版，只修改了子模版
```jade
doctype html
html(lang="en")
  head
    title="Jade Template"
  body
    block content
```
子模版,没有了外面的html声明等标记：
```jade
extends main_jade
block content
  h1 User using Jade Template
  ul
    li Name: #{uname}
    li Vehicle: #{vehicle}
    li Terrain: #{terrain}
    li Climate: #{climate}
    li Location: #{location}
```

**以上EJS和jade的具体语法可以参考官网**

### 将模版生成的HTML文档发送给用户
```js
app.render(view, [locals], callback);
```
view指定views目录中的某个视图文件名，locals指定传递的locals对象，回调函数在模版生成后执行，回调函数的参数中第一个参数为error对象，第二个对象为生成后的模版字符串（HTML文档）。

若需要将模版直接生成的模版字符串作为响应发到客户端，即不需要在发送响应之前对数据做处理，可使用res.render函数，同app.render一样，但是不需要有回调函数。

```js
var express = require('express'),
    jade = require('jade'),
    ejs = require('ejs');
var app = express();
app.set('views', './views');
app.set('view engine', 'jade');
app.engine('jade', jade.__express);
app.engine('html', ejs.renderFile);
app.listen(80);
app.locals.uname = "Brad";
app.locals.vehicle = "Jeep";
app.locals.terrain = "Mountains";
app.locals.climate = "Desert";
app.locals.location = "Unknown";
app.get('/jade', function (req, res) {
  res.render('user_jade');
});
app.get('/ejs', function (req, res) {
  app.render('user_ejs.html', function(err, renderedData){
    res.send(renderedData);    
  });
});
```