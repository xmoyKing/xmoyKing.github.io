---
title: Promise2-实践练习
categories:
  - promise
tags:
  - promise
date: 2017-04-10 17:27:46
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
上面是一个很简短的方法链的then - catch的例子，若方法链很长，而且没一个promise对象中都注册了onFulfilled和onRejected，这时执行流程将会如何呢？
```js
function TA(){
  console.log('task A');
}
function TB(){
  console.log('task B');
}
function onRejected(err){
  console.log('catch Error: A or B', err);
}
function finalTask(){
  console.log('final task');
}

var promise = Promise.resolve();
promise.then(TA)
  .then(TB)
  .catch(onRejected)
  .then(finalTask);

// 正常运行结果如下：
// task A
// task B
// final Task
```
![流程图](http://liubin.org/promises-book/Ch2_HowToWrite/img/promise-then-catch-flow.png)


上述流程中，没有在then中指定onRjected参数，也就是说，若TA，TB出现错误将会被catch捕捉，然后接着执行finalTask，而且无论如何都会执行finalTask.即：
若TA出现异常，会按照TA - onRjected - finalTask 流程处理，会跳过TB，直接执行onRejected
若TB出现异常，会按照TA - TB - onRjected - finalTask 

#### 方法链中传递参数
若上一个任务想给下一个任务（或后面的任务）传递参数时，可以使用return返回值。
```js
function TA(value){
  console.log('task A');
  return value + 1;
}
function TB(value){
  console.log('task B');
  return value * 2;
}
function onRejected(err){
  console.log('catch Error: A or B', err);
}
function finalTask(value){
  console.log('final task, value:' + value);
}

var promise = Promise.resolve(1); // 将1传入方法链中
promise.then(TA) // 输出log，然后将2返回，传入后续方法
  .then(TB) // 输出log，将4返回，传入后续方法
  .then(finalTask) // 输出log和value值
  .catch(onRejected); // 捕获错误
// 输出：
// task A
// task B
// final task, value:4
```
在每次方法或任务中return的值可以是任意的类型，return的值将会被Promise.resolve(value);进行包装，因此无论返回的什么样的值，then都会返回一个promise对象，后面也只会接手到一个promise对象。

注意：Promise.catch()仅仅只是promise.then(undefined, onRjected);的一个别名而已，算是一个语法糖，所以，这个方法实际上是指定当promise对象状态变为Rejected时的回调函数的。
在ES中，catch是保留字，所以报 `identifier not found` 的语法错误时，可以考虑使用中括号的方式调用：`Promise['catch']()`

若每次then都是创建新的promise对象，则多次使用同一个promise对象的then方法和链式调用同一个函数的差别就能看出来：
```js
// 多次使用同一个promise对象的then方法
var aPromise = new Promise(function(resolve){
  resolve(100);
});
aPromise.then(function(value){
  return value * 2;
});
aPromise.then(function(value){
  return value * 2;
});
aPromise.then(function(value){
  console.log('value a :', value); // value a : 100
});

// 链式调用同一个函数
var bPromise = new Promise(function(resolve){
  resolve(100);
});
bPromise.then(function(value){
  return value * 2;
}).then(function(value){
  return value * 2;
}).then(function(value){
  console.log('value b :', value); // value b : 400
});
```
上述a中情况是，由于没有使用链式调用，then的调用几乎是同时开始执行，而传给每个then方法的value值都是100，所以最后输出的是100。

b的情况是，由于链式调用，多个then方法串在一起，会严格按照resolve - then - then - then 的顺序执行，并且传给每个then方法的value值都是前一个promise对象通过return返回的值。

实际上，不仅仅是then会返回一个promise对象，catch也是如此，两者都返回**新的promise对象**：
```js
var aPromise = new Promise(function(resolve){
  resolve(100);
});
var thenPromise = aPromise.then(function(value){
  console.log(value);
});
var catchPromise = thenPromise.catch(funtion(err){
  console.error(err);
});

console.log(aPromise !== thenPromise); // true
console.log(catchPromise !== thenPromise); // true
```

同时，then的错误用法很容易出现一些问题
```js
// 错误用法
function badAsyncCall(){
  var promise = Promise.resolve();
  promise.then(function(){
    // 处理...
    return newval;
  });
  return promise;
}

// 正确用法，直接返回then函数调用
function rightAsyncCall(){
  var promise = Promise.resolve();
  return promise.then(function(){
    // 处理...
    return newval;
  });
}
```
错误用法的问题：
1. promise.then中产生的异常无法被外界捕捉
2. 无法得到then的返回值，return语句没有起作用

### Promise和数组
常常有这样的需求：需要一个函数在多个异步都完成之后再进行调用，使用原生普通的XHR回调函数为例：
```js
function getURLCallback(URL, callback) {
    var req = new XMLHttpRequest();
    req.open('GET', URL, true);
    req.onload = function () {
        if (req.status === 200) {
            callback(null, req.responseText);
        } else {
            callback(new Error(req.statusText), req.response);
        }
    };
    req.onerror = function () {
        callback(new Error(req.statusText));
    };
    req.send();
}
// <1> 对JSON数据进行安全的解析，捕捉可能出现的错误
function jsonParse(callback, error, value) {
    if (error) {
        callback(error, value);
    } else {
        try {
            var result = JSON.parse(value);
            callback(null, result);
        } catch (e) {
            callback(e, value);
        }
    }
}
// <2> 发送XHR请求
var request = {
        comment: function getComment(callback) {
            return getURLCallback('http://azu.github.io/promises-book/json/comment.json', jsonParse.bind(null, callback));
        },
        people: function getPeople(callback) {
            return getURLCallback('http://azu.github.io/promises-book/json/people.json', jsonParse.bind(null, callback));
        }
    };
// <3> 启动多个XHR请求，当所有请求返回时调用callback
function allRequest(requests, callback, results) {
    if (requests.length === 0) {
        return callback(null, results);
    }
    var req = requests.shift();
    req(function (error, value) {
        if (error) {
            callback(error, value);
        } else {
            results.push(value);
            allRequest(requests, callback, results);
        }
    });
}
function main(callback) {
    allRequest([request.comment, request.people], callback, []);
}
// 运行的例子
main(function(error, results){
    if(error){
        return console.error(error);
    }
    console.log(results);
});
```
上述回调函数的需要注意的点如下：
1. 直接使用JSON.parse函数可能会抛出出错（JSON对格式检查非常严格，有任何不匹配都会报错），所以这里使用一个try catch的函数包装一下，捕捉有可能出现的错误。
2. 将多个回调进行嵌套处理层次会比较深，所以采用数组的形式进行依次调用
3. 回调函数采用callback(err，val)的形式，第一个表示错误信息，第二个为返回值
4. 在用到jsonParse函数的时候，使用了bind函数绑定，通过这种方式减少匿名函数使用
```js
jsonParse.bind(null, callback);
// 与如下语句作用相当
function bindJSONParse(err, val){
  jsonParse(callback, err, val);
}
```
问题如下：
1. 需要显示的进行异常处理，每一个回调都需要
2. 为了让嵌套不深，需要一个对request进行处理的函数
3. 回调函数非常多

返回结果截图：
![返回结果截图](1.png)

上述代码用promise改造后代码如下：
```js
function getURL(url){
  return new Promise(function(resolve,reject){
    var req = new XMLHttpRequest();
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
    req.send();
  });
}

var request = {
  comment: function getComment(){
    return getURL('http://azu.github.io/promises-book/json/comment.json').then(JSON.parse);
  },
  people: function getPeople(){
    return getURL('http://azu.github.io/promises-book/json/comment.json').then(JSON.parse);
  }
};

function main(){
  function recordValue(results, value){
    results.push(value);
    return results;
  }

  // [] 用来保存初始化值
  var pushValue = recordValue.bind(null, []);
  return request.comment().then(pushValue).then(request.people).then(pushValue);
}

// 执行
main().then(function(value){
  console.log(value);
}).catch(function(error){
  console.log(error);
});
```
和回调函数风格相比，promise可以直接使用JSON.parse函数，main函数返回promise对象，错误处理的地方直接对返回的promise对象处理。

### Promise.all()
这种需要对多个异步调用都进行统一处理的场景，Promise.all(), Promise.race()可以更方便处理。
Promise.all()接收一个promise对象的数组作为参数，当这个数组里的所有promise对象全部变为resolve或reject状态的时候，才会去调用then方法。
向Promise.all()传递一个封装了XHR请求的promise数组，则在所有XHR请求都Fulfilled或Rejected状态之后，才调用then方法。
```js
function main(){
  return Promise.all([request.comment(), request.people()]);
}
```
main中的处理流程变得非常清晰，comment和people同时开始执行，而且执行结果同定义在数组中的顺序一致。
```js
main().then(function(value){
  console.log(value); // 按照[comment, people]的顺序接收
}).catch(function(error){
  console.log(error);
});
```

使用一个计时器程序，可以测试Promise.all()中的参数数组里的promise是同时开始执行的
```js
// delay 毫秒后执行resolve
function timerPromisefy(delay){
  return new Promise(function(resolve){
    setTimeout(function(){
      resolve(delay);
    },delay);
  });
}
var startDate = Date.now();

// 所有的promise变为resolve后程序退出
Promise.all([
  timerPromisefy(1),
  timerPromisefy(32),
  timerPromisefy(64),
  timerPromisefy(128)
]).then(function(values){
  console.log(Date.now() - startDate + 'ms'); // 129ms，约128ms
  console.log(values); // [1,32,64,128]
})
```
结果表示，确实只用了128ms左右，表示所有的promise对象是同时执行的不是顺序执行，因为若顺序执行则需要1 + 32 +64 +128 = 225ms左右的时间。

### Promise.race()
和Promise.all一样，接收一个promise对象数组为参数。

Promise.all 在接收到的所有的对象promise都变为 FulFilled 或者 Rejected 状态之后才会继续进行后面的处理，
而Promise.race 只要有一个promise对象进入 FulFilled 或者 Rejected 状态的话，就会继续进行后面的处理。
```js
// delay 毫秒后执行resolve
function timerPromisefy(delay){
  return new Promise(function(resolve){
    setTimeout(function(){
      console.log('delay:',delay);
      resolve(delay);
    },delay);
  });
}
// 任何promise变为resolve后程序退出
Promise.race([
  timerPromisefy(1),
  timerPromisefy(32),
  timerPromisefy(64),
  timerPromisefy(128)
]).then(function(values){
  console.log(values); // 1
});
// 输出
// delay: 1
// 1
// delay: 32
// delay: 64
// delay: 128

```
在第一个promise对象确定后，注册在then中的函数就调用返回了，结果就是then输出1，但是后面的promise对象依然会执行。

### then 、 catch
许多建议中，将catch和then分开使用进行错误错误，那么在 .then 里同时指定处理对错误进行处理的函数相比，和使用 catch 有什么差别么？
```js
function throwError(value) {
    // 抛出异常
    throw new Error(value);
}
// <1> onRejected不会被调用
function badMain(onRejected) {
    return Promise.resolve(42).then(throwError, onRejected);
}
// <2> 之前的promise有异常发生时onRejected会被调用
function badMain2(onRejected) {
    return Promise.resolve(42).then(throwError).then(throwError, onRejected);
}
// <3> 有异常发生时onRejected会被调用
function goodMain(onRejected) {
    return Promise.resolve(42).then(throwError).catch(onRejected);
}
// 运行示例
badMain(function(){
    console.log("BAD");
});
goodMain(function(){
    console.log("GOOD");
});
badMain2(function(){
    console.log("BAD2");
});
// 输出：
// GOOD
// BAD2
// Promise对象，Uncaught (in promise) Error: 42
```
上述代码中，badMain中，onRejected函数并不能捕捉throwError抛出的错误，所以得出结论：then中的onRejected只能不错前面的promise对象的错误

### 总结
1. 使用promise.then(onFulfilled, onRejected)时，在onFulfilled中发生的异常，在onRjected中无法捕捉
2. 在promise.then(onFulfilled).catch(onRejected)的情况下，then中产生的异常能在catch捕获
3. then和catch本质没有区别，catch是then(undefined, onRejected)的一个语法糖，但是需要分场合使用