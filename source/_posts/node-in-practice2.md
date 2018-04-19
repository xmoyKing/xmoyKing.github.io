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

#### 技巧17 使用 Buffer 转换原始数据
Node 提供了读取原始二进制数据的 API，依据特定的解析规则，读取、处理、转换二进制数据为其他格式。甚至可以基于二进制协议来进行数据的来回传输。

Buffer API提供了一些方法来处理二进制数据。详解参考[Node Buffer文档](https://nodejs.org/docs/latest/api/buffer.html)

#### 技巧18 自定义二进制协议
解析二进制文件并提取其中的数据含义、同时也可以对自定义数据进行编码，用良好定义的二进制协议来传输数据是一种简洁高效的方法。

要创建二进制协议、首先需要确定传输那些数据以及如何去表示这些数据。

以一个简单的数据库协议为例，：
1. 使用掩码确定数据存放在哪个数据库
2. 数据保存以一个在0~255范围之内的无符号正数（单字节）的键值来标识
3. 通过zlib压缩数据并存储

| Byte | 内容 | 描述　｜
| - | - | - |
| 0 | 1 byte | 决定数据要写入哪个数据库 |
| 1 | 1 byte | 一个字节的无符号证书（0~255）用作数据库键存储 |
| 2-n | 0-n byte | 存储的数据、任意通过zlib进行压缩的byte |

**使用比特表示选择哪个数据库**,第一个字节用于表示选择哪个数据库来存储传输的数据，在数据接收端，主数据库用一个简单的多维数组表示，内部分为8个小数据库（即1字节等于8比特）。

比如数字8对应的二进制为00001000，则将数据存储在第4个数据库中，因为第四个比特为1（从右向左数，索引为3）

**利用掩码和位操作（&操作）**，能快速测试某个数值的二进制是否满足要求。

**找出数据存储的键值**，将字节位1中的数字取出，然后利用readUInt8方法即可获取键值

**使用zlib解压缩**，在传输字符串/ASCII/UTF-8数据时进行压缩非常有必要，这样可以大大减少传输使用的带宽，假设接到的数据是已经压缩过的。

Node内置的zlib模块提供 deflate（压缩）、inflate（解压缩）的方法，也包括gzip的压缩方法，需要对接收到的数据进行检查其是否正确压缩，否则不进行解压。通常zlib压缩的结果数据的第一个字节为0x78。

```js
var zlib = require('zlib');
// 用数组字面量表示数据库
var　database = [[],[],[],[],[],[],[],[]];
// 掩码数组，如此能方便测出数字哪一位为1
var bitmasks = [1,2,4,8,16,32,64,128];

function store(buf){
  var db = buf[0]; // 获取数据库索引
  var key = buf.readUInt8(1); // 获取存储键值

  if(buf[2] === 0x78){ // 从第二个字节位开始判断
    zlib.inflate(buf.slice(2), function(err, inflatedBuf){
      if(err) return console.error(err);

      var data = inflatedBuf.toString(); // zlib.inflate返回一个Buffer对象，将其转换为UTF-8格式字符串存储

      // 依次循环对比所有掩码
      bitmasks.forEach((bitmask, idx)=>{
        if((db & bitmask) === bitmask){
          database[idx][key] = data;
        }
      });

    });
  }
}
```

上述为存储数据的代码，生成数据的代码如下：
```js
var zlib = require('zlib');
var header = new Buffer(2);

header[0] = 8;
header[1] = 0;

zlib.deflate('some message', function(err, deflatedBuf){
  if(err) return console.error(err);

  var message = Buffer.concat([header, deflatedBuf]);
  store(message);
})