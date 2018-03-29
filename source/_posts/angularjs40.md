---
title: AngularJS巩固实践-40-$timeout使用技巧
categories:
  - AngularJS
tags:
  - AngularJS
  - JavaScript
  - $timeout
date: 2017-08-22 22:18:00
updated:
---

在前端开发中，经常处理一些延时任务，比如，为了防止界面停止响应，将一些费时任务延后（js单线程执行，所以需要合理安排任务执行顺序），或是要等一些DOM元素出现后才能继续，这时，通常使用window.setTimeout来专门处理这类延时任务。

在ng应用中也可以使用setTimeout，但由于ng的脏检查机制，在延时任务中修改被绑定到界面中的变量时，window.setTimeout是不会触发脏检查来更新UI界面的，所以此时就需要使用$scope.$apply来手动触发脏检查。

但有时会遇到ng报错：`Error: $digest already in progress`，即ng内部已经正进行脏检查了，此时可以先检查ng内部是不是正在做脏检查，使用如下代码解决：
```js
function safeApply(scope, fn){
  (scope.$$phase || scope.$root.$$phase) ? fn() : scope.$apply(fn);
}
```

上述代码似乎已经完美解决问题了，但其实可以直接使用ng提供的$timeout，自带$apply效果。

#### $timeout源码分析
ng内置$timeout服务是ng包装原生的window.setTimeout而实现的。
```js
function $TimeoutProvider(){
  this.$get = ['$rootScope', '$browser', '$q', '$excpetionHandler',
  function($rootScope, $browser, $q, $excpetionHandler){
    var deferreds = {};

    function timeout(fn, delay, invokeApply){
      var deferred = $q.defer(),
          promise = deferred.promise,
          skipApply = (isDefined(invokeApply) && !invokeApply),
          timeoutId;

      timeoutId = $browser.defer(function(){
        try {
          deferred.resolve(fn());
        }catch(e){
          deferred.reject(e);
          $exceptionHandler(e);
        }finally{
          delete deferreds[promise.$$timeoutId];
        }

        if(!skipApply)
          $rootScope.$apply();
      }, delay);

      promise.$$timeoutId = timeoutId;
      deferreds[timeoutId] = deferred;

      return promise;
    }

    timeout.cancel = function(promise){
      if(promise && promise.$$timeoutId in deferreds){
        deferreds[promise.$$timeoutId].reject('canceled');
        delete deferreds[promise.$$timeoutId];
        return $browser.defer.cancel(promise.$$timeoutId);
      }
      return false;
    }

    return timeout;
  }];
}

function Browser(window, document, $log, $sniffer){
  var self = this;
      // ...
  self.defer = function(fn, delay){
    var timeoutId;
    outstandingRequestCount++;
    timeoutId = setTimeout(function(){
      delete pendingDeferIds[timeoutId];
      completeOutstandingRequest(fn);
    }, delay || 0);
    pendingDeferIds[timeoutId] = true;
    return timeoutId;
  };
  slef.defer.cancel = function(deferId){
    if(pendingDeferIds[deferId]){
      delete pendingDeferIds[deferId];
      clearTimeout(deferId);
      completeOutstandingRequest(noop);
      return true;
    }
    return false;
  };
}
```
ng在$browser中封装了defer和defer.cancel方法，他们分别封装了window.setTimeout和取消window.setTimeout的任务，之所以封装是为了针对不同浏览器的粘合。

$timeout服务利用$browser中分组了defer和defer.cancel，再次将window.setTimeout封装为Promise的方法，而且可以使用.then方法注册接受延时回调的返回值，并且可以用$timeout.cancel(promise)取消这次延时任务。

在$timeout中，接受延时任务的回调函数、延时间隔时间（毫秒）、以及是否需要调用$apply的标记参数。对于延时间隔为0，表示在当前任务完成，线程空闲后立即执行。apply的标记参数默认为true，需要调用$apply机制，此处启动脏检查会在当前任务完成后，线程空闲才执行，所以不会出现前面的`Error: $digest already in progress`的问题

$timeout是一个便于进行单元测试的服务组件，在ngMock中会为$timeout添加一个flush方法：将放在队列中的延时任务全部立即执行以便，这样就将异步延时任务变为同步，以便在ng的单元测试中更好的测试应用的业务组件。

angular-mock中angualr.mock.$Browser方法中关于defer的定义：
```js
angular.mock.$Browser = function(){
  var self = this;
  // ...
  this.isMock = true;

  slef.defer = function(fn, delay){
    delay = delay || 0;
    self.deferredFns.push({
      time: (self.defer.now + delay),
      fn: fn,
      id: self.deferredNextId
    });
    self.deferredFns.sort(function(a, b){
      return a.time - b.time;
    });
    return self.deferredNextId++;
  };

  self.defer.now = 0;
  self.defer.cancel = function(deferId){
    var fnIndex;
    angular.forEach(self.deferredFns, function(fn, index){
      if(fn.id === deferId) fnIndex = index;
    });

    if(fnIndex |== undefined){
      self.deferredFns.splice(fnIndex, 1);
      return true;
    }
    return false;
  };

  self.defer.flush = function(delay){
    if(angular.isDefined(delay)){
      slef.defer.now += delay;
    }else{
      if(self.deferredFns.length){
        self.defer.now = self.deferredFns(self.deferredFns.length - 1).time;
      }else{
        throw new Error('No deferred tasks to be flushed');
      }
    }

    while(self.deferredFns.length && self.deferredFns[0].time <= self.defer.now){
      self.deferredFns.shift().fn();
    }
  };
}
```

$timeout不仅可用于延时任务，而且对第三方的js组件（比如jquery)封装很有用。

在需要手动scope.$apply的情况下，都可以利用$timeout或者$scope.$evalAsync的延时和默认$apply机制巧妙解决问题。

对于定时器window.setInterval，ng内置了$interval服务。在使用$interval服务之前，确定是否可以用HTML5 WebSocket代替。