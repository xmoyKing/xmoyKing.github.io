---
title: AngularJS巩固实践-38-移除不必要的$watch
categories:
  - AngularJS
tags:
  - AngularJS
  - $watch
date: 2017-08-17 22:59:21
updated:
---

双向绑定是ng的核心概念之一，它带了思维方式的转变：不再是DOM驱动，而是以Model
为核心，在View中写上声明式标签，然后ng就在会自动同步View的变化到Model，并将Model变化更新到View。

双向绑定带来了巨大好处和方便，但它需要在后台常驻一个监听的“眼睛”，随时观察所有绑定值的改变，这就是ng1.x中的“性能杀手”——“脏检查机制”（$digest）。 可以想象，若有非常多的“眼睛”时，一定会产生性能问题，在讨论如何优化ng的性能前，需要先理解双向绑定和watchers函数。

#### 双向绑定和watchers函数
为了实现双向绑定，ng使用了$watch API来监控$scope上的Model改变。ng应用在编译模板时，会手机模板上的声明式标签——指令或绑定表达式，并链接（link)他们，在这个过程中，指令或绑定表达式会注册自己的监控函数，这就是watchers函数。
以常用的`{ {} }`表达式为例：

HTML：
```html
<body ng-app="com.ngnice.app" ng-controller="DemoController as demo">
  <div> hello: {{demo.count}} </div>
  <button type="button" ng-click="demo.increase();">increase++</button>
</body>
```

JS：
```js
angular.module('com.ngnice.app').controller('DemoController', function(){
  var vm = this;
  vm.count = 0;
  vm.increase = function(){
    vm.count++;
  };
  return vm;
})
```
这是一个自增长计数器的例子，ng表达式`{ {} }`会在其所在的$scope（本例为DemoController）中注册watchers函数，监控count属性的变化以便能及时更新View。

每次点击button的时候，count计数器就加1，然后count的辩护会通过ng的$digest过程同步到View上，这是从Model到View的更新，是一个单向过程。

若处理一个带ngModel指令的input控件，则在View上的每次输入都会更新到Model上，此时是反向的更新，从View到Model。

Model数据能被更新到View是因为背后默默工作的$digest循环（脏检查）被触发了。它会执行当前scope以及其所有子scope上注册的watchers函数，检查是否发生变化，变化则执行相应的处理函数，直至Model稳定。结束$digest循环后，浏览器会重新渲染改变Model数据后对应的视图。

ng表达式`{ {} }`实现源码：
```js
function collectDirectives(node, directives, attrs, maxPriority, ignoreDirective){
  var nodeType = node,nodeType,
      attrsMap = attrs.$attr,
      match,
      className;

  switch(nodeType){
    case 1: // 元素
      // ...
      break;
    case 3:  // 文本
      addTextInterpolateDirective(directives, node.nodeValue);
      break;
    case 8: // 注释
      // ...
      break;
  }

  directives.sort(byPriority);
  return directives;
}

function addTextInterpolateDirective(directives, text){
  var interpolateFn = $interpolate(text, true);
  if(interpolateFn){
    directives.push({
      priority: 0,
      compile: function textInterpolateCompileFn(templateNode){
        // 当引用的模板的根元素上有绑定的对象时，因为没有父元素，所以需要在linkFn中做如下操作
        var parent = templateNode.parent(),
            hasCompileParent = parent.length;
        if(hasCompileParent)
          safeAddClass(templateNode.parent(), 'ng-binding');

        return function textInterpolateLinkFn(scope, node){
          var parent = node.parent(),
              bindings = parent.data('$binding') || [];

            bindings.push(interpolateFn);
            parent.data('$binding', bindings);
            if(!hasCompileParent)
              safeAddClass(parent, 'ng-binding');

            scope.$watch(interpolateFn, function interpolateFnWatchAction(value){
              node[0].nodeValue = value;
            });
        };
      };
    });
  }
}
```

ng会在compile阶段手机View模板上的所有Directive，ng表达式会被解析成一种特殊的指令，addTextInterpolateDirective。 到了link阶段，就会利用scope.$watch的API注册在上面提到的watchers函数：它的求值函数为$interpolate对绑定表达式进行编译的结果，监听函数则时用新的表达式计算值去修改DOM Node的nodeValue。 可见，在View中的ng表达式，也会成为ng在$digest循环中watchers的一员。

