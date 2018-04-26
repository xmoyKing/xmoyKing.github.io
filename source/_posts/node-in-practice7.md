---
title: Node 技巧笔记7 - child_process
categories: Nodejs
tags:
  - JavaScript
  - Nodejs
  - child_process
date: 2018-04-16 11:10:31
updated: 2018-04-16 11:10:31
---

任何一个平台都不是孤立的，在Node中，child_process模块允许在Node程序中执行一些外部程序，如Java、shell、PHP等（也包括其他利用Node开发的应用），这样能避免重复造轮子。

child_process模块提供四种不同的方法来执行外部程序，这些方法都是异步的。分别是：
- execFile 执行外部程序，需提供一组参数，以及一个在进程退出后的缓存输出的回调
- spawn 执行外部程序，需提供一组参数，以及一个在进程退出后的输入输出和事件的数据流接口
- exec 在一个命令行窗口中执行一个或多个命令，以及一个在进程退出后缓冲输出的回调
- fork 在一个独立的进程中执行一个Node模块，需要提供一组参数，以及一个类似spawn方法里的数据流和事件式的接口，同时设置好父进程和子进程之间的通信

选择时的考虑如下：
![](1.png)

### 技巧56 执行外部程序
若想要运行一个外部程序，然后获取其输出结果，那么使用execFile方法是最直接的方式，它会将输出结果自动缓存，并且通过一个回调函数返回最后的结果或异常信息。

以一个echo命令为例：
```js
const cp = require('child_process');

// 第一个参数是程序命名名，第二个参数为数组类型，表示命令的输入
cp.execFile('echo', ['hello','world'], (err, stdout, stderr) => {
  if(err) console.error(err);

  console.log(`stdout: ${stdout}`);
  console.log(`stderr: ${stderr}`);
});
```
