---
title: Nodejs-console模块
categories: Nodejs
tags:
  - Nodejs
  - console
date: 2016-09-26 19:14:37
updated: 2016-09-26 19:14:37
---

console（控制台）模块是Node最有用（常用）的模块之一，该模块提供了大量的功能，同来把调试和信息内容写到控制台。
console模块能控制输出，实现时间差的输出，并把跟踪信息和断言写到控制台。console模块并不需要使用require()语句就能把它加载到模块中,只需要使用console.xxx()即可调用xxx方法。

console的一些常用函数：
1. log([data], [……]): 将data输出到控制台，data变量可以是字符串或者可解析为字符串的一个对象，额外的参数也可以被发送。
```js
console.log('There are %d items', 5); // There are 5 items
```
2. info(): 与log类似
3. error(): 与log类似，其中error输出也被发送到stderr
4. warn(): 与error类似
5. dir(obj): 把一个JS对象的字符串表示形式写到控制台
```js
console.dir({name: 'king', role: 'author'}); // {name: 'king', role: 'author'}
```
6. time(label): 把一个精度为毫秒的当前时间戳赋给字符串label
7. timeEnd(label): 创建当前时间与赋给label的时间戳之间的差值，并输出结果，
```js
console.time('FileWriter');
f.write(data); // 大约用时500ms
console.timeEnd('FileWriter'); // FileWriter: 500ms
```
8. trace(label): 把代码当前位置的栈跟踪信息写到stderr
```js
module.trace('tracer');
// 输出栈跟踪信息:
Trace: tracer
    at Object.<anonymous> (E:\Git\xmoyKing.github.io\Untitled-1.txt.js:1:71)
    at Module._compile (module.js:570:32)
    at Object.Module._extensions..js (module.js:579:10)
    at Module.load (module.js:487:32)
    at tryModuleLoad (module.js:446:12)
    at Function.Module._load (module.js:438:3)
    at Module.runMain (module.js:604:10)
    at run (bootstrap_node.js:394:7)
    at startup (bootstrap_node.js:149:9)
    at bootstrap_node.js:509:3
```
9. assert(expression, {message}): 如果expression计算结果为false，就把消息和栈跟踪信息写到控制台