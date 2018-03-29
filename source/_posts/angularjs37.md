---
title: AngularJS巩固实践-37-使用Controller as Vm方式
categories:
  - AngularJS
tags:
  - AngularJS
  - JavaScript
  - AngularJS深度剖析
date: 2017-08-14 22:12:44
updated:
---

ng从1.2开始引入语法`Contorller as Vm`在之前的版本需要在Controller中注入$scope服务才能在视图绑定中使用这些变量。

比如：
```js
angular.module('com.ngnice.app').controller('DemoController', function($scope){
  $scope.title = 'Angular';
});

// 使用如下：
<div ng-app="com.ngnice.app" ng-controller="DemoController">
  hello: {{ title }}
</div>
```
在controller中需要显示注入$scope, 对controller来说，$scope几乎是必须使用的对象，所以几乎每次都加入这个参数，这显得有些多余和累赘，所以ng引入了新的语法糖：controller as，上述代码可改为：
```js
angular.module('com.ngnice.app').controller('DemoController', function(){
  this.title = 'Angular';
});

// 使用如下：
<div ng-app="com.ngnice.app" ng-controller="DemoController as demo">
  hello: {{ demo.title }}
</div>
```
此处，controller中没有注入$scope服务，所以controller更像是一个普通的js函数对象，而新的语法在使用时为controller引入了一个别名，方便在ng-controller的DOM区内的视图模板通过别名来访问数据对象。

源码如下：
```js
controllerInstance = $controller(controller, locals);
...
if(directive.controllerAs){
  locals.$scope[directive.controllerAs] = controllerInstance;
}
```
ng将controller对象实例以其as为别名放在$scope上，所以视图模板能够访问。

对比controller as语法，更清晰也更好的方法为：
```js
angular.module('com.ngnice.app').controller('DemoController', function(){
  var vm = this;
  vm.title = 'Angular';
  return vm;
})
```
这样做的好处时，避免js中this指针的一些问题。也可以在controller中注入$scope同时声明一个内部变量
```js
var vm = $scope.vm = {};
```

原则上在controller中应该避免使用$watch、$emit、$on等$scope提供的特殊的方法。但当一定要使用这些方法时，就需要使用注入$scope的方式。

同时因controller实例将会成为$scope服务的一个属性，所以视图模板上所有的字段都会现在在一个别名引用属性上，这样可以避开js原型链继承对普通值类型的覆盖问题。

然后由于没有注入$scope, controller也更接近普通js对象，所以后期可以利用coffeescript等提供的class语法来实现，看起来更好。

最后多重controller嵌套的情况下，由于每个字段都会用别名，所以能避免嵌套继承的命名冲突问题。

路由中使用controller as语法
一般ng应用都时单页面应用，需要用到ng-route或ui-route来知道controller，他们也支持这种as语法：

以ng-route为例：
```js
angular.module('com.ngnice.app').config(function($routeProvider){
  $routeProvider.when('/Book/:bookId', {
    templateUrl: 'book.html',
    controller: BookController,
    controllerAs: 'book'
  });
})
```

指令中也可以使用controller as语法：
```js
angular.module('com.ngnice.app').controller('DemoController', function(){
  var vm = this;
  vm.title = 'Angular';
  return vm;
}).directive('hello',function(){
  return {
    restrict: 'EA',
    controllerAs: 'vm',
    template: '<div>{{vm.title}}</div>',
    controller: 'DemoController'
  };
});
```
但上述方式在通过scope:{}声明的变量仍然会自动绑定到$scope而不是vm上，所以需要开启bindToController选项：
```js
angular.module('com.ngnice.app').controller('DemoController', function(){
  var vm = this;
  vm.title = 'Angular';
  return vm;
}).directive('hello',function(){
  return {
    restrict: 'EA',
    scope: {
      name: =
    }
    controllerAs: 'vm',
    bindToController: true,
    template: '<div>{{vm.title}}</div>',
    controller: 'DemoController'
  };
});
```
