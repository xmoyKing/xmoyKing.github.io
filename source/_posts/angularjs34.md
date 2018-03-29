---
title: AngularJS巩固实践-34-脏检查机制
categories:
  - AngularJS
tags:
  - AngularJS
  - JavaScript
  - ng脏检查
  - AngularJS深度剖析
date: 2017-08-07 18:19:58
updated:
---

“脏检查”时ng中的核心机制之一，它时实现双向绑定、MVVM模式的重要基础。用一句话概括即：ng将双向绑定转换为一堆watch表达式，然后递归检查这些watch表达式的结果是否变了，若变了，则执行相应的watcher函数，等到Model的值不再变化，也就不再有watcher函数被触发，一个完整的digest循环就结束了。这时，浏览器会重新渲染DOM来体现model的改变，这里的watcher函数就view上的指令（如ngBind、ngShow、ngHide等）或{ { } }表达式（严格来说时$compile服务）所注册的，指令在ng的compile阶段会被逐一解析、注册。

#### 浏览器事件循环和ng的MVW
在浏览器中js时靠事件循环工作的，浏览器中存在一个事件循环池，无限循环以保持执行工程的可用，等待事件（如layout、paint、鼠标点击、键盘输入等）并执行他们。程序员的代码则先通过注册事件回调函数来响应这类事件，然后等待js引擎来执行回调函数。在回调函数中一般操作DOM或改变样式，一旦回调函数执行完成，浏览器就会执行渲染更新界面。

如下时js在浏览器中的事件循环模型：
![js event loop](1.png)
来自浏览器本身或键盘等设备的事件会被浏览器放入事件队列中，然后一次被单线程的Event Loop(事件循环)分配给对应的回调函数，最后浏览器更新DOM状态。

NodeJS也是基于事件循环的，所有的I/O操作，如HTTP请求，数据查询，磁盘文件I/O操作，都会异步执行，然后等被注册的事件回调函数在主线程中处理。

ng扩展了浏览器的事件模型，创建了一个独特的执行环境。ng在View上声明的事件指令，如ngClick、ngChange等，会将浏览器的事件转发给$scope这个ViewModel的响应函数，等待响应函数中改变Model，然后触发“脏检查”刷新View。

$rootScope对象时ng中所有$scope对象的祖先，所有的$scope都是直接或间接利用$rootScope提供的$new方法创建的，他们都是从$rootScope中继承了$new、$watch、$watchGroup、$watchCollection、$digest、$destroy、$eval、$evalAsync、$apply、$on、$emit、$broadcast等方法，并且有$id、$parent这两个属性。

#### ng中的$watch函数
在ng中，大部分指令都依赖watcher函数来监听Model的变化，以更新View，它是“脏检查”的核心之一。下面是$watch函数的源码：
```js
$watch: function(watchExp, listener, objectEquality){
  var scope = this,
      get = compileToFn(watchExp, 'watch'),
      array = scope.$$watchers,
      watcher = {
        fn: listener, // 监听函数
        last: initWatchVal, // 上次的值
        get: get, // 获取监听表达式的值
        exp: watchExp, // 监听表达式
        eq: !!objectEquality //是否需要深度对比
      };

    lastDirtyWatch = null;
    ...
    if(!array){
      array = scope.$$watchers = [];
    }

    array.unshift(watcher);

    return function deregisterWatch(){
      arrayRemove(array, watcher);
      lastDirtyWatch = null;
    };
}
```
上面代码中每一个watcher对象都包括：监听函数fn、上次变化的值last(最初为初始值)、获取监听表达式等... 所谓的深度对比，是指使用angular.equals()函数进行对比。

watch表达式很灵活，可以是函数、$scope上的一个属性名、字符串形式的表达式。$scope上的属性名或表达式、最终都会被$parse服务解析为用于响应的获取属性值的函数。
所有的watcher函数都会被unshift函数插入scope.$$watchers数组的头部，以便后面的$digest使用。
最后，$watch函数会返回一个反注册函数，其用于移除注册的watcher。

ng默认不适用angular.equals()函数进行深度比较是因为使用 === 的方式更快，但由于===对数组或object进行比较时检查的时引用，所以即使内容完全一样的两个表达式也会判定为不同。此时，若需要进行深度比较，需要将第三个参数设置为true.

ng还提供了$watchGroup、$watchCollection方法来监听数组或一组属性。

#### ng中的$digest函数
对浏览器的事件循环，ng到底做了那些扩展？
当接收view上的神机箭指令所转发的事件时，就会切换到ng的环境来响应事件，此时$digest循环就会触发。
$digest循环实际上包括两个while循环，分别处理$evalAsync的异步运算队列、处理$watch的watchers队列。

当$digest循环发生时，它会便利当前$scope及其所有子$scope上注册的所有watchers函数，所谓的“脏检查”就是遍历所有的watcher函数，遍历一遍称为一轮脏检查。每执行完一轮检查时，若任何一个watcher所监听的指改变过，那么会接着执行一轮，直到所有的watcher函数都稳定不再改变。

