---
title: Express4-2中间件
categories:
  - Nodejs
tags:
  - nodejs
  - express
  - express4
date: 2017-04-30 16:47:31
updated: 2017-04-30 16:47:31
---
Express提供的大部分功能都是通过中间件函数完成的，这些中间件就如同一个又一个封装并扩展了http模块功能的插件，通过他们能极大减少工作量。
中间件的提供了一些功能，一般在接收到请求前，处理请求时，和发送响应时这三者之间调用。比如身份认真，cookie和会话，静态文件，POST请求。

Express中间件框架的底层是connect模块，基于这个底层中间件模块，Express支持许多中间件组件，例如：
- static： 允许服务器以流式处理静态文件的GET请求，这个中间件是Express内置的，不需要安装，通过express.static()就可以使用
- express-logger：一个格式化的请求日志，记录服务器接收到的请求
- basic-auth-connect: 提供对基本HTTP身份验证的功能
- cookie-parse: 读取请求中附带的cookie并设置响应中的cookie
- cookie-session: 提供基于cookie的会话功能
- express-session: 另一种会话功能
- body-parse: 将POST请求中的JSON数据解析为req.body的属性
- compression： 提供Gzip压缩
- csurf： 提供跨站点请求访问伪造保护功能

以上中间件都需要通过npm安装后才能导入使用

### 使用中间件
在Express App对象上使用use([path], middleware)方法，可对所有的路由指定该中间件，省略path则默认`'/'`，表示所有路径，middleware则是一个函数，传递给该函数的参数如`function(req, res, next)`，req是Request对象，Response对象，next是下一个执行的中间件函数

每个中间件都有一个构造函数，返回的实例对象提供了对应的中间件功能，例如：
```js
// 在所有路径都应用body-parser中间件
const express = require('express');
const bodyParser = require('body-parser');

var app = express();
app.use('/', bodyParse());
```
若只对某个路由使用中间件，则可以修改path参数。
```js
const express = require('express');
const bodyParser = require('body-parser');

var app = express();

app.get('/parsedRoute', bodyParser(), function(req, res){
  res.send('parsed');
})
app.get('/otherRoute', bodyParser(), function(req, res){
  res.send('no parsed');
})
```
添加多个中间件,但是需要注意中间件的使用顺序，一些中间件之间是可以有依赖关系的：
```js
const express = require('express');
const bodyParser = require('body-parser');
const cookieParser = require('cookie-parser');
const session = require('express-session');

var app = express();
app.use('/', bodyParse()).use('/',cookieParser()).use('/', session());
```

### query中间件
query中间件将一个url查询字符串转换为js对象，保存在Request对象的query属性中，从Express4开始，内置该中间件。
```js
const express = require('express');
var app = express();
app.get('/', function(req, res){
  var id = req.query.id;
  var score = req.query.score;
  console.log(JSON.stringify(req.query));
  res.send('done');
});
// 查询字符串为?id=1&score=95
```

### 静态文件服务
express的static中间件能直接从磁盘将静态文件输出到客户端
```js
express.static(path, [options])
```
path指定请求引用的静态文件所在的根目录，options可以设置如下属性：
- maxAge 浏览器缓存的最长时间，默认为0，单位ms
- hidden 若为true表示启用传输隐藏文件功能，默认false
- redirect 若为true表示当请求路径是目录时，则将被重定向到一个有尾随/的路径，默认true
- index 根路径的默认文件名，默认为index.html
```js
var express = require('express');
var app = express();
app.use('/', express.static('./static'), {maxAge: 60*60*1000});
app.use('/images', express.static( '../images'));
app.listen(80);
```

### POST数据
```js
var express = require('express');
var bodyParser = require('body-parser');
var app = express();
app.use(bodyParser.urlencoded({ extended: true }));
app.use(bodyParser.json()); // 通过bodyParse.json()将post数据转化为req.body的属性
app.get('/', function (req, res) {
  var response = '<form method="POST">' +
        'First: <input type="text" name="first"><br>' +
        'Last: <input type="text" name="last"><br>' +
        '<input type="submit" value="Submit"></form>';
  res.send(response);
});
app.post('/',function(req, res){
  var response = '<form method="POST">' +
        'First: <input type="text" name="first"><br>' +
        'Last: <input type="text" name="last"><br>' +
        '<input type="submit" value="Submit"></form>' +
        '<h1>Hello ' + req.body.first + '</h1>'; // 这里first属性即是json方法转化后添加的
  res.type('html');
  res.end(response);
  console.log(req.body);
});
app.listen(80);
```

### cookie操作
cookie操作非常频繁，也很重要，使用cookie-parse中间件可以解析请求中的cookie然后将其作为req.cookies的属性.
```js
express.cookie-parser([secret]);
```
注：**Connect3发布后，cookie-parser重命名为cookie，而且不向后兼容**
secret参数是利用一个secret字符串在cookie内部，防止cookie篡改。
在响应中可以使用`res.cookie(name, value, [options])`
options可以有如下属性：
- maxAge 指定cookie的过期时间
- httpOnly 若为true表示cookie只能由服务器访问，浏览器的js代码无法获取
- signed 若为true表示cookie将被签名，使用req.signedCookie对象获取，而不是req.cookie
- path 指明cookie应用路径
```js
var express = require('express');
var cookieParser = require('cookie-parser');
var app = express();
app.use(cookieParser());
app.get('/', function(req, res) {
  console.log(req.cookies);
  if (!req.cookies.hasVisited){
    res.cookie('hasVisited', '1',
               { maxAge: 60*60*1000,
                 httpOnly: true,
                 path:'/'});
  }
  res.send("Sending Cookie");
});
app.listen(80);
```

