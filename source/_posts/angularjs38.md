---
title: angularjs巩固实践-38-移除不必要的$watch
categories:
  - angularjs
tags:
  - angularjs
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