从第一轮检查直到结果稳定，这个过程就是完整的$digest循环，当$digest循环结束时，ng将模型最后的变化更新到DOM中，这样是为了合并多个更新，防止频繁DOM操作。但若直到10轮检查都没有稳定，则会抛出异常防止无限循环检查下去。

那么什么时候触发“脏检查”就变得很重要了。 每一个进入ng环境的事件都会执行一次$digest循环，对于ngModel监听的表单交互控件来说，每输入一个字符，就会触发一次循环来检查watcher函数，以便及时更新view， 在angular1.3之后，可以使用ngModelOptions对触发方式进行配置。

ngClick、ngSubmit、ngChange等事件指令，$http、$resource这类外部ajax数据获取的回调函数、以及$timeout、$interval都会直接或间接调用$scope.$digest函数。

$digest源码 *多看几遍就看懂了:)*：
```js
$digest: function(){
  var watch, value, last,
      watchers,
      asyncQueue = this.$$asyncQueue,
      postDigestQueue = this.$$postDigestQueue,
      length,
      dirty, ttl = TTL, // TTL默认为10，循环最大轮数
      next, current, target = this,
      watchLog = [],
      logIdx, logMsg, asyncTask;

  beginPhase('$digest'); // 设置$$phase状态为$digest中
  $browser.$$checkUrlChange();

  lastDirtyWatch = null;

  do{ // "脏检查"循环开始
    dirty = false;
    current = target;

    // 先执行由$scope.$evalAsync注册的异步对象
    while(asyncQueue.length){
      try{
        asyncTask = asyncQueue.shift();
        asyncTask.scope.$eval(asyncTask.expression);
      }catch(e){
        clearPhase();
        $excpetionHandler(e);
      }
      lastDirtyWatch = null;
    }

    traverseScopeLoop:
      do{ // 对当前$scope及其子$scope循环
        if((watchers = current.$$watchers)){
          // 对当前$scope的watcher函数询问
          length = watchers.length;

          while(length--){
            try{
              watch = watchers[length];
              // 首先会使用js的 === 比较，因为比较快，再视情况使用angular.equals比较
              // 对number类型比较应排除NaN
              if(watch){
                if(
                  (value = watch.get(current)) !== (last = watch.last) &&
                  !(watch.eq?equals(value, last):(typeof value === 'number' && typeof last === 'number' && isNaN(value) && isNaN(last)))
                ){
                  dirty = true;
                  lastDirtyWatch = watch;
                  watch.last = watch.eq?copy(value, null):value;
                  // 执行watcher的监听函数，参数为：新值、旧值、当前$scope
                  watch.fn(value, ((last === initWatchVal)?value:last), current);
                  if(ttl < 5){
                    // ... log message
                  }
                }else if(watch === lastDirtyWatch){
                  dirty = false;
                  break traverseScopeLoop;
                }
              }
            }catch(e){
              clearPahse(); // 去除$$phase的$digest状态
              $exceptionHandler(e);
            }
          }
        }

        // 对后代$scope循环，所有的watcher函数都会检查
        if(
          !(next = (current.$$childHead || (current !== target && current.$$nextSibling)))
          ){
            while(current !== target && !(next = current.$$nextSibling)){
              current = current.$parent;
            }
        }
      }while((current = next)); // 进入下一个子$scope $digest

    if((dirty || asyncQueue.length) && !(ttl--)){
      // 若超过默认10次digest循环，抛出异常，终止循环、
      clearPhase();
      throw $rootScopeMinErr('infdig', '{0} $digest() iterations reached. Aborting!\n' + 'Watchers fired in the last 5 iterations: {1}', TTL, toJson(watchLog));
    }
  }while(dirty || asyncQueue.length);

  clearPhase(); // 去除$$phase的$digest状态

  while(postDigestQueue.length){
    try{
      postDigestQueue.shift()();
    }catch(e){
      $exceptionHandler(e);
    }
  }
}
```

#### ng中的$apply
$digest是一个内部函数，正常的应用是不应该直接调用它的，而应该调用scope.$apply函数，它是触发ng“脏检查”的公开接口。
```js
$apply: function(expr){
  try{
    beginPhase('$apply');
    return this.$eval(expr);
  }catch(e){
    $exceptionHandler(e);
  }finally{
    clearPhase();
    try{
      $rootScope.$digest();
    }catch(e){
      $exceptionHandler(e);
      throw e;
    }
  }
}
```
首先设置$$phase为$apply阶段，并利用$scope.$eval方法来执行计算传入的ng表达式，更新Model或ViewModel的值，然而不管执行是成功还是失败，都会进入ng的$digest方法中。
但ng只能管理自己的行为而无法管理第三方插件，不能自动更新视图，所以需要手动调用$scope.$apply。
