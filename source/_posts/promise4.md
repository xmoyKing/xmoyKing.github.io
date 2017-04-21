---
title: Promise4-高级进阶
categories:
  - fe
tags:
  - fe
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



## 使用reject而不是throw

## Defferred 和 Promise

## 使用Promise.race 和 delay取消XHR

## Promise.prototype.done

## 方法链

## 基于Promise顺序处理