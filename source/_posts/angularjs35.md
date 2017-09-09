---
title: angularjs巩固实践-35-指令生命周期
categories:
  - fe
tags:
  - fe
  - angularjs
date: 2017-08-09 20:36:50
updated:
---

指令是ng提出的一个概念，为HTML提供了DSL（特定领域语言）的扩展语法，并为组件化（Web Component)提供了帮助。

指令有自己的生命周期，一个指令从解析到生效，会经历inject、compile、controller加载、pre-link、post-link这几个主要阶段。以如下指令为例：
```js
angular.module('com.ngnice.app').directive('directiveLife', function($log){
  $log.info('injecting function directiveLife');

  return {
    restrict: 'EA',
    transclude: true,
    replace: true,
    template: '<div><h2>count: {{count}} </h2><p ng-transclude></p></div>',
    scope: {
      count: '=',
    },
    compile: function(elm, iAttrs){
      $log.info('compile', 'count value from attribute: ' + iAttrs.count);

      return {
        pre: function(scope, elm, iAttrs){
          $log.info('pre-link', 'count value from attribute: '+ iAttrs.count, 'count value from scope: '+scope.count);
        },
        post: function(scope, elm, iAttrs){
          $log.info('post-link', 'count value from attribute: '+ iAttrs.count, 'count value from scope: '+scope.count);
        },
      };
    },
    controller: function($scope){
      $log.info('controller', 'count value from controller: '+ $scope.count);
    }
  };
});
angular.module('com.ngnice.app').controller('DemoController', function(){
  var vm = this;
  return vm;
});
```
上述代码创建了名为directiveLife的指令，用于展示指令的执行顺序，它有一个count属性，当页面中复用这个指令时可用于区分各自指令的执行过程。
```html
<body ng-controller="DemoController as demo">
  <div id="directiveLife">
    <directive-life count="1"></directive-life>
  </div>
</body>
```
然后可从控制台看到如下日志信息：
```
injecting function directiveLife
compile count value from attribute: 1
controller count value from controller: 1
pre-link count value from attribute: 1 count value from scope: 1
post-link count value from attribute: 1 count value from scope: 1
```
上述输出展示了指令执行时的顺序，每一个阶段都负责完成不同的功能。

#### injecting阶段
在ng第一次使用该指令时，会先调用注入函数来获取它依赖的服务，此过程仅发生在首次解析该指令时，即多此使用同一个指令只注入一次所依赖的服务。
在injecting阶段，因为在一个闭包中，所以所有directiveLife指令共享同一个作用域，所以此阶段设置的Directive的配置信息会被所有后续调用的指令共享，有点类似默认配置，但不建议在此配置，因为有更好的方法专门用于配置使用：
1. ng中所有的Service时全局共享的，所以可将这类配置信息抽取到一个Constant中，然后在指令中注入该Constant
2. 在config阶段配置默认信息