在上面的代码中，还有一部分时给调试器用的，它会在ng表达式所属的DOM节点加上名为ng-binding的调试类，类似的调试类还有ng-scope，ng-isolate-scope等。在ng1.3中可以使用compileProvider来关闭这些调试信息。
```js
app.config(function($compileProvider){
  // disable debug info
  $compileProvider.debugInfoEnable(false);
});
```

#### 其他指令中的watchers函数
不仅ng的表达式会使用$scope.$watch API添加watchers，ng内置的大部分指令也一样。
- ngBind:
  它和ng表达式很像，都是绑定特定表达式的值到DOM的内容，并保持与scope同步，不同之处在于它需要一个HTML节点并以attribute属性的方式标记，简单来说，除开一些细微的区别(防止ng表达式闪烁的问题)，ng表达式算是ngBind的特定语法糖。
```js
var ngBindDirective = ngDirective({
  compile: function(templateElement){
    templateElement.addClass('ng-binding');
    return function(scope, element, attr){
      element.data('$binding', attr.ngBind);
      scope.$watch(attr.ngBind, function ngBindWatchAction(value){
        // 故意使用 == 而不是 ===，因为需要捕获当值为null或undefined的时候
        element.text(value == undefined ? '' : value);
      });
    };
  }
});
```
  $scope.$watch的注册代码：watchers函数为ngBind attribute的值，处理函数则是用表达式计算的结果去更新DOM的文本内容。

- ngShow / ngHide:
  根据表达式的计算结果来控制显示/隐藏DOM节点的指令。
```js
var ngShowDirective = ['$animate', function($animate){
  return function(scope, element, attr){
    scope.$watch(attr.ngShow, function ngShowWatchAction(value){
      $animate[toBollean(value) ? 'removeClass' : 'addClass'](element, 'ng-hide');
    });
  };
}];


var ngHideDirective = ['$animate', function($animate){
  return function(scope, element, attr){
    scope.$watch(attr.ngHide, function ngHideWatchAction(value){
      $animate[toBollean(value) ? 'addClass' : 'removeClass'](element, 'ng-hide');
    });
  };
}];
```
若有太多watcher函数，例如超过2000个，那么每次$digest循环时，肯定比较慢，这是脏检查的性能瓶颈。解决的方案是：减少$watch,移除不必要的$watch.

#### 慎用$watch和及时销毁
想要提高ng的性能，那么在开发时就应该尽量减少显示使用$scope.$watch。ng内置的很多指令能满足大部分的业务需求，特别是能够复用ng内置的UI事件指令（ngChange,ngClick）时，就不要添加额外$watch。

对于不再使用的$watch函数，尽早释放，$scope.$watch函数的返回值就是用于释放watcher的函数，如下例（实现单次绑定）：
```js
angular.module('com.ngnice.app').controller('DemoController', function($scope){
  var vm = this;
  vm.count = 0;
  var textWatch = $scope.$watch('demo.updated', function(newVal, oldVal){
    if(newVal !== oldVal){
      vm.count++;
      textWatch();
    }
  });
  return vm;
});
```

#### one-time 绑定
在开发中，常有很多静态数据构成的页面，如静态商品、订单的显示，他们后绑定了数据后，在当前的Model就不再改变了。比如，需要一个会议例程的展示界面，常规的ng方式是使用ng-repeat来渲染列表：

HTML:
```html
<ul>
  <li ng-repeat="session in sessions">
    <div class="info">
      {{session.name}} - {{session.room}} - {{session.hour}} - {{session.speaker}}
    </div>
    <div class="likes">
      {{session.likes}} likes!
      <button ng-click="likeSession(session)">Like it!</button>
    </div>
  </li>
</ul>
```

JS:
```js
angular.module('com.ngnice.app').controller('MainController', function($scope){
  $scope.sessions = [/*...*/];
  $scope.likeSession = function(session){
    // ...
  }
});
```
普通的实现非常简单，但若sessions非常多，比如300个，那么会产生多少个$watch? 上例中每一个session有5个绑定，额外的ng-repeat一个，将会产生1501个$watch,。问题就在于每次用户点击button，ng就会去检查name，room等5个属性是不是被改变了。

而除了button之外，所有的数据都是静态数据，那么既然某些数据Model不会被改变，是否可以让ng不对这些数据进行脏检查呢？但$watch在第一次确实必要的，因为初始化时需要用静态信息填充DOM，所以若能换为单次绑定（one-time）则再好不过了。

