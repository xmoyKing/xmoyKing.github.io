---
title: AngularJS巩固实践-33-依赖注入
categories:
  - AngularJS
tags:
  - AngularJS
  - 依赖注入
date: 2017-08-04 17:25:26
updated:
---

依赖注入（Dependency Injection）简称DI, 简单理解一下什么是DI。

当写程序时需要某个对象（完成某种任务），有如下几种方式来获取这个对象：
1. 每次都手动创建
  这个是最简单直接的方式，但由于对象创建可能很复杂，比如需要很多参数初始化，甚至它依赖很多其他对象，所以这种自己创建的方式可能并不是非常好，无法适应复杂的对象，同时随着对象的复杂化，难度成倍提升。
  总之，自己动手丰衣足食是一个很难实现的理想。
2. 从全局中查找并获取
  这种方式也很容易想到，不用每次都创建，而是最开始创建一个全局对象，然后用到时到全局对象中查找即可，`obj = globalRegisterObj.get(objId)`这种方式不用管它的初始化，拿来就用，非常nice。
  但，仍然是有问题的，比如它很难被单元测试，全局变量是单元测试中的魔鬼，因为它让各个“单元”相互耦合。
3. DI —— 衣来伸手饭来张口
  所谓的DI, 就是直接指定所需对象，然后在使用时即有人将这些对象给出。这个“人”可能就是“框架（Framework）”，也可能是“测试容器（Test Runner）”，使用者不需要关心这个“人”是谁，也不需要关心这个“人”是如何将所需对象给出的。这个“人”的专业叫法为“容器”。
  “指定所需对象”的方式也有多种，比如直接声明一个属性，或者写一个注解（annotation），或者用配置文件声明依赖关系，或者在函数参数中声明。ng所采用的方式是函数参数，以及特殊的annotation来防止代码压缩破坏参数名（在使用js代码压缩工具时，一般会将函数参数名替换掉，但是这会破坏ng的DI声明）。

#### 如何用js实现DI
在js中实现DI，看似很难，其他原理很简单，关键是函数对象的toString()方法。在js中，对一个函数对象执行toString(),返回值是函数的源码，拿到源码后就可以对函数的声明进行解析了。
伪代码如下：
```js
// giveMe函数声明了一个叫config的参数，希望容器根据这个名字找到同名对象，并且注入
var giveMe = function(config){
  // 经过注入后，此处config的内容为{delay: 1}
  // 跟registry中保存的是同一个实例
};

// 全局注册表对象，这里保存了可注入的对象，包括一个名为config的对象
var registry = {
  config : {
    delay : 1
  }
};

// 注入函数，此处用来演示注入容器的行为
// thisForFunc 用于在需要时，调用者可以额外指定一个this，以避免this错误的问题
var inject = function(func, thisForFunc){
  // 获取func的源码，这样能知道func需要什么参数
  var sourceCode = func.toString();
  // 用正则表达式解析源码
  var matcher =  sourceCode.match(/* 正则表达式较复杂省略 */);
  // 从matcher中解析出各个参数的名称、解析过程省略
  var objectIds = ...

  // 准备调用func时用的参数表
  var objects = [];
  for(var i = 0; i < objectIds.length; ++i){
    var objectName = objectIds[i];
    // 根据对象名称查出相应的对象
    var object = registry[objectName];
    // 放到数组中准备作为参数传递过去
    objects.push(object);
  }
  // 调用apply同调func函数，并将参数传过去
  func.apply(thisForFunc || func, objects);
}
```
使用时调用 `inject(giveMe)` 或 `inject(giveMe, anotherThis)` 即可.
实际上，DI需要考虑很多问题，但是基本原理是这样。

#### ng中的DI
ng中,主要的一些编程元素都需要通过某种方式注册进去，比如`myModule.service('serviceName', function()..)`,实际就是把后面的函数加入容器中，并且命名为serviceName，以供后续使用。

ng的实现中使用了延迟初始化，即，只有当对象被用到时，才会被创建，否则不会创建，这种延迟初始化提高了启动速度。

问题，ng的容器是什么？
与上面的伪代码不同，ng中不存在真正的全局对象，所以可以放心的在页面中添加多个ng-app而不用担心他们互相干扰。但容器又需要一个公用的地方来存放这些“名字和对象”的注册表（Registry), 在ng中，这个注册表就是module，所以一个app可以使用很多不同名字的module，他们之间可以存在依赖关系。`angular.module('someModule',['dep1', 'dep2'])`, 这种划分module有利于程序的文件组织和复用。

根据DI的原理，可以发现，所有被注入的对象都是单例对象，只创建一次然后多次复用，因此若需要在ng中跨Controller共享数据或相互通信，则创建一个Service/Value/Constant，然后将他们分别注入到多个Controller中，这写Controller就自然共享同一个对象了。

另外，DI的实现需要容器进行处理，所以，ng中只有某几种函数可以使用依赖注入，分别是：controller、service/factory/provider、directive、filter、animation、config、run、decorator。简单的说，通过module注册进来的函数都可以使用，因为module负责管理这些注入的服务。其中provider比较特殊，在它的声明和$get函数中都是可以注入的，但注入的内容有限制，如：
```js
angular.module('com.ngnice.app').provider('test', function(/* 此处只能注入constant和已定义的provider，不能注入服务 */){
  this.$get = function(/* 此处可以注入服务，就像在controller函数中一样 */){
    ...
  };
});
```

看似DI的使用受限，但由于js作用域特殊，在外层函数中定义的变量可被内层函数使用，而几乎所有的ng代码都被包含在上述的几个函数中，所以通常情况下，只要注入一次就可以到处使用。

但，当出现循环依赖时就不能使用依赖注入了，必须使用手动注入的方式解决循环依赖问题，即通过$injector在代码执行时获取指定名称的服务，比如当$http和interceptor服务之间出现了循环依赖，解决方法为：`$injector.get('$http')`。

#### DI与minify
大多数情况下，在项目发布时都需要对代码进行压缩（minify），即减少js文件大小，同时能起到一些混淆加密的作用。简单的说就是将参数以及部分变量、函数名进行重命名，这种方法方式一般的项目能正常工作，但ng项目例外。

由于ng的DI机制是根据参数名进行注入的，所以对参数名进行重命名会破坏ng的DI机制。所以若不进行特殊的处理，minify后的代码在执行时肯定会报错的，提示找不到服务。

不过由于minify不能修改字符串，所以利用这点，ng的处理方法就是用数组代替函数，如：
```js
angular.module('com.ngnice.app').controller('TestController', ['$http', '$timeout', function($http, $timeout){
  ...
}]);
```
也就是说，数组的最后一个元素是函数，前面都是字符串格式的服务名，同时函数的参数与这些服务名一一对应。

另外一种解决的方式是使用annotation（注解）, 如下代码需要依赖$http和$route：
```js
var MyController = function(obfuscatedScope, obfuscatedRoute){
   //...
};

// 给MyController函数添加一个$inject属性，一个数组，指定了需要被注入的对象
MyController['$inject'] = ['$scope', '$route'];
```

上述两种方式都又一个麻烦的地方，那就是当依赖（被注入的对象）改变时要同时修改两个地方。

不过，在实际的实现种，ng提供了对此进行处理的工具，ngAnnotate(原ngMin)，它的作用就是找到代码种的controller的定义，然后将它修改为annotation的形式。所以一般ng项目在build过程中都会先调用ngAnnotate，然后在minify。