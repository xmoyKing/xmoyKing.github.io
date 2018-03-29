---
title: Node 技巧笔记2 - Buffer
categories: Nodejs
tags:
  - JavaScript
  - Nodejs
  - buffer
date: 2018-03-21 12:15:21
updated: 2018-03-21 12:15:21
---

Buffer 代表原始堆的分配额的数据类型，在 JS 中以类数组的方式来使用，在全局可用，不需要导入，可以将其视作 Node 环境下 JS 的新类型（如同 String 和 Number 一样）。
```js
let buf = new Buffer(255); // 分配255个字节
buf[0] = 23; // 将第一个字节写入整形数据 23
```

### 修改数据编码
若没有提供编码格式，那么文件操作及很多网络操作会默认将数据作为 Buffer 类型返回，以 fs.readFile 为例：
```js
let fs = require('fs');
fs.readFile('./names.txt', function(err, buf){
  Buffer.isBuffer(buf); // true
  console.log(buf);
});
```
当然，大部分情况下是知道文件编码的。

#### 技巧15 Buffer 转换为其他格式
以读取纯文本文件 names.txt为例，其内每行有一个人名,类似：
```
Alex
Bob
Cheng
Dave
```

使用 fs.readFile 直接读取输出为一串八位字节组（16进制编码）:
`<Buffer 41 6c 65 78 0d 0a 42 6f 62 0d 0a 43 68 65 6e 67 0d 0a 44 61 76 65>`

这样无法直接阅读，通过 Buffer 提供的 toString 方法可将其转换为 UTF-8 格式，`buf.toString()` 输出原内容，其中 toString 方法可以接收一个编码类型，如`'ascii'、'utf16le'、'base64'、'hex'`等。

#### 技巧16 使用 Buffer 修改字符串编码
利用 Buffer 将字符串编码格式转换为其他格式，如将用户名和密码转换为 Base64 等:
```js
// Buffer 构造函数也可以接收字符串，同时第二个参数可指定其存储的编码
new Buffer('myRealPassword', 'base64');
```

在转换一些小图片为 base64 的时候尤其有用：
```js
// 将 readFileSync 返回的 Buffer 直接转换为 base64 格式字符串
let data = fs.readFileSync('./picture.png').toString('base64');
let mime = 'image/png';
let uri = 'data:' + mime ';base64,' + data; // 一个合格的 base64 的uri，可直接放在<img src="">中
```