---
title: angularjs巩固实践-31-Promise复习
categories:
  - fe
tags:
  - fe
  - angularjs
  - promise
date: 2017-07-28 22:36:56
updated:
---

承诺（Promise）不是 Angular 首创的。作为一种编程模式，它出现在……1976 年，比JavaScript 还要古老得多。Promise 全称是 Futures and promises（未来与承诺）。要想深入了解，可以参见 http://en.wikipedia.org/wiki/Futures_and_promises。
而在 JavaScript 世界中，一个广泛流行的库叫作 Q(https://github.com/kriskowal/q)。而Angular 中的 $q 就是从它引入的。

#### 类比生活中的示例
Promise 解决的是异步编程的问题，用生活中的一个例子对此做一个形象的讲解。

假设有一个家具厂，而它有一个 VIP 客户张先生。
有一天张先生需要一个豪华衣柜，于是，他打电话给家具厂说：“我需要一个衣柜，回头做好了给我送来”，这个操作就叫 $q.defer()，也就是延期。因为这个衣柜不是现在要的，所以张先生这是在发起一个可延期的请求。

家具厂接下了这个订单，给他留下了一个回执号，并对他说：“我们做好了会给您送过去，放心吧”。这叫作 Promise，也就是给了张先生一个“承诺”。

这样，这个 defer 算是正式创建了，于是他把这件事记录在自己的日记上，并且同时记录了回执号，这个变量叫作 deferred，也就是已延期事件。

现在，张先生就不用再去想着这件事了，该做什么做什么，这就是“异步”请求的含义。

假设家具厂在一周后做完了这个衣柜，并如约送到了张先生家（包邮哦，亲），这就叫作 deferred.resolve（衣柜），也就是“问题已解决，这是您的衣柜”。而这时候张先生只取出一下这个“衣柜”参数就行了。而且，这个“邮包”中也不一定只有衣柜，还可以包含别的东西，比如厂家宣传资料、产品名录等。整个过程中轻松愉快，谁也没等谁，没有浪费任何时间。

假设家具厂在评估后发现这个规格的衣柜我们做不了，那么它就需要 deferred.reject （理由），也就是“我们不得不拒绝您的请求，因为……”。拒绝没有时间限制，可以发生在给出承诺之后的任何时候，甚至可能发生在快做完的时候。而且拒绝时候的参数也不仅仅限于理由，还可以包含一个道歉信，违约金之类的。总之，你想给他什么就给他什么，如果你觉得不会惹恼客户，那么不给也没关系。

假设家具厂发现，自己正好有一个符合张先生要求的存货，它就可以用 $q.when（现有衣柜）来兑现给张先生的承诺。于是，这件事立刻解决了，皆大欢喜。张先生可不在乎你是从头做的还是现有的成品，只要达到自己的品质要求就满意了。

假设这个家具厂对客户格外的细心，它还可以通过 deferred.notify（进展情况）给张先生发送进展情况的“通知”。

这样，整个异步流程圆满完成！无论成功还是失败，张先生都没有往里面投入任何额外的时间成本。

再扩展一下这个故事：

张先生又来订货了，这次他分多次订了一张桌子，三把椅子，一张席梦思。但他不希望今天收到个桌子，明天收到个椅子，后天又得签收一次席梦思，而是希望家具厂做好了之后一次性送过来，但是他当初又是分别下单的，那么他就可以重新跟家具厂要一个包含上述三个承诺的新承诺，这就是 $q.all([ 桌子承诺，椅子承诺，席梦思承诺 ])，这样，他就不用再关注以前的三个承诺了，直接等待这个新的承诺完成，到时候只要一次性签收了前面的这些承诺就行了。

#### 回调地狱和 Promise
通过上面这个生活中例子，已经了解到了异步和 Promise 的方式。为什么我们需要 Promise 呢？

JavaScript 是一门很灵活的语言，由于它寄宿在浏览器中以事件机制为核心，所以在 JavaScript 编码中存在很多的回调函数。这是一个高性能的编程模式，所以它衍生出了基于异步 I/O 的高性能 Nodejs 平台。但是如果不注意编码方法，那么就会陷入“回调地狱”，也有人称为“回调金字塔”。嵌套式的回调地狱，代码将会变得像意大利面条一样。如下边的嵌套回调函数一样：
```js
async1(function(){
  async2(function(){
    async3(function(){
      async4(function(){
        ....
      });
    });
  });
});
```
这样嵌套的回调函数，让代码的可读性变得很差，而且很难于调试和维护。所以为了降低异步编程的复杂性，开发人员一直寻找简便的方法来处理异步操作。其中一种处理模式称为 Promise，它代表了一种可能会长时间运行而且不一定必须完成的操作的结果。这种模式不会阻塞和等待长时间的操作完成，而是返回一个代表了承诺的（Promised）结果的对象。它通常会实现一种名叫 then 的方法，用来注册状态变化时对应的回调函数。

Promise 在任何时刻都处于以下三种状态之一：未完成（pending）、已完成（resolved）和拒绝（rejected）三个状态。以 CommonJS Promise/A 标准为例，Promise 对象上的 then 方法负责添加针对已完成和拒绝状态下的处理函数。then 方法会返回另一个 Promise 对象，以便于形成 Promise 管道，这种返回 Promise 对象的方式能够让开发人员把异步操作串联起来，如 then(resolvedHandler, rejectedHandler)。resolvedHandler 回调函数在 Promise 对象进入完成状态时会触发，并传递结果；rejectedHandler 函数会在拒绝状态下调用。

所以上边的嵌套回调函数可以修改为：
```js
async1().then(async2).then(async3).catch(showError);
```

在 ES6 的标准版中已经包含了 Promise 的标准，很快它就将会从浏览器本身得到更好的支持。与此同时在 ES6 的标准版中，还引入了 Python 这类语言中的 generator（迭代器的生成器）概念，它本意并不是为异步而生的，但是它拥有天然的 yield 暂停函数执行的能力，并保存上下文，再次调用时恢复当时的状态，所以它也被很好地运用于 JavaScript 的异步编程模型中，其中最出名的案例当属 Node Express 的下一代框架 KOA 了。

在 ES7 的标准中将有可能引入 async 和 await 这两个关键词，来更大的简化 JavaScript 异步编程模型。就可以如下的方式以同步的方式编写异步代码：
```js
async function sleep(timeout) {
  return new Promise((resolve, reject) => {
    setTimeout(function() {
      resolve();
    }, timeout);
  });
}

(async function() {
  console.log('做一些事情，' + new Date());
  await sleep(3000);
  console.log('做另一些事情，' + new Date());
})();
```

#### Angular 中的 Promise
在 Angular 中大量使用着 Promise，最简单的是 $timeout 的实现:
```js
function timeout(fn, delay, invokeApply) {
  // 创建一个延期请求
  var deferred = $q.defer(),
  promise = deferred.promise,
  skipApply = (isDefined(invokeApply) && !invokeApply),
  timeoutId;

  timeoutId = $browser.defer(function() {
    try {
      // 成功，将触发then的第一个回调函数
      deferred.resolve(fn());
    } catch(e) {
      // 失败，将触发then的第二个回调函数或catch的回调函数
      deferred.reject(e);
      $exceptionHandler(e);
    } finally {
      delete deferreds[promise.$$timeoutId];
    }
    if (!skipApply) $rootScope.$apply();
  }, delay);

  promise.$$timeoutId = timeoutId;
  deferreds[timeoutId] = deferred;
  // 返回承诺
  return promise;
}
timeout.cancel = function(promise) {
  if (promise && promise.$$timeoutId in deferreds) {
    deferreds[promise.$$timeoutId].reject('canceled');
    delete deferreds[promise.$$timeoutId];
    return $browser.defer.cancel(promise.$$timeoutId);
  }
  return false;
};
```
