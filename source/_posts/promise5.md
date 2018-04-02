---
title: Promise-5-高级进阶(deferred,race)
categories:
  - JavaScript
tags:
  - Promise
  - deferred
date: 2017-04-14 08:36:26
updated:
---

简要介绍一下Deferred和Promise的关系

## Defferred 和 Promise
Deferred这个术语，其实就是指延迟到未来某个点再执行，是一种回调函数解决方案，可以解决耗时很长的操作的回调问题。

PS:可以参考[jQuery的deferred对象详解](http://www.ruanyifeng.com/blog/2011/08/a_detailed_explanation_of_jquery_deferred_object.html)

Deferred和Promise不同，Deferred没有规范，每个库可以自行实现和扩展。以下以jQuery.Deferred为例。
1. Deferred包含了Promise
2. Deferred有能对Promise状态进行操作的特权方法

一个自定义的基于Promise实现的Deferred示例，
```js
// 基于Promise实现Deferred的例子
function Deferred(){
  this.promise = new Promise(function(resolve, reject){
    this._resolve = resolve;
    this._reject = reject;
  }.bind(this));
}
Deferred.prototype.resolve = function(value){
  this._resolve.call(this.promise, value);
};
Deferred.prototype.reject = function(reason){
  this._reject.call(this.promise, reason);
};

// 将getUrl用Deferred改写
function getUrl(url){
  var deferred = new Deferred();
  var req = new XMLHttpRequest();
  req.open('GET',url,true);
  req.onload = function(){
    if(req.status === 200){
      deferred.resolve(req.responseText);
    }else{
      deferred.reject(new Error(req.statusText));
    }
  };
  req.error = function(){
    deferred.reject(new Error(req.statusText));
  };
  req.send();
  return deferred.promise;
}
// 执行
var url = 'http://httpbin.org/get';
getUrl(url).then(function onFulfilled(value){
  console.log(value);
}).catch(console.error.bind(console));
// 等待一段时间，XHR回调输出如下json
{
  "args": {},
  "headers": {
    "Accept": "*/*",
    "Accept-Encoding": "gzip, deflate, sdch",
    "Accept-Language": "en-US,en;q=0.8",
    "Connection": "close",
    "Host": "httpbin.org",
    "Origin": "http://liubin.org",
    "Referer": "http://liubin.org/promises-book/",
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36"
  },
  "origin": "122.193.105.218",
  "url": "http://httpbin.org/get"
}
```
上述中能对Promise状态进行操作的特权方法指的是能对promise对象状态进行resolve和reject的方法，而Promise通常只能在构造函数传递的方法之内对promise对象状态进行操作。


基于XHR,Promise实现的getUrl：
```js
function getUrl(url){
  return new Promise(function(resolve, reject){
    var req = new XMLHttpRequest();
    req.open('GET',url,true);
    req.onload = function(){
      if(req.status === 2000){
        resolve(req.responseText);
      }else{
        reject(new Error(req.statusText));
      }
    };
    req.onerror = function(){
      reject(new Error(req.statusText));
    };
    req.send();
  })
}
// 执行
var url = 'http://httpbin.org/get';
getUrl(url).then(function onFulfilled(value){
  console.log(value);
}).catch(console.error.bind(console));
// 输出结果与上式相同
```
Promise与Deferred（都是由XHR实现）相比有如下异同：
1. 异：Deferred不需要将代码用Promise包起来，可以减少一层嵌套和缩进，但也没有了Promise的错误处理，但是Deferred有对Promise进行操作的特权方法，所以能对流程进行高度自由的操作
2. 同：处理流程相同，都是调用resolve和reject，函数都返回promise对象

比如：一般Promise在构造函数中编写主要处理逻辑，对resolve和reject方法的调用时机基本是确定的。
```js
// 使用Promise
new Promise(function(resolve,reject){
  // 逻辑处理代码段，对promise对象的状态确定
});

// 使用Deferred
var deferred = new Deferred();
// 逻辑处理，在任意时机调用resolve和reject
```
而使用Deferred的话，不需要将处理逻辑编写为一大块，只需要先创建deferred对象，可以在任何时候对resolve和reject进行调用。

总结：
如果说Promise是对值进行抽象的话，Deferred则是对处理还没有结束的状态或操作进行抽象化。即：Promise代表一个对象，这个对象状态不确定，但在未来某个时间点是能确定的。
而Deferred对象代表了一个处理还没有结束，在它处理结束的时候，需要通过Promise对象来取得处理结果。

## 使用Promise.race 和 delay取消XHR

### 基于Promise.race，使用Promise.race实现超时机制。
XHR有timeout属性，使用该属性能简单实现超时功能，但当涉及多个XHR对象同时超时时，需要采用更容易理解的异步方法在XHR中通过超时来实现取消正在进行的操作。

Promise使用setTimeout实现超时
```js
function delayPromise(ms){
  return new Promise(function(resolve){
    setTimeout(resolve, ms);
  });
}
setTimeout(function(){
  console.log('setTimeout after 100ms!', Date());
}, 1000);
// 与上面的同时输出
delayPromise(1000).then(function(){
  console.log('delayPromise after 100ms!', Date());
});
```
delayPromise返回一个经过onFulfilled处理的promise对象，直接使用setTimeout函数相比，仅仅是编码不同。

Promise.race的作用为在任何一个promise对象进入到确定的状态后就执行后续处理，如下示例：
```js
var winnerPromise = new Promise(function(resolve){
  setTimeout(function(){
    console.log('this is winner');
    resolve('this is winner');
  }, 4);
});
var loserPromise = new Promise(function(resolve){
  setTimeout(function(){
    console.log('this is loser');
    resolve('this is loser');
  }, 1000);
});

// 第一个promise变为resolve后程序停止
Promise.race([winnerPromise, loserPromise]).then(function(value){
  console.log(value); // this is winner
});
```

将delayPromise与其他promise对象一起放到Promise.race来实现简单的超时机制。
函数timeoutPromise接收两个参数，第一个是需要使用超时机制的promise对象，第二个是超时时间，返回一个由Promise.race创建的竞争的promise对象
```js
function timeoutPromise(promise, ms){
  var timeout = delayPromise(ms).then(function(){
    throw new Error('timeout after '+ ms +' ms');
  });
  return Promise.race([promise, timeout]);
}
// 运行
var taskPromise = new Promise(function(resolve){
  // 一些操作
  var delay = Math.random() * 2000;
  setTimeout(function(){
    resolve(delay + 'ms');
  }, delay);
});

timeoutPromise(taskPromise, 1000).then(function(value){
  console.log('taskPromise在规定时间内结束：'+ value);
}).catch(function(error){
  console.log('超时', error);
});
// 输出：
// 正常：taskPromise在规定时间内结束 : 141.978790332816ms
// 超时：超时,Error: Operation timed out after 1000 ms
```

### 自定义TimeouError类
虽然在超时的时候确实抛出错误，但是无法区分是普通的错误类型还是我们定义的超时错误类型。可以定一个Error对象的子类TimeoutError来做出区分。

Error对象是ES的内建对象，由于stack trace等原因，在ES6之前是无法创建一个完美继承内建类的类，在但ES6中可以通过class语法来定义类的继承关系
```js
class MyError extends Error{
  // 继承Error类的对象
}
```

为了让TimoutError能支持类似`error instanceof TimeoutError`的使用方法，需要修改原型链上的构造器, 继承Error.prototype
```js
function copyOwnFrom(target, source){
  Object.getOwnPropertyNames(source).forEach(function(propName){
    Object.defineProperty(target, propName, Object.getOwnPropertyDescriptor(source, propName));
    return target;
  });
}
function TimeoutError(){
  var superInstance = Error.apply(null, arguments);
  copyOwnFrom(this, superInstance);
}
TimeoutError.prototype = Object.create(Error.prototype);
TimeoutError.prototype.constructor = TimeoutError;

// 执行
var promise = new Promsie(function(){
  throw TimeoutError('timeout');
});
promise.catch(function(error){
  console.log(error instanceOf TimeoutError); // true
});
```

### 通过超时取消XHR操作
取消XHR操作可以调用XMLHttpRequest对象的abort方法实现，为了能在外部调用abort方法，可以对getUrl进行扩展，cancelalbeXHR返回一个包装XHR的promise对象，这个对象还有一个abort方法取消XHR请求。
```js
function cancelableXHR(url){
  var req = new XMLHttpRequest();
  var promise = new Promise(function(resolve, reject){
    req.open('GET', url, true);
    req.onload = function(){
      if(req.status === 200){
        resolve(req.responseText);
      }else{
        reject(new Error(req.statusText));
      }
    };
    req.onerror = function(){
      reject(new Error(req.statusText));
    };
    req.onabort = function(){
      reject(new Error('abort this request'));
    };
    req.send();
  });

  var abort = function(){
    // 若request还没结束则执行abort
    if(req.readyState !== XMLHttpRequest.UNSENT){
      req.abort();
    }
  };
  return {
    promise: promise,
    abort: abort
  };
}
```

有了cancelableXHR之后，基于它编写普通的Promise处理流程即可：
1. 通过cancelableXHR方法取得包装XHR的promise对象，和取消该XHR请求的abort方法。
2. 在timeoutPromise方法中通过Promise.race让XHR保证的promise和超时promise进行竞争。
  - XHR在超时前返回结果的话，则和正常promise一样，通过then返回请求结果
  - 若超时则抛出throw TimeoutError异常并且被catch捕获
  - catch捕获的异常若是TimeoutError的话，则调用abort方法取消XHR请求
```js
// 执行
var object = cancelableXHR('http://httpbin.org/get');

timeoutPromise(object.promise, 1000).then(function (contents) {
    console.log('Contents', contents);
}).catch(function (error) {
    if (error instanceof TimeoutError) {
        object.abort();
        return console.log(error);
    }
    console.log('XHR Error :', error);
});
// 输出：
// Contents,{ "args": {}, "headers": { "Accept": "*/*", "Accept-Encoding": "gzip, deflate, sdch", "Accept-Language": "en-US,en;q=0.8", "Connection": "close", "Host": "httpbin.org", "Origin": "http://liubin.org", "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36" }, "origin": "122.193.105.218", "url": "http://httpbin.org/get" }
```
在上述的cancelableXHR中，promise对象及其操作方法都是在一个对象中返回的，这样做的好处是，不用将所有的操作都放在一个函数中完成，一个函数只返回一个值（可以是对象），对象中可以包含多个方法，每个方法完成不同的工作，减少单个方法的复杂度，同时方便扩展，阅读和维护。

将这些处理封装为一个模块（AMD，CommonJS，ES6 module …），比如：将cancelableXHR封装为一个Nodejs模块：
```js
'use strict';
var requestMap = {};
function createXHRPromise(url){
  var req = new XMLHttpRequest();
  var promise = new Promise(function(resolve, reject){
    req.open('GET',url,true);
    req.onreadystatechange = function(){
      if(req.readyState === XMLHttpRequest.DONE){
        delete requestMap[url];
      }
    };
    req.onload = function(){
      if(req.state === 2000){
        resolve(req.responseText);
      }else{
        reject(new Error(req.statusText));
      }
    };
    req.onerror = function(){
      reject(new Error(req.statusText));
    };
    req.onabort = function(){
      reject(new Error('abort this req'));
    };
    req.send();
  });

  requestMap[url] = {
    promise: promise,
    request: req
  };
  return promise;
}

function abortPromise(promsie){
  if(typeof promise === 'undefined'){
    return;
  }
  var request;
  Object.keys(requestMap).some(function(url){
    if(requestMap[url].promise === promise){
      request = requestMap[url].request;
      return true;
    }
  });
  if(request != null && request.readyState !== XMLHttpRequest.UNSENT){
    request.abort();
  }
}
module.exprots.createXHRPromise = createXHRPromise;
module.exprots.abortPromise = abortPromise;
```

测试模块：创建包装XHR的promise对象，取消prmise对象的请求
```js
var cancelableXHR = require('./cancelableXHR');
var xhrPromise = cancelableXHR.createXHRPromise('http://httpbin.org/get');
xhrPromise.catch(function (error) {
    // 调用 abort 抛出的错误
});
cancelableXHR.abortPromise(xhrPromise);
```

## Promise.prototype.done

## 方法链

## 基于Promise顺序处理