---
title: AngularJS巩固实践-30-服务复习
categories:
  - AngularJS
tags:
  - AngularJS
  - JavaScript
date: 2017-07-25 22:00:40
updated:
---

服务是对公共代码的抽象，比如，如果在多个控制器中都出现了相似的代码，那么把它们提取出来，封装成一个服务，你将更加遵循 DRY 原则（即：不要重复你自己），在可维护性等方面获得提升。由于服务剥离了和具体表现相关的部分，而聚焦于业务逻辑或交互逻辑，它更加容易被测试和复用。

引入服务的首要目的是为了优化代码结构，而不是复用。复用只是一项结果，不是目标。所以，当发现代码中混杂了表现层逻辑和业务层逻辑的时候，就要认真考虑抽取服务了 — 哪怕它还看不到复用价值。

服务的概念通常是和依赖注入紧密相关的，Angular 中也一样。由于依赖注入的要求，服务都是单例的，这样我们才能把它们到处注入，而不用手动管理它们的生命周期，并容许Angular 实现“延迟初始化”等优化措施。

在 Angular 中，服务分成很多种类型：
- 常量（Constant）：用于声明不会被修改的值。
- 变量（Value）：用于声明会被修改的值。
- 服务（Service）：没错，它跟服务这个大概念同名，原作者在“开发者指南”中把这种行为比喻为“把自己的孩子取名叫‘孩子’ — 一个会气疯老师的名字”。事实上，同名的原因是 — 它跟后端领域的“服务”实现方式最为相似：声明一个类，等待Angular 把它 new 出来，然后保存这个实例，供它到处注入。
- 工厂（Factory）：它跟上面这个“服务”不同，它不会被 new 出来，Angular 会调用这个函数，获得返回值，然后保存这个返回值，供它到处注入。它被取名为“工厂”是因为：它本身不会被用于注入，我们使用的是它的产品。但是与现实中的工厂不同，它只产出一份产品，我们只是到处使用这个产品而已。
- 供应商（Provider）：“工厂”只负责生产产品，它的规格是不受我们控制的，而“供应商”更加灵活，我们可以对规格进行配置，以便获得定制化的产品。

事实上，除了 Constan- 外，所有这些类型的服务，背后都是通过 Provider 实现的，可以把它们看做让 Provider 更容易写的语法糖。一个明显的佐证是：当你使用一个未定义的服务时，Angular 给出的错误提示是它对应的 Provider 未找到，比如使用一个未定义的服务：test，那么 Angular 给出的提示是：Unknown provider: testProvider <- test。

Provider 的声明方式如下：
```js
angular.module('com.ngnice.app').provider('greeting', function() {
  var _name = 'world';
  this.setName = function(name) {
    _name = name;
  };
  this.$ge- = function(/*这里可以放依赖注入变量*/) {
    return 'Hello, ' + _name;
  };
});
```
使用时：
```js
angular.module('com.ngnice.app').controller('SomeCtrl', function($scope, greeting) {
  // 这里greeting应该等于'Hello, world'，怎么样，你猜对了吗？
  $scope.message = greeting;
})
```
对 Provider 进行配置时：
```js
angular.module('com.ngnice.app').config(function(greetingProvider) {
  greetingProvider.setName('wolf');
});
```
容器的伪代码如下：
```js
var instance = diContainer['greeting']; // 先找是否已经有了一个实例
if (!angular.isUndefined(instance)) {
  return instance; // 如果已经有了一个实例，直接返回
}
var ProviderClass = angular.module('com.ngnice.app').lookup('greetingProvider');
// 在服务名后面自动加上Provider后缀是Angular遵循的一项约定
var provider = new ProviderClass(); // 把Provider实例化
provider.setName('wolf');
instance = provider.$get(); // 调用$get，并传入依赖注入参数
diContainer['greeting'] = instance; // 把调用结果存下来
return instance;
```
事实上，如果不需要对 name 参数进行配置，声明代码可以简化为：
```js
angular.module('com.ngnice.app').value('greeting', 'Hello, world');
```
这也就是需要这么多语法糖的原因。

服务的等价形式：
```js
angular.module('com.ngnice.app').service('greeting', function() {
  this.sayHello = function(name) {
    return 'Hello, ' + name;
  };
});
```
等价于：
```js
angular.module('com.ngnice.app').provider('greeting', function() {
  this.$ge- = function() {
    var Greeting = function() {
      this.sayHello = function(name) {
        return 'Hello, ' + name;
      };
    };
    return new Greeting();
  };
};
```
使用时：
```js
angular.module('com.ngnice.app').controller('SomeCtrl', function($scope, greeting) {
  $scope.message = greeting.sayHello('world');
});
```

