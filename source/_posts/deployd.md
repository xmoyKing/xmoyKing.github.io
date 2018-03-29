---
title: deployd入门安装
categories: Nodejs
tags:
  - Nodejs
  - mongodb
  - deployd
date: 2017-05-11 14:37:44
updated: 2017-05-11 14:37:44
---

在ubuntu下使用deployd搭建一个开发测试环境，数据库使用服务器端。

可将deployd安装到任何地方，使用官方的脚本启动本地的deployd服务即可。

由于deployd是node的一个包，而且依赖mongodb数据库，所以需要有这两者的环境。同时，若想要将应用常驻服务器中（作为ubuntu中的一个守护进程）则可以使用node的forever启动应用。

参考:[HOW TO SETUP DEPLOYD ON UBUNTU SERVER](http://terraltech.com/how-to-setup-deployd-on-ubuntu-server/)

### 关于mongodb的一些坑
安装mongodb时，直接使用`sudo apt-get install mongodb-server`

在mongodb添加用户的时候，发现addUser已经废弃了，使用createUser代替

若要开启服务器上的mongod作为常驻服务，需要配置`bind_ip`，将bind_ip配置为0.0.0.0，表示接受任何IP的连接。

mongodb的配置文件中的bind_ip 默认为127.0.0.1，默认只有本机可以连接。

配置完成后需要重新启动mongod， 然后就可以使用Robomongo远程链接了

[【Linux】启动mongo db后台服务](http://blog.csdn.net/sodino/article/details/52402368)

[MongoDb的bin目录下文件mongod,mongo,mongostat命令的说明及使用](http://www.360sdn.com/MongoDB/2013/1209/1033.html)

若不想自己搭建mongodb的测试服务器，也可以使用mlab中的免费服务器。

### deployd
当使用远程的mongodb时，就无法直接使用`dpd`开启服务了，此时可以使用node脚本开启服务并且可以指定远程mongodb数据库.

```js
// server.js
var deployd = require('deployd');

var server = deployd({
  port: process.env.PORT || 5000,
  env: 'demo',
  db: {
    host: '远程mongodb域名或ip',
    port: '数据库端口',
    name: '数据库名',
    credentials: {
      username: '数据库用户名',
      password: '数据库密码'
    }
  }
});

server.listen();

server.on('listening', function() {
  console.log("Demo Server in 5000");
});

server.on('error', function(err) {
  console.error(err);
  process.nextTick(function() { // Give the server a chance to return an error
    process.exit();
  });
});
```

注意，运行此脚本时的命令文件夹下必须存在resource命令（即不是指此脚本的位置，而是指运行此脚本时的位置）如下目录：
```
/Demo
  /dpdDir
    /.dpd
    /data
    /public
    /resources
    app.dpd
    server.js
  /node_moudules
  package.json
```
当在Demo目录下使用命令`cd dpdDir`，然后运行`node server.js`没问题。
但当直接使用命令`node ./dpdDir/server.js`就会报找不到resources文件夹

所以若直接在package.json中使用`scripts`开启服务，则需要使用命令
```js
  "scripts": {
    "start": "cd dpdDir && node server.js"
  },
```

然后，当进入dashboard的时候会需要一个key，此时在项目目录下（dpdDir）使用`dpd keygen`即可生成key，此命令会在`dpdDir/.dpd/keys.json`中生成一个对象，打开文件即可找到这个key，其内的一串英文。
使用`show key`也可以得到这个生成的key，但是可能会被自动换行，所以复制keys.json里的字符串即可。