### 会话
cookie-session中间件基于cookie-parser,所以若使用cookie-session则需先添加cookie-parser中间件。
```js
var express = require('express');
var cookieParser = require('cookie-parser');
var cookieSession = require('cookie-session');
var app = express();
app.use(cookieParser());
app.use(cookieSession({secret: 'MAGICALEXPRESSKEY'}));
app.get('/library', function(req, res) {
  console.log(req.cookies);
  if(req.session.restricted) {
    res.send('You have been in the restricted section ' +
             req.session.restrictedCount + ' times.');
  }else {
    res.send('Welcome to the library.');
  }
});
app.get('/restricted', function(req, res) {
  req.session.restricted = true;
  if(!req.session.restrictedCount){
    req.session.restrictedCount = 1;
  } else {
    req.session.restrictedCount += 1;
  }
  res.redirect('/library');
});
app.listen(80);
```
上述代码，若用户访问library则正常，但是若访问restricted后则会被提示已经被限制登录。而且记录访问restricted路径的次数。

### 基本的身份验证
Expressd的basic-auth-connect中间件提供了HTTP基本身份验证，使用Authorization Header从浏览器向服务器发送编码后的用户名和密码。若浏览器内没有存储URL的授权信息，则浏览器会启动基本的登录对话框，让用户登录。
```js
const basicAuth = require('basic-auth-connect');
app.use( express.basicAuth(function(username, passs){
  // ……
}));
```
在全局路由上使用身份验证
```js
var express = require('express');
var basicAuth = require('basic-auth-connect');
var app = express();
app.listen(80);
app.use(basicAuth(function(user, pass) {
  return (user === 'testuser' && pass === 'test');
}));
app.get('/', function(req, res) {
  res.send('Successful Authentication!');
});
```
在单个路径上使用身份验证
```js
var express = require('express');
var basicAuth = require('basic-auth-connect');
var app = express();
var auth = basicAuth(function(user, pass) {
  return (user === 'user1' && pass === 'test');
});
app.get('/library', function(req, res) {
  res.send('Welcome to the library.');
});
app.get('/restricted', auth, function(req, res) {
  res.send('Welcome to the restricted section.');
});
app.listen(80);
```

### 会话身份验证
基于HTTP的基本的身份验证很简单，方便，但是若证书被记住，则登录一直存在，所以不是很安全。
Express的session中间件对会话机制提供了很完善的功能用来管理会话，可以基于session完善身份验证。

res.session对象上有几个用来管理会话的方法
- regenerate([callback]) 重置会话
- destroy([callback]) 移除req.session对象
- save([callback]) 保存会话数据
- touch([callback]) 重置maxAge
- cookie 将会话链接到浏览器cookie对象

```js
var express = require('express');
var bodyParser = require('body-parser');
var cookieParser = require('cookie-parser');
var session = require('express-session');
var crypto = require('crypto'); // 加密模块，用于生成一个安全的密码
function hashPW(pwd){
  return crypto.createHash('sha256').update(pwd).
         digest('base64').toString();
}
var app = express();
app.use(bodyParser.urlencoded({ extended: true }));
app.use(bodyParser.json());
app.use(cookieParser('MAGICString'));
app.use(session());

app.get('/restricted', function(req, res){
  if (req.session.user) {
    res.send('<h2>'+ req.session.success + '</h2>' +
             '<p>You have entered the restricted section<p><br>' +
             ' <a href="/logout">logout</a>');
  } else {
    req.session.error = 'Access denied!';
    res.redirect('/login');
  }
});

app.get('/logout', function(req, res){
  req.session.destroy(function(){
    res.redirect('/login');
  });
});

app.get('/login', function(req, res){
  var response = '<form method="POST">' +
    'Username: <input type="text" name="username"><br>' +
    'Password: <input type="password" name="password"><br>' +
    '<input type="submit" value="Submit"></form>';
  if(req.session.user){
    res.redirect('/restricted');
  }else if(req.session.error){
    response +='<h2>' + req.session.error + '<h2>';
  }
  res.type('html');
  res.send(response);
});
app.post('/login', function(req, res){
  //user should be a lookup of req.body.username in database
  var user = {name:req.body.username, password:hashPW("myPass")};
  if (user.password === hashPW(req.body.password.toString())) {
    req.session.regenerate(function(){
      req.session.user = user;
      req.session.success = 'Authenticated as ' + user.name;
      res.redirect('/restricted');
    });
  } else {
    req.session.regenerate(function(){
      req.session.error = 'Authentication failed.';
      res.redirect('/restricted');
    });
    res.redirect('/login');
  }
});
app.listen(80);
```

### 自定义中间件
在使用Express时，可以自定义中间件，即提供一个函数，该函数接受三个参数，第一参数为Request对象，第二参数为Response对象，第三参数为next，next是通过中间件框架传递的函数，它指向下一个要执行的中间件，所需必须在退出自定义中间件之前调用next(),否则后面的处理程序永不会被调用。

自定义一个将查询字符串从url剥离的中间件
```js
var express = require('express');
var app = express();
function queryRemover(req, res, next){
  console.log("\nBefore URL: ");
  console.log(req.url);
  req.url = req.url.split('?')[0];
  console.log("\nAfter URL: ");
  console.log(req.url);
  next();
};
app.use(queryRemover);
app.get('/no/query', function(req, res) {
  res.send("test");
});
app.listen(80);
```