---
title: Nodejs-文件系统
categories:
  - Nodejs
tags:
  - nodejs
  - fs
date: 2016-09-28 10:32:45
updated: 2016-09-28 10:32:45
---

参考自:[Node.js 文档](http://nodejs.cn/api/fs)

Nodejs提供的fs模块几乎都有两个版本，一个同步版本，一个异步版本，比如异步的write()和同步的writeSync(), 虽然这两个方法在底层功能上是一致的，但这两个版本在运用时有比较大的区别，最好不要混用。

异步和同步调用的区别：
- 异步调用需要回调函数做为参数，回调函数在文件系统的请求完成时被调用，并通过第一个参数为错误对象
- 异步调用自动处理异常，若发生异常，就将错误对象作为第一个参数传递，否则第一个参数为null。而同步调用中处理异常需要try/catch语句
- 同步调用立即运行，且直到结束前会阻塞主线程。异步调用则被放在事件队列中，且执行后返回主线程（真正的执行其实是在事件循环中被提取出时才执行）

大多数语言的文件系统打开文件都提供了好几种读写模式，在node中也是这样：
```js
// 异步打开一个文件，需要指定回调函数
fs.open(path, flags, [mode], callback);
// 同步打开一个文件
fs.openSync(path, flags, [mode]);
```

### 这里的flags即指定读写模式

'r' - 以读取模式打开文件。如果文件不存在则发生异常。

'r+' - 以读写模式打开文件。如果文件不存在则发生异常。

'rs+' - 以同步读写模式打开文件。命令操作系统绕过本地文件系统缓存。

这对 NFS 挂载模式下打开文件很有用，因为它可以让你跳过潜在的旧本地缓存。 它对 I/O 的性能有明显的影响，所以除非需要，否则不要使用此标志。

注意，这不会使 fs.open() 进入同步阻塞调用。 如果那是你想要的，则应该使用 fs.openSync()。

'w' - 以写入模式打开文件。文件会被创建（如果文件不存在）或截断（如果文件存在）。

'wx' - 类似 'w'，但如果 path 存在，则失败。

'w+' - 以读写模式打开文件。文件会被创建（如果文件不存在）或截断（如果文件存在）。

'wx+' - 类似 'w+'，但如果 path 存在，则失败。

'a' - 以追加模式打开文件。如果文件不存在，则会被创建。

'ax' - 类似于 'a'，但如果 path 存在，则失败。

'a+' - 以读取和追加模式打开文件。如果文件不存在，则会被创建。

'ax+' - 类似于 'a+'，但如果 path 存在，则失败。

mode选项只有在创建文件的时候才有效，默认为0666，表示可读写，类似linux的文件系统权限模式。

一个文件被打开后需要关闭，使系统将更改的内容刷新到磁盘并释放对文件的锁，可以通过文件描述符关闭文件，在异步关闭的情况下，还需要传入回调函数。
```js
// fd 为文件描述符，是打开文件后返回的值
fs.close(fd, callback);
fs.closeSync(fd);
```

打开并关闭的异步和同步的例子：
```js
fs.open('file','w',function(err, fd){
  if(!err){
    fs.close(fd);
  }
});

var fd = fs.openSync('file', 'w');
fs.closeSync(fd);
```

### 写入文件
fs模块提供了4种方式将数据写入文件，可以在一个程序中，将数据写入文件，同步/异步/Writable流等，输入对象为String或Buffer。

#### 简单写入
写入文件最简单的方式为writeFile(), 将一个字符串或缓冲区的所有内容写入文件
```js
fs.writeFile(path, data, [otpions], callback);
fs.writeFileSync(path, data, [otpions]);
```
path为写入文件的路径，可以是相对或绝对路径；
data为写入文件的string或buffer对象；
options为可选对象，包含定义字符串编码，以及打开文件时使用的模式和标志encoding，mode和flag属性。

```js
var fs =require('fs');
var config = {
  maxFiles: 20,
  maxConnections: 15,
  rootPath: '/webroot'
};

var configTxt = JSON.stringify(config);
var options = {encoding: 'utf8', flag:'w'};
fs.writeFile('config.txt', configTxt, options, function(err){
  if(err){
    console.error(err);
  }else{
    console.log('Config saved');
  }
})
```

#### 同步写入
需要先用openSync打开文件并获取文件描述符，然后使用writeSync写入文件。
```js
fs.writeSync(fd, data, offset, length, position);
```
offset指定data参数中开始的索引，若从当前索引开始应为null，
length指定写入的字节数，null表示写到数据缓冲区的末尾，
position指定在文件中开始写入的位置，null为当前位置。


```js
var fs = require('fs');
var vals = ['carrots', 'celery', 'olives'];
var fd = fs.oepnSync('val.txt', 'w');
while(vals.length){
  val = vals.pop() + ' ';
  var bytes = fs.writeSync(fd, val, null, null, null); //返回写入的字节数
  console.log('wrote %s %d bytes', val, bytes);
}
fs.closeSync(fd);
```

#### 异步写入
```js
fs.write(fd, data, offset, length, position, callback);
```
callback接受两个参数，error和bytes，

#### 流式写入
写入大量数据时，最好使用流，把文件作为一个Writable流打开，可以使用pipe方法与Readable流链接。
将数据异步写入文件，需要先创建一个Writable对象
```js
fs.createWriteStream(path, [options]);
```
一旦打开Writable文件流，就可以使用标准的流式write(buffer)写入，当完成后，使用end()方法关闭流,这会触发close事件。
```js
var fs = require('fs');
var garins = ['wheat', 'rice', 'oats'];
var options = {encoding: 'utf8', flag: 'w'};
var fileWriteStream = fs.createWriteStream('grains.txt', options);
fileWriteStream.on('close', function(){
  console.log('file closed');
});
while(grains.length){
  var data = grains.pop()+' ';
  fileWriteStream.write(data);
  console.log('Wrote: %s', data);
}
fileWriteStream.end();
```

### 读取文件
同写入文件一样，读取文件也有4种

#### 简单读取
直接使用readFile和readFileSync即可

#### 同步读取
```js
fs.readSync(fd, buffer, offset, length, position);
```
buffer替代data对象，作为读入数据的存储对象。

#### 异步读取
```js
fs.read(fd, buffer, offset, length, position, callback);
```
callback函数的参数有三个，error、btyes和buf，使用示例如下：
```js
var fs = require('fs');
function readFruit(fd, fruits){
  var buf = new Buffer(5);
  buf.fill();
  fs.read(fd, buf, 0, 5, null, function(err, bytes, data){
    if(bytes > 0){
      console.log('read %d bytes', bytes);
      fruits += data;
      readFruit(fd, fruits);
    }else{
      fs.close(fd);
      console.log('Fruits: %s', fruits);
    }
  });
}

fs.open('fruit.txt','r',function(err, fd){
  readFruit(fd, ''); // 初始时传入fruits为空字符串
})
```

#### 流读取文件
读取大量数据时最好使用流式读取，将文件作为Readable流打开。步骤同流写入相同
```js
var fs = require('fs');
var options = {encoding: 'utf8', flag: 'r'};
var fileReadStream = fs.createWriteStream('grains.txt', options);
fileReadStream.on('data', function(chunk){
  console.log('grains: %s', chunk);
  console.log('grains length: %d', chunk.length);
});
fileReadStream.on('close', function(){
  console.log('file closed');
});
```

### 其他文件系统功能
1. 检测文件是否存在/获取文件信息 使用stat方法，返回一个stats对象，有如下属性和方法：
```js
// 方法
stats.isFile() // 是否为文件
stats.isDirectory() // 是否为目录
stats.isBlockDevice() // 是否为块设备
stats.isCharacterDevice() 
stats.isSymbolicLink() (仅对 fs.lstat() 有效)
stats.isFIFO()
stats.isSocket()
// 属性
{
  dev: 2114, // 文件所在设备ID
  ino: 48064969, 
  mode: 33188, // 访问模式
  nlink: 1,
  uid: 85,
  gid: 100,
  rdev: 0,
  size: 527, // 文件字节数
  blksize: 4096, // 存储文件的块的大小，字节为单位
  blocks: 8, // 占用的磁盘的块的数目
  atime: Mon, 10 Oct 2011 23:24:11 GMT, // 上次访问文件的时间
  mtime: Mon, 10 Oct 2011 23:24:11 GMT, // 最后修改的时间
  ctime: Mon, 10 Oct 2011 23:24:11 GMT, // 创建时间
  birthtime: Mon, 10 Oct 2011 23:24:11 GMT
}
```
2. 读取目录中的文件列表
```js
fs.readdir(path, callback);
fs.readdirSync(path);
```
3. 删除文件
```js
fs.unlink(path, callbak);
fs.unlinkSync(path); // 返回true/false表示成功或失败
```
4. 截断文件
```js
fs.turncate(path, len, callback); // len 不写时为截断成零字节
```
5. 建立、删除目录（同文件有区别）
```js
fs.mkdir(path, [mode], callback);
fs.rmdir(path, callback);
```
6. 重命名文件或目录
```js
fs.rename(oldpath, newpath, callback);
```
7. 监视文件更改
```js
fs.watchFile(path, [options], callback);
```
options对象有两个属性：persistent表示是否持续，true为确定，interval表示轮询时间间隔，毫秒单位
下面的例子，每隔5s检测一次，输出log.txt文件的上次修改时间和本次修改时间
```js
fs.watchFile('log.txt', {persistent: true, interval: 5000}, function(curr, prev){
  console.log('modified at :' + curr.mtime);
  console.log('Previous modified at :' + prev.mtime);
});
```