工厂服务等价形式：
```js
angular.module('com.ngnice.app').factory('greeting', function() {
  return 'Hello, world';
});
```
等价于：
```js
angular.module('com.ngnice.app').provider('greeting', function() {
  this.$ge- = function() {
  var greeting = function() {
    return 'Hello, world';
  };
  return greeting();
}
});
```
使用时：
```js
angular.module('com.ngnice.app').controller('SomeCtrl', function($scope, greeting) {
  $scope.message = greeting;
});
```

在 Angular 源码中，它们的实现是这样的：
```js
function factory(name, factoryFn) {
  return provider(name, { $get: factoryFn });
}
function service(name, constructor) {
  return factory(name, ['$injector', function($injector) {
    return $injector.instantiate(constructor);
  }]);
}
function value(name, val) {
  return factory(name, valueFn(val));
}
```

Angular 提供了这么多种形式的服务，那么在实践中该如何选择？可以遵循下列决策流程：
- 需要全局的可配置参数？用 Provider。
- 是纯数据，没有行为？用 Value。
- 只 new 一次，不用参数？用 Service。
- 拿到类，我自己 new 出实例？用 Factory。
- 拿到函数，我自己调用？用 Factory。

但是，还有另一种更方便的方式：
- 是纯数据时，先用 Value ；当发现需要添加行为时，改写为 Service ；或当发现需要通过计算给出结果时，改写为 Factory ；当发现需要进行全局配置时，改写为Provider。
- 最酷的是，这个过程对于使用者是透明的 — 它不需要因为实现代码的改动而更改原有代码。如上面 Value 和 Factory 的使用代码，仅仅从使用代码中分不出它是 Value 还是 Factory。

与其他 Service 不同，Constan- 不是 Provider 函数的语法糖。更重要的差别是，它的初始化时机非常早，可以在 `angular.module('com.ngnice.app').config` 函数中使用，而其他的服务是不能被注入到 config 函数中的。这也意味着，如果你需要在 config 中使用一个全局配置项，那么它就只能声明为常量，而不能声明为变量。
```
类　　型                Factory   Service   Value   Constan-  Provider
可以依赖其他服务            是       是       否       否       是
使用类型友好的注入           否       是       是       是       否
在 config 阶段可用           否       否       否       是       是
可用于创建函数 / 原生对象      是       否       是       是       是
```
- 可以依赖其他服务：由于 Value 和 Constan- 的特殊声明形式，显然没有进行依赖注入的时机。
- 使用类型友好的注入：这条没有官方的解释，可以理解为 — 由于 Factory 可以根据程序逻辑返回不同的数据类型，所以我们无法推断其结果是什么类型，也就是对类型不够友好。Provider 由于其灵活性比 Factory 更高，因此在类型友好性上和Factory 是一样的。
- 在 config 阶段可用：只有 Constan- 和 Provider 类型在 config 阶段可用，其他都是Provider 实例化之后的结果，所以只有 config 阶段完成后才可用。
- 可用于创建函数 / 原生对象：由于 Service 是 new 出来的，所以其结果必然是类实例，也就无法直接返回一个可供调用的函数或数字等原生对象。

如果确实需要对一个没有提供 Provider 的第三方服务进行配置，该怎么办呢？Angular 提供了另一种机制：decorator。这个 decorator 和前面提到过的装饰器型指令没有关系，它是用来改变服务的行为的。

比如有一个第三方服务，名叫 ui，它有一个 prompt 函数，我们不能改它源码，但需要让它每次弹出提问框时都在控制台输出一条记录，那么我们可以这样写：
```js
angular.module('com.ngnice.app').config(function($provide) {
  // $delegate是ui的原始服务
  $provide.decorator('ui', function($delegate) {
    // 保存原始的prompt函数
    var originalPrompt = $delegate.prompt;
    // 用自己的prompt替换
    $delegate.prompt = function() {
      // 先执行原始的prompt函数
      originalPrompt.apply($delegate, arguments);
      // 再写一条控制台日志
      console.log('prompt');
    };
    // 返回原始服务的实例，但也可以返回一个全新的实例
    return $delegate;
  })
});
```
这种方式超级灵活，可以改写包括 Angular 系统服务在内的任何服务 — 事实上，angular-mocks 模块就是使用 decorator 来 MOCK $httpBackend、$timeout 等服务的。

不过，如果大幅修改了原始服务的逻辑，那么，这可能会给自己和维护者挖坑。俗话说，“不作死就不会死”。如果总结 decorator 的使用原则，那就是 — 慎用、慎用、慎用，如果确实想用，请务必遵循“ Liskov 代换”原则，并写好单元测试。特别是，如果想修改系统服务的工作逻辑，建议先多看几遍文档，确保正确理解了它的每一个细节！