---
title: Promise实战
categories:
  - fe
tags:
  - fe
  - promise
date: 2017-04-20 17:27:46
updated: 2017-04-20 17:27:46
---

本次学习Promise提供的各种方法以及错误处理

### 快捷方式
Promise对象提供了许多静态方法，比如`Promise.resolve(value)`可以看作是new Promise()的快捷方式。
```js
Promise.resolve(42);
// 上行的作用同下面的代码一样，可以看作是下面方法的快捷方式，或语法糖
new Promise(function(resolve, reject){
  resolve(42);
})
```
`Promise.resolve(value)`的返回值就是一个Promise对象，所以可以直接链式操作，进行then调用
```js
Promise.resolve(42).then(function(value){
  console.log(value); // 42
});
```
Promise.resolve()的另外一个作用是将一个thenable对象（简单的说，即具有.then方法的对象）转换为Promise对象（有点像将具有.length属性的对象成为Array like对象一样），这里的then方法应该与Promise对象所有的then方法具有一样的功能和处理流程。 然后就能直接使用then或catch等ES6 Promise中定义的方法了。

比如jQuery.ajax(),它的返回值就是thenable的对象，但是毕竟不是原生的Promise生成的对象，所以转换后的promise会出现一些问题，
```js
var promise = Promise.resolve($.ajax('file.json')); // 返回一个promise对象
promise.then(function(value){
  console.log(value);
});
```
所以，Promise.resolve方法的作用就是将传递给它的参数填充到一个promise对象，然后返回这个promise对象。

可以类比到Promise.reject()，不同的是Promise.reject()静态方法调用的是reject，而不是resolve。
```js
Promise.reject(new Error('BOOM！')).catch(function(error){
  console.error(error); // Error: BOOM!
});
```

在使用then的过程中，可能认为then指定的方法是同步的，而实际上是异步的。
```js
var promise = new Promise(function(resolve){
  console.log('inner promise'); // 1
  resolve(42);
});
promise.then(function(value){
  console.log(value); // 3
});
console.log('outer promise'); // 2

// 输出:
// inner promise
// outer promise
// 42
```

### 同步、异步调用可能存在的问题
根据Effective JS 67条，不要对异步回调函数进行同步调用。
1. 绝对不能对异步回调函数（即使在数据已经就绪）进行同步调用。
2. 如果对异步回调函数进行同步调用的话，处理顺序可能会与预期不符，可能带来意料之外的后果。
3. 对异步回调函数进行同步调用，还可能导致栈溢出或异常处理错乱等问题。
4. 如果想在将来某时刻调用异步回调函数的话，可以使用 setTimeout 等异步API。

比如一个onReady()函数，会根据具体的情况，选择以同步还是异步的方式对回调函数进行调用。
```js
function onReady(fn){
  var readyState = document.readyState;
  if(readyState === 'interactive' || readyState === 'complete'){
    fn(); // 同步调用
  }else{
    window.addEventListener('DOMContentLoaded', fn); // 异步调用
  }
}

onReady(function(){
  console.log('DOM fully loaded and parsed');
});
console.log('==Starting==');

// 输出（不用情况会输出不同顺序）：
// DOM fully loaded and parsed
// ==Starting==
```

以上代码就会在某些情况下出现问题，解决的方式就是使用setTimeout或promise
```js
// setTimeout的方式 --------------------------------------
function onReady(fn){
  var readyState = document.readyState;
  if(readyState === 'interactive' || readyState === 'complete'){
    setTimeout(fn, 0); // 异步调用
  }else{
    window.addEventListener('DOMContentLoaded', fn); // 异步调用
  }
}

onReady(function(){
  console.log('DOM fully loaded and parsed');
});
console.log('==Starting==');

// Promise的方式 --------------------------------------

function onReadyPromise(){
  return new Promise(function(resolve, reject){
    var readyState = document.readyState;
    if(readyState === 'interactive' || readyState === 'complete'){
      resolve();
    }else{
      window.addEventListener('DOMContentLoaded', resolve); // 异步调用
    }
  });
}

onReadyPromise().then(function(){
  console.log('DOM fully loaded and parsed');
});
console.log('==Starting==');

// 输出：
// ==Starting==
// DOM fully loaded and parsed
```

### 方法链 promise chain 
由于then方法返回一个promsie对象，所以可以使用链式调用
```js
aPromise.then(function taskA(value){
  // task A
}).then(function taskB(vaue){
  // task B
}).catch(function onRejected(error){
    console.log(error);
});
```
