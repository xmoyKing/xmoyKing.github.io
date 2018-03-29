---
title: Nodejs-其他模块
categories: Nodejs
tags:
  - Nodejs
  - os
  - util
date: 2016-10-28 19:17:16
updated: 2016-10-28 19:17:16
---

本文记录Node的一些其他内置模块，比如os模块能获取操作系统方面的信息，util提供各种功能，如同步输出、字符串格式化、继承，dns模块能查找DNS或反向查找

## os模块
os模块提供了的获取操作系统信息的方法，参考[os (操作系统)](http://nodejs.cn/api/os.html#os_os)

### os常用方法
os.EOL 一个字符串常量,定义操作系统相关的行末标志:
    `\n` 在 POSIX 系统上
    `\r\n` 在 Windows系统上

os.arch() 方法返回一个字符串, 表明Node.js 二进制编译 所用的 操作系统CPU架构.
os.constants
os.cpus()  方法返回一个对象数组, 包含安装的每个CPU/CPU核的信息.
os.endianness() 方法返回一个字符串,表明Node.js二进制编译环境的字节顺序. 'BE' 大端模式 'LE' 小端模式
os.freemem() 方法以整数的形式回空闲系统内存 的字节数.
os.homedir()
os.hostname() 方法以字符串的形式返回操作系统的主机名.
os.loadavg()
os.networkInterfaces() 方法返回一个对象,包含只有被赋予网络地址的网络接口.
os.platform() 方法返回一个字符串, 指定Node.js编译时的操作系统平台
os.release() 方法返回一个字符串, 指定操作系统的发行版.
os.tmpdir() 方法返回一个字符串, 表明操作系统的 默认临时文件目录.
os.totalmem() 方法以整数的形式返回所有系统内存的字节数.
os.type() 方法返回一个字符串,表明操作系统的名字, 由uname(3)返回.举个例子, 'Linux' 在 Linux系统上, 'Darwin' 在 OS X 系统上,'Windows_NT' 在 Windows系统上.
os.uptime() 方法在几秒内返回操作系统的上线时间.
os.userInfo([options])

### OS 常量
信号常量
错误常量
  POSIX 错误常量
  Windows 系统特有的错误常量
libuv 常量

## util模块
util模块是一个专用用于提供大量使用功能的"工具包"模块

### 格式化字符串
在处理字符串数据时，通常需要格式化字符串，util模块提供的format函数接受一个格式化字符串作为第一个参数，并返回格式化后的字符串。
format参数可以包含占位符，
```
%s 表示字符串
%d 表示数值，整数或浮点数
%j 表示JSON，或可转换为字符串的对象
%  若%后为空，则不作为占位符
```
当参数比占位符少时，多余的占位符不会被替换而是直接输出
```js
const util  = require('util');
var s = util.format('%s = %s', 'item');
console.log(s); // item = %s
```
当参数多余占位符时，多余的参数被转换为字符串，然后用空格分隔
```js
var s = util.format('%s = %s', 'item','aaaa', 'more', 'bbb');
console.log(s); // item = aaaa more bbb
```
若第一个参数不是格式字符串，则会自动转换为字符串，用空格分隔
```js
var s = util.format(1, 2, 3, 'more', 'bbb');
console.log(s); // 1 2 3 'more' 'bbb'
```

### 检查对象类型
使用instanceof运算符，比较对象的类型，返回true或false，
```js
console.log([1,2,3] instanceof Array); // true
```
util提供了isArray，isRegExp，isDate，isError等方便的方法

### 同步写入输出流
同步写数据到stdout和stderr意味着进程保持阻塞，直到数据写入完成。
```js
util.debug(string); // 将string写入stderr
util.error([……]); // 接受多个参数，并写入stderr 如 util.error(errorCode, 'errorname');
util.puts([……]); // 接受多个参数，将每一个参数都转换为字符串，然后写入stdout
util.log(string); // 将string以及时间戳写入stdout如 util.log('msg');输出 28 Apr 21:40:39 - msg
```

### 将JS对象转换为字符串
util.inspect能检查一个对象，然后返回该对象的字符串表示形式
```js
uitl.inspect(object, [options]);
```
options对象可以控制字符串格式化，比如
showHidden: 将该对象的不可枚举属性也转换为字符串，默认false
depth: 当格式化属性也是对象时，限制遍历的深度，默认为2，可以防止无限循环并防止复杂对象占用大量CPU资源，若为null，则无限制递归
colors：当设置为true时，使用ANSI颜色样式，默认false
customInspect：默认true，当设置为false时，被检测的对象定义的任何自定义inspect方法都不会被调用。（即可以手动覆盖inspect方法）比如：
```js
const util = require('util');
var obj = {first: 'king', last: 'mine'};
obj.inspect = function(depth){
  return '{name:"'+ this.first +' '+ this.last +'"}';
};
console.log(util.inspect(obj)); // {name:"king mine"}
```

### 继承
util.inherits方法能创建一个对象，可以指定继承另一个对象的原型方法。
```js
util.inherits(constructor, superConstructor);
```
可以通过 constructor.super_ 属性从自定义对象的构造函数访问superConstructor

创建一个继承events.EventEmitter对象构造函数的Writable流，示例如下：
```js
const util = require('util');
const events = require('events');

function Writer(){
    events.EventEmitter.call(this);
}

util.inherits(Writer, events.EventEmitter);
Writer.prototype.write = function(data){
    this.emit('data', data);
};

var w = new Writer();
console.log(w instanceof events.EventEmitter);
console.log(Writer.super_ === events.EventEmitter);

w.on('data', function (data) {
    console.log('Data: ' + data);
});

w.write('some data!');
```
输出：
true
true
Data: some data!

### dns模块
dns模块能帮助解析DNS域名，查找域，或做反向查找。
```js
const dns = require('dns');
console.log('resolving www.baidu.com ……');

dns.resolve4('www.baidu.com',function(err, address){
    console.log('ipv4 addresses: '+ JSON.stringify(address, false, ' '));
    address.forEach(function (addr) {
        dns.reverse(addr, function (err, domains) {
            console.log('Reverse for ' + addr + ': ' + JSON.stringify(domains));
        });
    });
});
```
输出：
resolving www.baidu.com ……
ipv4 addresses: [
 "119.75.217.109",
 "119.75.218.70"
]
Reverse for 119.75.217.109: undefined
Reverse for 119.75.218.70: undefined