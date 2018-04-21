---
title: Node 技巧笔记4 - fs模块
categories: Nodejs
tags:
  - JavaScript
  - Nodejs
  - fs模块
date: 2018-04-05 16:03:12
updated: 2018-04-05 16:03:12
---

Node的核心模块通常是低级别的API，而fs模块通过以下的方式运行开发与文件系统交互：
- POSIX文件I/O
- 文件流
- 批量文件I/O
- 文件监控

fs模块相较其他I/O模块（如net、http），不仅有异步接口，也有同步接口。文件系统的同步接口很大部分是因为Node的内部工作方式，比如模块系统、require方法就是同步的。

fs模块包含常规的POSIX文件操作的封装，比如readdir在Node中对应的fs.readdir方法：
```js
const fs = require('fs');

fs.readdir('/path/dir', function(err, files){
  console.log(files);
});
```

### 技巧39 读取配置文件
把配置文件放在单独的文件中非常有用，尤其是当应用运行在多个环境下时（开发环境、测试环境、生成环境）。

```js
const fs = require('fs');
try{
  const config = JSON.parse(fs.readFileSync('./config.json').toString());

  doThings(config);
}catch(err){
  console.error(err);
}
```
一般在系统初始化时加载配置文件，可以通过同步的方式读取。
通过readFileSync拿到的数据是buffer，需要将其转换为字符串，然后解析为JSON。
同步的错误可以通过标准try-catch捕获。

其实，通过require直接加载JSON文件更方便`const require('./config.json');`，但这种方式下，模块会被全局缓存，所以有其他模块也需要加载并修改了配置，则会影响到全局所有的配置。因此最好将其配置文件内容定义为const常量。

### 技巧40 使用文件描述符（File descriptor）
文件描述符是在操作系统中管理的在进程中打开文件所关联的一些索引（数字类型），操作系统通过指派一个唯一的证书给每个打开的文件用来查看关于这个文件的信息。

可以通过fs文件描述符来读写文件。而文件描述符不仅仅可指向文件，还可以指向目录、管道、网络套接字、标准流等。比如

| 标准流 | 文件描述符 | 描述 |
| - | - | - |
| stdin | 0 | 标准输入流 |
| stdout | 1 | 标准输出流 |
| stderr | 2 | 标准错误流 |

而Node中，console.log()其实就是stdout的语法糖，通过使用全局process对象，也可以实现打印输出。
`process.stdout.write('some log to stdout')`

fs模块有一个很少使用的方法，即将文件描述符作为第一个参数。比如通过fs.writeSync写入文件描述符1（即stdout）：
`fs.writeSync(1, 'some log to stdout')`，

三种打印的效果相同，是因为Node中，console.log和process.stdout.write实际上是同步的方法。

文件描述符非常有用，尤其是在多进程和多线程编程时。

### 技巧41 使用文件锁
在需要协同多个进程同时访问一个文件并且保证文件的完整性时文件锁就非常有用了。例如锁住文件防止多个进程篡改文件。

Node本身不能实现直接锁住文件，无论是强制锁还是咨询锁，其中咨询锁可以用过调用系统调用（syscalls）实现，如flock。

在Node中，除了flock方法，还可以使用锁文件实现，所谓锁文件，其实就是普通的文件或文件夹，它作为一个标记，存在时就说明其他资源正在使用资源。锁文件的创建必须是原子性的，这样才能避免冲突。

可以通过一些方法来实现锁文件：
- 使用独占标记创建锁文件
- 使用mkdir创建锁文件

**使用独占标记创建锁文件**
fs模块为所有需要打开文件的方法（如fs.writeFile、fs.createWriteStream、fs.open）都提供了一个x标记，即表示以执行模式打开（0_EXCL），执行模式是独占的，也就是说当使用这个方法时，若这个文件存在，则文件不能被打开。
```js
// 以可执行、可写入模式打开
fs.open('lockfile.lock', 'wx', err=>{
  // 包括文件存在都会返回失败
  if(err) return console.error(err);
  // ... 安全修改资源
});
```

一般来说，可以将当前进程的PID写入锁文件而不是仅仅打开一个空文件，这样当有异常时，可以知道最后使用锁的进程。
```js
fs.writeFile('lockfile.lock', process.id, {flags: 'wx'}, err=>{
  if(err) return console.error(err);
  // ... 安全修改资源

});
```

**使用mkdir创建锁文件**
上面的锁文件有可能会无法工作，比如某些系统在远程硬盘上无法识别0_EXCL标记，此时的策略就是将创建文件改为创建文件夹，mkdir也是原子性的操作，无法并发，而且支持跨平台。当目录名已经存在时，mkdir会失败，可以将PID写入该目录下的文件内。
```js
fs.mkdir('lockdir', err=>{
  if(err) return console.error(err);

  fs.writeFile('lockdir/lockfile_'+ process.id, err=>{
    if(err) return console.error(err);
    // ... 安全修改资源

  });
});
```

上述2种方式完成了锁文件的创建，但还需要一个方法在操作结束时删除锁文件，同时需要在进程退出时，将所有锁文件都删除，这些操作可以封装在一个简单模块中：
```js
const fs = require('fs');

const lockDir = 'lockDir';
let hasLock = false;

// 定义获取锁方法
exports.lock = function(cb){
    if(hasLock) return cb(); // 若已经获取锁

    fs.mkdir(lockDir, err=>{
        if(err) return cb(err); // 无法创建锁
        // 写入PID，方便调试
        fs.writeFile(lockDir+'/'+process.pid, err=>{
            if(err) console.error(err); // 若无法写入PID，则打印并继续运行

            hasLock = true; // 设置已创建锁的标识
            return cb();
        });
    });
}

// 定义释放锁方法
exports.unlock = function (cb) {
    if(!hasLock) return cb(); // 没有可释放的锁
    // 与获取锁的方式相反，先删除文件，然后删除文件夹
    fs.unlink(lockDir+'/'+process.pid, err=>{
        if(err) return cb(err);

        fs.rmdir(lockDir, err=>{
            if(err) console.error(err); // 若无法写入PID，则打印并继续运行

            hasLock = false;
            cb();
        });
    });
}

process.on('exit', ()=>{
    if(hasLock){ // 若还有锁存在，则退出前需要释放掉
        fs.unlinkSync(lockDir+'/'+process.pid);
        fs.rmdirSync(lockDir);
        console.log('released lock');
    }
})
```

测试用法
```js
const locker = require('./locker');
// 获取锁
locker.lock(err=>{
    if(err) throw err;
    // 操作...

    // 操作完成后释放锁
    locker.unlock(()=>{
        // ... 释放锁之后的回调
    });
});
```

关于独占模式的实现，可查看[lockfile模块的实现](https://github.com/npm/lockfile)

### 技巧42 递归文件操作
有时候需要像`rm -rf`那样删除一个目录以及它下面的所有子目录，或者创建一个目录的同时也创建所有中间目录。此时需要用到递归文件操作。递归操作易用，但也易错，尤其是在异步操作时。

