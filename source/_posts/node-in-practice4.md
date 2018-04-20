---
title: Node 技巧笔记4 - stream
categories: Nodejs
tags:
  - JavaScript
  - Nodejs
  - stream
date: 2018-04-02 11:19:10
updated: 2018-04-02 11:19:10
---

在Node中，流是由几个不同的对象附着的抽象接口，流能够读写，并基于事件。流通常包含某种I/O，而且能够通过其处理的类型分为不同的组。

**什么时候使用流？**
当使用fs.readFileSync同步读取文件时，将文件内容都会读到内存中，同时程序会阻塞。而用fs.readFile则不会阻塞。

但若需要读取很大的数据时，比如大文件压缩，归档，媒体文件，日志文件时，内存可能会不够，此时则可以使用fs.read+一个合适的缓冲区，每次读取固定长度的数据，或者直接使用fs.createReadStream。

### 技巧30 正确从流的基类继承
Node的流基类能够被用作新模块或子类的起点，但需要决定那个基类最适合解决问题，使用Object.prototype.call和util.inherits继承它。

所有的流基类都可以在流核心模块中找到，基础类为四种：Readable、Writable、Duplex、Transform。所以需要考虑接口的行为。

Transform 一般称为转换流，是基于Duplex 双工流的，其在一定程度上改变了数据，比如crypto模块。

#### 技巧31 实现一个可读流
可读流可被用来为I/O源提供API，也可以被用作解析器。通过继承stream.Readable类，然后实现_read(size)方法即可实现一个可读流。

以JSON行解析器为例，写一个可读流来读取缓冲区内的数据，每当一个新行出现，就使用JSON.parse解析。

```js
const stream = require('stream');
const util = require('util');
const fs = require('fs');

function JSONLineReader(source){
    stream.Readable.call(this); // 调用父类构造函数
    this._source = source;
    this.foundLineEnd = false;
    this._buffer = '';

    source.on('readable', ()=>{ // 当数据源准备好，调用read方法
        this.read();
    });
}
// 从stream.Readable继承
util.inherits(JSONLineReader, stream.Readable);

// 从stream.Readable继承的类必须实现_read方法
JSONLineReader.prototype._read = function (size) {
    let chunk,
        line,
        lineIndex,
        result;
    // 当准备接收更多数据时，在源上调用read方法
    if(this._buffer.length === 0){
        chunk = this._source.read();
        this._buffer += chunk;
    }

    lineIndex = this._buffer.indexOf('\n');

    if(lineIndex !== -1){
        // 截取buffer中第一行进行解析
        line = this._buffer.slice(0, lineIndex);

        if(line){
            result = JSON.parse(line);
            this._buffer = this._buffer.slice(lineIndex+1);
            this.emit('object', result); // 当JSON解析好后，触发object事件，
            this.push(util.inspect(result)); // 将解析好的JSON发送回内部队列
        }else{
            this._buffer = this._buffer.slice(1);
        }
    }
}
```

测试，读取一个JSON行文件,json-lines.txt内的数据如下:
{"name":1,"letter":"a"}
{"name":2,"letter":"b"}
{"name":3,"letter":"c"}

```js
let input = fs.createReadStream(__dirname + '/json-lines.txt', {
    encoding: 'utf8'
});

let jsonLineReader = new JSONLineReader(input);

jsonLineReader.on('object', obj=>{
    console.log(`pos: ${obj.name} - letter:${obj.letter}`)
});
```

除了读取字符串，还需要读取对象流或二进制流。例如实现一个可读取对象流的自定义类,此时需要使用到可读流的配置选项，可传入一个option选项对象。
```js
const stream = require('stream');

// 使用ES6 Class继承一个可读流
class MemeoryStream extends stream.Readable {
    constructor(option = {}){
        option.objectMode = true;
        super(option);
    }

    _read(size){
        this.push(process.memoryUsage());
    }
}

// 测试
let ms = new MemeoryStream();
ms.on('readable', ()=>{
    let output = ms.read();
    console.log(`Type: ${typeof output}, heapTotal:${output.heapTotal}`)
})
```


**实现一个可写流**
若想要使用一个流接口I/O输出数据，则可以继承stream.Writable，实现_write方法即可向底层发送数据。

**实现一个双工流**
继承stream.Duplex并实现_read和_write方法即可。

**实现一个转换流**
继承stream.Transform并实现_transform方法即可。转换流和双工流很像，但不同在于，转换流是转换数据，通过_transform方法实现，当数据被转换完成后执行回调，允许转换流异步解析数据。