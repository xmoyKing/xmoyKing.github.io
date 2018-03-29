---
title: Promise4-高级进阶(resolve,reject)
categories:
  - JavaScript
tags:
  - promise
  - web notifications
date: 2017-04-13 22:54:03
updated:
---

基于前面学过的一些Promise知识，深入了解Promise里的一些内容，加深理解。

## Promise.resolve 和 Thenable
Promise.resolve最大的一个特性就是可以将thenable对象转换为promise对象，这一节就具体了解这个转换过程

以桌面通知Web Notifications API为例，它能通过浏览器在桌面显示通知消息， 关于Web Notifications 可以参考[使用 Web Notifications - WebAPI | MDN](https://developer.mozilla.org/zh-CN/docs/Web/API/notification/Using_Web_Notifications)
```js
// 由于Web Notification涉及到桌面通知，所以选哟先获取权限
// 通过如下语句可以发起请求，向用户请求权限，
Notification.requestPermission(function(status){
  console.log(status); //分别有：默认询问default，允许granted，拒绝denied
});

// 在已经获得权限的情况下，在浏览器运行如下语句能在桌面弹出提示
new Notification('Hi!');
```
Notification的granted和denied与Promise的resolve和reject很相似。

先用回调函数的方式对WN(仅仅是本文对Web Notification的简称)包装函数进行重写：
```js
function notifyMsg(msg, opts, cb){
  if(Notification && Notification.permission === 'granted'){ // 若已经获取到权限
    var notification = new Notification(msg, opts);
    cb(null, notification);

  }else if(Notification.requestPermission){
    Notification.requestPermission(function(status){
      if(Notification.permission !== status){
        Notification.permission = status;
      }

      if(status === 'granted'){
        var notification = new Notification(msg, opts);
        cb(null, notification);
      }else{
        cb(new Error('user denied'));
      }
    });

  }else{
    cb(new Error('do not support WN'));
  }
}

// 执行,第二个参数是opts对象
notifyMsg('Hi', {}, function(error,notification){
  if(error){
    return console.log(error); // 失败时，打印错误：Error: user denied
  }
  console.log(notification); // 成功获得权限则打印notification对象
});
```
上述代码，一旦用户选择第一次拒绝或同意之后就不会再弹出请求弹窗了，而是直接在控制台输出信息。

若要将上述代码转变为promise风格,可以加上如下代码：
```js
function notifyMsgPromise(msg, opts){
  return new Promise(function(resolve, reject){
    notifyMsg(msg, opts, function(error, notification){
      if(error){
        reject(error);
      }else{
        resolve(notification);
      }
    });
  });
}
// 执行
notifyMsgPromise('Hi').then(function(notification){
  console.log(notification);
}).catch(function(error){
  console.error(error);
})
```
上述代码，当用户同意时，then函数会被调用，然后显示Hi信息在桌面，当用户拒绝时，catch会被调用。

注意：由于浏览器是以网站为单位保存WN的许可状态的，而状态有四种，分别为：
1. 已经获得用户许可, then方法会被调用
2. 弹出对话框并获得许可，then方法会被调用
3. 已经被童虎拒绝，catch方法会被调用
4. 弹出对话框并被拒绝，catch方法会被调用
即：当使用原生的WN时，需要对上述四种程序进行处理，可以将四种情况包装简化为两种处理方式。

### WN包装函数wrapper
*PS:貌似同前面没什么差别，暂时没记录*

### Thenable
thenable就是一个具有then方法的对象，下面在回调函数风格的代码中增加一个返回值为thenable类型的方法，具体如下：
```js
function notifyMsg(msg, opts, cb){
  if(Notification && Notification.permission === 'granted'){
    var notification = new Notification(msg, opts);
    cb(null, notification);

  }else if(Notification.requestPermission){
    Notification.requestPermission(function(status){
      if(Notification.permission !== status){
        Notification.permission = status;
      }
      if(status === 'granted'){
        var notification = new Notification(msg, opts);
        cb(null, notification);
      }else{
        cb(new Error('user denied'));
      }
    });

  }else{
    cb(new Error('do not support Notification'));
  }
}

// 返回thenable
function notifyMsgThenable(msg, opts){
  return {
    'then': function(resolve, reject){
      notifyMsg(msg, opts, function(error, notification){
        if(error){
          reject(error);
        }else{
          resolve(notification);
        }
      });
    }
  };
}

// 执行
Promise.resolve(notifyMsgThenable('message')).then(function(notification){
    console.log(notification);
}).catch(function(error){
    console.error(error);
});
```
上述代码中的notifyMsgThenable方法返回的对象有then方法，then方法的参数和`new Promise(function(resolve, reject){})`一样，在确定时调用resolve，拒绝时调用reject。

notifyMsgThenable和notifyMsgPromise一样，Promise.resolve(thenable)都能调用，这里的thenable是一个promise对象。

这种Thenable对象的封装表现为回调和Promise风格之间，即：Callback - Thenable - Promise. 这种Thenable最大的用处可能是能将一个对象在不同的Promise类库（基于Promise标准，但实现不同，可能有一些独特的方法和限制）之间进行转换。

比如类库Q的Promise实例为Q Promise，就提供了ES6 Promises的实例对象不具备的方法，如：`promise.finally(cb) 和 promise.nodeify(cb)`。

```js
// 将ES6 Promise转换为Q Promise
var Q = require('Q');

// ES6中的promise对象
var promise = new Promise(function(resolve){
  resolve(1);
});

// 转换为Q promise对象
Q(promise).then(function(value){
  console.log(value);
}).finally(function(){
  console.log('finally');
});
```
上述代码中promise对象在创建时具备then方法， 因此可以通过Q(thenable)将这个对象转换为Q Promise对象。


## 使用reject而不是throw
Promise的构造函数，以及被then调用执行的函数可以认为是在try catch中执行的，所以这些代码中即使执行了throw语句，也不会导致程序异常终止。
若在Promise中使用throw语句，会被catch捕获，同时promise对象变为Rejected状态
```js
var promise = new Promise(function(resolve, reject){
  throw new Error('message');
});

promise.catch(function(error){
  console.error(error); // 'message'
});
```
上述代码运行不会出问题，但是在更改promise对象状态的时候，使用reject方法更合理更清晰。
```js
var promise = new Promise(function(resolve, reject){
  reject('message');
});

promise.catch(function(error){
  console.error(error); // 'message'
});
```
使用reject还有一个好处：throw语句无法区分是否是我们主动抛出的还是其他的非预期的异常导致的，而reject可以确定是我们主动调用的。

当在then中进行reject，想要像下面那样在then中进行reject？
```js
var promise = Promise.resolve();
promise.then(function(value){
  setTimeout(function(){
    //... 一段时间后若还没处理完则进行reject
  }, 1000);
  // 一些耗时任务
  somethingHardwork();
}).catch(function(error){
  // 捕获超时错误
});
```
上述代码需要在then中reject调用，但是传递给当前的回调函数的参数只有前面的一个promise对象，该怎么办？
此处需要利用then中的return功能，返回值不仅仅是简单的字面量，还可以是复杂的对象类型，这个返回的值能传给后面的then或catch。同时若返回一个promise对象，则可以根据这个promise对象状态，在下一个then中指定回调函数的onFulfilled和onRjected的哪一个调用是确定的。
```js
var promise = Promise.resolve();
promise.then(function(){
  var retPromise = new Promise(function(resolve, reject){
    // resolve和reject的状态是onFulfilled或onRejected
  });
  return retPromise;
}).then(onFulfilled, onRejected);
```
上述代码，then的待用函数promise对象的状态来决定的。

也就是说，这个retPromise对象状态为Rejected，会调用后面then的onRejeected方法。
```js
var onRejected = console.error.bind(console);
var promise = Promise.resolve();
promise.then(function(){
  var retPromis = new Promise(function(resolve, reject){
    reject(new Error('this promise is rejected'));
  });
}).catch(onRejected);
// 下面代码是对上面代码的简化
var onRejected = console.error.bind(console);
var promise = Promise resolve();
promise.then(function(){
  return Promise.reject(new Error('this promise is rejected'));
}).catch(onRejected);
```