ng中，单次绑定的定义是：**单词表达式在第一次$digest完成后，将不再计算（监测属性的变化）**
ng1.3为ng表达式引入了新语法，以“::”作为前缀的表达式为one-time绑定：
```html
<ul>
  <li ng-repeat="session in sessions">
    <div class="info">
      {{::session.name}} - {{::session.room}} - {{::session.hour}} - {{::session.speaker}}
    </div>
    <div class="likes">
      {{session.likes}} likes!
      <button ng-click="likeSession(session)">Like it!</button>
    </div>
  </li>
</ul>
```
若在1.3之前的版本想要实现one-time绑定该如何实现呢？ 有牛人已经实现了：[Bindonce](https://github.com/Pasvaz/bindonce)
```js
<ul>
  <li ng-repeat="session in sessions">
    <div class="info">
      <span bo-text="session.name"></span> -
      <span bo-text="session.room"></span> -
      <span bo-text="session.hour"></span> -
      <span bo-text="session.speaker"></span>
    </div>
    <div class="likes">
      {{session.likes}} likes!
      <button ng-click="likeSession(session)">Like it!</button>
    </div>
  </li>
</ul>
```

需要引入bindonce库，并依赖模块，JS:
```js
angular.module('com.ngnice.app',['pasvaz.bindonce']);
```

#### 滚屏加载
另外一种性能解决方案是滚屏加载（Endless Scrolling / unpagination）,用于大量数据显示时，又不分页，一般是当滚屏到底部时加载新数据到页面底部。开源组件[ngInfiniteScroll](https://sroze.github.io/ngInfiniteScroll/)的 [Demo](https://sroze.github.io/ngInfiniteScroll/demo_async.html)：

HTML:
```html
<div ng-app='myApp' ng-controller='DemoController'>
  <div infinite-scroll='reddit.nextPage()' infinite-scroll-disabled='reddit.busy' infinite-scroll-distance='1'>
    <div ng-repeat='item in reddit.items'>
      <span class='score'>{{item.score}}</span>
      <span class='title'>
        <a ng-href='{{item.url}}' target='_blank'>{{item.title}}</a>
      </span>
      <small>by {{item.author}} -
        <a ng-href='http://reddit.com{{item.permalink}}' target='_blank'>{{item.num_comments}} comments</a>
      </small>
      <div style='clear: both;'></div>
    </div>
    <div ng-show='reddit.busy'>Loading data...</div>
  </div>
</div>
```

JS:
```js
var myApp = angular.module('myApp', ['infinite-scroll']);

myApp.controller('DemoController', function($scope, Reddit) {
  $scope.reddit = new Reddit();
});

// Reddit constructor function to encapsulate HTTP and pagination logic
myApp.factory('Reddit', function($http) {
  var Reddit = function() {
    this.items = [];
    this.busy = false;
    this.after = '';
  };

  Reddit.prototype.nextPage = function() {
    if (this.busy) return;
    this.busy = true;

    var url = "https://api.reddit.com/hot?after=" + this.after + "&jsonp=JSON_CALLBACK";
    $http.jsonp(url).success(function(data) {
      var items = data.data.children;
      for (var i = 0; i < items.length; i++) {
        this.items.push(items[i].data);
      }
      this.after = "t3_" + this.items[this.items.length - 1].id;
      this.busy = false;
    }.bind(this));
  };

  return Reddit;
});
```

#### 其他
解决性能问题的方案还有很多，将其他更高效的第三方非ng组件封装为ng组件，需要注意scope和model的同步，以及合理的触发$apply更新view，比如通过[ngReact](https://github.com/ngReact/ngReact)将React组件应用到ng中。

重要提醒：**其实ng的脏检查机制并不慢**，ng为此专门做了很多优化，在大多数情况下，ng的watcher机制比很多模版引擎更快，因为ng不需要通过大范围的DOM操作来更新View，它每次更新的区域很小，DOM操作更少，而DOM操作的代价远远高于JS运算，在有些浏览器中，修改DOM的速度甚至比JS运算速度慢1000倍。

同时，随着ES的新标准Object.obserse的使用，ng2.0改用它来代替“脏检查”，运行性能显著提高，尤其是针对Mobile开发的ionic这类框架，非常有利。