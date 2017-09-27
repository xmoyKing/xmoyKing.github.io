---
title: Promise1-相关知识点
categories:
  - promise
tags:
  - promise
date: 2017-04-8 16:36:18
updated: 
---

记录学习Promise的过程，以及查到的一些资料和博客，知识点，主要参考：[JavaScript Promise迷你书（中文版）](http://liubin.org/promises-book/)

[PDF: Promises迷你书电子版（中文版）](javascript-promise-book.pdf)

[Github: Promises迷你书电子版（中文版）](https://github.com/liubin/promises-book/)


### 一个简单的Promise示例：
```js
var promise = new Promise(function(resolve){
  resolve(42);
});

promise.then(function(value){
  console.log(value);
}).catch(function(error){
  console.log(error);
})
// 结果为正常输出 42
```
`new Promise(fn)`返回一个promise对象，在fn中指定异步处理流程，若正常则调用`resolve(result)`将处理的值result返回，若错误则调用`reject(error)`将error对象返回（其中可以包含错误的信息）


普通的异步的回调函数
```js
getAsync('file.json', function(error, result){
  if(error){ // 出错时处理
    throw error;
  }
  // 成功时处理 ...
  JSON.parse(result); 
})
```
在Nodejs中，规定JS回调函数的第一个参数为`Error`对象，但这仅仅是约定，不采用也不会出问题。

而Promise则规范了异步处理，采用统一规则，其他的写法会出错，比如必须使用then和catch作为成功与失败的接口：
```js
var promise = getAsyncPromise('file.json');
promise.then(function(result){
  // 成功时的处理
}).catch(function(error){
  // 失败时的处理
})
```

### Promise简介
ES6 Promises标准中定义三种类型的promise：
1. Constructor, 可以使用`new Promise()`实例化一个promise对象
```js
var promise = new Promise(function(resolve, reject){
  // 处理
  
  // 处理完成，使用resolve或reject
});
```
2. Instance Method, 通过new生成的promise对象，可以调用`promise.then()`方法，
```js
promise.then(onFulfilled, onRejected)
```
当成功时，即resolve时，onFulfilled会被调用，当失败时，即reject时，onRejected会被调用。

promise.then在成功和失败时都可以调用，若只想对异常进行处理，则可以采用`promise.then(undefined, onRejected)`,即只指定reject时的回调函数，也可以使用`promise.catch(onRejected)`。
3. Static Method， Promise类的静态方法，比如Promise.all(),Promise.resolve()等，是一些对Promise辅助的方法。

### Promise流程
先看一个Promise示例：
```js
function asyncFunction(){
  // step1
  return new Promise(function(resolve, reject){
    setTimeout(function(){
      resolve('Async Hello world');
    }, 16);
  });
}

// step2
asyncFunction().then(function(value){
  console.log(value); // 'Async Hello world'
}).catch(function(error){
  console.log(error);
});

// 下面的代码的作用与step2中一样
asyncFunction().then(function(value){
  console.log(value); // 'Async Hello world'
}, function(error){
  console.log(error);
});

```
1. step1: 在asyncFunction函数内部，使用new Promise()实例化一个promise对象，然后返回。
2. step2：设置asyncFunction函数返回的promise对象，比如then和catch，该promise对象，会在16ms时被resolve，此时，then的回调函数会被调用，并输出结果`'Async Hello world'`。 

上面的情况中，catch的回调函数不会执行，因为promise返回的是resolve，但若没有setTimeout的话，则会产生异常，此时catch中的回调就会执行。

### Promise状态
任何一个Promise的实例对象都有三种状态（按照ES6 Promise规范和Promises/A+规范描述的术语）：
1. "has-resolution" - Fulfilled，表示成功，即resolve时调用onFulfilled回调函数
2. "has-rejection" - Rejected， 表示失败，即reject时调用onRejected回调
3. "unresolved" - Pending， 表示等待，即不是resolve，也不是reject的状态，是Promise对象创建后的初始化状态。

以上的三种状态，从Pending转换为Fulfilled或Rejected之后，promise对象的状态就不会变化了（即认为Fulfilled和Rejected是Settled不变的）。

### 初次使用Promise
比如用Promise处理XHR(XMLHttpRequest)数据，封装一个getURL函数，返回一个封装了XHR的Promise对象。
```js
function getURL(url){
  return new Promise(function(resolve, reject){
    var req = new XMLHttpRequest();
    req.open('GET', url, true);

    req.onload = function(){
      if(req.status === 200){ //只有在状态为200时，即成功时返回数据
        resolve(req.responseText);
      }else{
        reject(new Error(req.statusText));
      }
    };

    req.onerror = function(){
      reject(new Error(req.statusText));
    };
    req.send();
  });
}

// 测试执行
var url = 'http://httpbin.org/get'; //
getURL(url).then(function onFulfilled(value){
  console.log(value);
}).catch(function onRejected(error){
  console.error(error);
});
// 返回一个json对象，包含了请求的主机的一些信息。比如：
// { "args": {}, "headers": { "Accept": "*/*", "Accept-Encoding": "gzip, deflate, sdch", "Accept-Language": "en-US,en;q=0.8", "Connection": "close", "Host": "httpbin.org", "Origin": "http://liubin.org", "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36" }, "origin": "211.86.158.159", "url": "http://httpbin.org/get" }

var URL = "http://httpbin.org/status/500"; // 服务器返回状态码为500，发生错误
getURL(URL).then(function onFulfilled(value){
    console.log(value);
}).catch(function onRejected(error){
    console.error(error);
});
// Error: INTERNAL SERVER ERROR
```

