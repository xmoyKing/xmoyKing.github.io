---
title: AngularJS入门笔记-5-模块和依赖注入
categories:
  - AngularJS
tags:
  - AngularJS
  - JavaScript
date: 2017-04-30 22:58:43
updated: 2017-04-30 22:58:43
---

AngularJS最需要重点了解的是依赖注入，以及依赖注入和模块的联系。
依赖注入使AngularJS模块保持一个非常整洁、有组织的形式，并更容易访问其他模块，当正确使用的时候，能减少很多工作量。

## AngularJS模块
AngularJS模块是一种容器，它使代码隔离并组织成简洁、可复用的块。模块本身不提供直接的功能，但它包含其他提供了功能的对象的实例，如控制器、过滤器、服务、动画等。

通过定义AngularJS提供的对象来构建模块，然后通过依赖注入将这些模块连接在一起，构成一个完整的项目。

AngularJS建立在模块之上，大部分由AngularJS提供的功能都内置到一个ng模块中，包含了大部分的指令和服务。

同时，模块对象也有一些初始的方法：
- filter(name, factory) 创建一个对数据进行格式化的过滤器（多用在视图中，也可通过$filter(name)获取）
- controller(name, constructor) 创建一个控制器
- directive(name, factory) 创建一个指令，可以看作是对HTML的扩展
- value(name, value) 定义一个常量的服务
- constant(key, value) 定义一个常量的服务，与value相互区别
- factory(name, provider) 创建一个服务，
- service(name, constructor) 创建一个服务
- provider(name, type) 注意provider、factory、service三者相互区别
- run(callback) 注册一个在AngularJS加载完后对所有模块进行配置的函数
- config(callback) 注册一个在模块加载时对该模块进行配置的函数
- name 返回模块名称
- animation(name, factory) 支持动画

## 依赖注入
依赖注入是许多服务器端语言的设计模式的一种，AngularJS依赖注入的思想是定义依赖对象并动态的将它注入另一个对象，使所有的依赖对象所提供的功能都可用，

理解依赖注入先从理解其是要解决什么问题开始，一个AngularJS应用中的一些组件将会依赖于其他组件。比如：控制器需要使用$scope组件，向视图传递数据，所以控制器依赖于$scope组件。

依赖注入简化了组件之间处理依赖的过程（即解决依赖），没有依赖注入，就不得不以某种方式自己查找$scope，很有可能使用全局变量，虽然也可以完成同样工作，但是没有使用依赖注入这么方便易用（不考虑依赖注入的实现和原理）

依赖注入改变了函数参数的作用，AngularJS应用中一个组件通过在工厂函数的参数上声明依赖.没有依赖注入，参数会被用于接收调用者传入的任何对象(无法保证该对象具有我们想要的方法和属性)，但有了依赖注入，函数使用参数提出依赖需求，AngularJS自动为你找到这个组件。

同时AngularJS的依赖注入工作方式允许你任意声明依赖的顺序，即以下两种声明依赖的方式作用和结果一样。
```js
myApp.controller('myAppCtrl', function($scope, $filter){ ... })

myApp.controller('myAppCtrl', function($filter,$scope){ ... })
```

AngularJS通过provider和injector这两种服务实现依赖注入。

provider本质上是一个对象，这个对象定义了如何创建一个具有所有必要功能的对象实例的方式，一个模块将provider注册到injector的服务中，然后AngularJS中，一个provider对象只创建一个实例，即provider为单例模式。

injector则负责跟踪provider对象的实例，只要一个模块注册了provider，就会创建一个injector实例，当一个provider对象发出一个依赖的请求时，injector就会先检查injecotr的缓存，查看是否该实例已经存在,若在则直接使用，若没有在缓存中找到则使用provider的定义创建一个新的实例，并将其缓存起来，以备后用，最后将实例返回。


## 创建模块
创建一个AngularJS模块是非常简单的过程，使用`AngularJS.module(name, [require], [configFn])`方法即可，这个方法创建一个Module对象的实例，将它注册到injector中，然后返回这个新建的Module实例，比如：
```js
var myModule = AngularJS.mudole('myModule', ['$window', '$http'], function(){
  $provide.value('myValue', 'Some value');
})
```
若不指定require，则module方法会作为getter返回实例。
```js
var myModuleInstance = AngularJS.module('myModule');
```

当一个模块被定义时，执行AngularJS模块的配置阶段，在此阶段，任何provider都被注册到injector中，在模块实例对象上使用config方法配置。
```js
var myModule = AngularJS.module('myModule', [])
  .config(function($provide, $filterProvider){
    $provide.value('startTime', new Date());
    $filterProvider.register('myFilter', function(){});
  });
```

一旦配置完成，则到了AngularJS模块的运行阶段，在此阶段可实现实话模块所需的代码。在运行时，不能实现任何provider代码，因为在此时，整个模块已经完成配置并注册到injetor中。
```js
myModule.run(function(startTime){
  startTime.setTime((new Date()).getTime());
});
```

