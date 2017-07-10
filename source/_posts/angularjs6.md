---
title: angularjs入门笔记-6-做一个todo小程序
categories:
  - fe
tags:
  - fe
  - angularjs
date: 2017-05-01 14:34:50
updated: 2017-05-01 14:34:50
---

先做一个静态HTML程序，一个todo小程序（待办事项小应用）, angularjs版本为1.2

### 目录组织结构：
```s
angularjs #项目目录
  angular.js #文件
  bootstrap-theme.css
  bootstrap.css
  todo.html #静态html文件
  todo.json #json数据，模拟请求
node_modules #node包目录
server.js #启动静态服务器的入口文件
```


其中server.js使用到了connect,而connect分为两种版本，一个2.x,一个是最新的3.x
参考[node报错：connect.static is not a function](https://segmentfault.com/q/1010000005090969)

若使用2.x则创建Web服务器的代码如下：
```js
// 安装connect2命令:
// npm install connect@2.x.x --save
var connect = require('connect');

connect.createServer(
    connect.static("./angularjs")
).listen(5000);
```
而在最新的connect3版本中，将非核心功能分离，由一些中间件实现
```js
// 使用如下命令安装库:
// npm install connect --save
// npm install server-static --save
var connect = require('connect');
var serverStatic = require('serve-static');

var app = connect();

app.use(serverStatic("./angularjs"));
app.listen(5000);
```
todo.json中存储着一些数据，这些数据使用ajax动态加载到ng应用中
```
[{ "action": "Buy Flowers", "done": false },
 { "action": "Get Shoes", "done": false },
 { "action": "Collect Tickets", "done": true },
 { "action": "Call Joe", "done": false }]
```


为了方便，将所有的html和ngjs逻辑都在todo.html中，
同时将代码解释以注释的形式直接写在代码中，更加清晰和方便。
```html

```
