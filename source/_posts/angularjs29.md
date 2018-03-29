---
title: AngularJS巩固实践-29-指令复习
categories:
  - AngularJS
tags:
  - AngularJS
  - JavaScript
  - ng指令
  - AngularJS深度剖析
date: 2017-07-22 21:41:01
updated:
---

指令是ng中非常重要的概念，相当于一个自定义的HTML元素，在ng官方文档中称其为HTML语言的DSL(特定领域语言)扩展。

指令的使用场景和作用分为两种：组件型指令(component)和装饰型指令(decorator)，这种命名的方式是angular 2.x 提出的。

组件型指令主要是为了将复杂而庞大的View分离，使得页面的View具有更强的可读性和维护性，实现“高内聚低耦合”和“分离关注点”的有效手段；而装饰器型指令则是为 DOM 添加行为，使其具有某种能力，如自动聚焦（autoFocus）、双向绑定、可点击（ngClick）、条件显示 / 隐藏（ngShow/ngHide）等能力，同时它也是链接 Model 和 View 之间的桥梁，保持 View 和 Model 的同步。在 Angular 中内置的大多数指令，是属于装饰器型指令，它们负责收集和创建 $watch，然后利用 Angular 的“脏检查机制”保持 View 的同步。

对于组件型指令和装饰器型指令的这两种区分是非常重要的，它们在写法、业务含义、适用范围等方面都有非常明显的区别，理解了它们，对于我们日常的指令开发也具有很好的指导作用。

### 组件型指令
组件型指令是一个小型的、自封装且内聚的一个独立体，它包含业务所需要显示的视图
以及交互逻辑，比如：

需要在首页放置一个登录框和一个 FAQ 列表，如果把它们都直接写在首页的视图和控制器中，那么首页的视图和控制器将会变得非常庞大，这样不利于分工协作和页面的长期维护。

这时候更好的方案应该是，把它们拆分成两个独立的内聚的指令 login-panel 和 faq-list，然后分别将 `<login-panel></login-panel>` 和 `<faq-list></faq-list>` 两个指令嵌入到首页。

注意，这里拆出这两个指令的直接目的不是为了复用，更重要的目的应该是分离 View，促进代码结构的优化，达到更好的语义化和组件化，当然对于这样独立内聚的指
令，有时还能意外地获得更好的复用性。

组件型指令应该是满足封装的自治性、内聚性的，它不应该直接引用当前页面的 DOM结构、数据等。如果存在需要的信息，则可以通过指令的属性传递或者利用后端服务接口来自我满足。如 login-panel 应该在其内部访问登录接口来实现自我的功能封装。它的Scope 应该是独立的（isolated），不需要对父作用域的结构有任何依赖，否则一旦父作用域的结构发生改变，可能它也需要相应地变更，这种封装是很脆弱的。更好的封装应该是“高内聚低耦合”的，内聚是描述组件内部实现了它所应该包含的逻辑功能，耦合则描述它和外部组件之间应该是尽量少的相互依赖。

组件型指令的写法通常是这样的：
```js
// 声明一个指令
angular.module('com.ngnice.app').directive('jobCategory', function () {
  return {
    // 可以用作HTML元素，也可以用作HTML属性
    restrict: 'EA',
    // 使用独立作用域
    scope: {
      configure: '='
    },
    // 指定模板
    templateUrl: 'components/configure/tree.html',
    // 声明指令的控制器
    controller: function JobCategoryCtrl($scope) {
      ...
    }
  };
});
```
指令中 return 的这个结果，称之为“指令定义对象”。

restrict 属性用来表示这个指令的应用方式，它的取值可以是 E（元素）、A（属性）、C（类名）、M（注释）这几个字母的任意组合，工程实践中常用的是 E、A、EA 这三个，对于 C、M 笔者并不建议使用它们。对于组件型指令来说，标准的用法是 E，但是为了兼容 IE8，通常也支持一个 A，因为 IE8 的自定义元素需要先用 document.createElement 注册，用 A 可以省去注册的麻烦。

scope 有三种取值：不指定（undefined）/false、true 或一个哈希对象。

不指定或为 false 时，表示这个指令不需要新作用域。它直接访问现有作用域上的属性或方法，也可以不访问作用域。如果同一节点上有新作用域或独立作用域指令，则直接使用它，否则直接使用父级作用域。

为 true 时，表示它需要一个新作用域，可以跟本节点上的其他新作用域指令共享作用域，如果任何指令都没有新作用域，它就会创建一个。

为哈希对象时，表示它需要一个独立的（isolated）作用域。所谓独立作用域，是指独立于父作用域，它不会从父节点自动继承任何属性，这样的话，就不会无意间引用到父节点上的属性，导致意料之外的耦合。

要注意，一个节点上如果已经出现了一个独立作用域指令，那么就不能再出现另一个独立作用域指令或者新作用域指令，否则使用 scope 的代码将无法区分两者，如果自动将两个作用域合并，又会失去“独立性”。总之，记住一句话：独立作用域指令是“排它”的。

那么哈希对象的内容呢？它表示的是属性绑定规则，如：
```js
{
  // 绑定字面量
  name: '@',
  // 绑定变量
  details: '=',
  // 绑定事件
  onUpdate: '&'
}
```
这里我们绑定了三个属性，以 `<user-details name='test' details='details' on-update='updateIt(times)'></user-details>` 为例，

name 的 值 将 被 绑 定 为 字 符 串 'test'。

details 的值不是 'details'，而是绑定到父页面 scope 上一个名为 details 的变量，当父页面 scope 的details 变量变化时，指令中的值也会随之变化 — 即使绑定到 number 等原生类型也一样。

onUpdate 绑定的则是一个回调函数，它是父页面 scope 上一个名为 updateIt 的函数。当指令代码中调用 scope.onUpdate() 的时候，父页面 scope 的 updateIt 就会被调用。当然，name 也同样可以绑定到变量，但是要通过绑定表达式的方式，比如 `<user-details name="{{name}}"></user-details>` 中，name 将会绑定到父页面 scope 中的 name 变量，并且也会同步更新。

对于组件型指令，更重要的是内容信息的展示，所以一般不涉及指令的link 函数，而应该尽量地将业务逻辑放置在 Controller 中。组件化的开发方式以及组件化的复用，是在前端开发中一直追求的一个理想目标。从最初的 iframe、jQuery UI、Extjs、jQuery easyui，我们一直在不懈地朝着组件化的方向前进。

### 装饰型指令
对于装饰器型指令，其定义方式则如下：
```js
angular.module('com.ngnice.app').directive('twTitle', function () {
  return {
    // 用作属性
    restrict: 'A',
    link: function (scope, element, attrs) {
      ...
    }
  };
});
```
装饰器型指令主要用于添加行为和保持 View 和 Model 的同步，所以它不同于组件型指令，经常需要进行 DOM 操作。其 restrict 属性通常为 A，也就是属性声明方式，这种方式更符合装饰器的语义：它并不是一个内容的主体，而是附加行为能力的连接器。

同时，由于多个装饰器很可能被用于同一个指令，包括独立作用域指令，所以装饰器型指令通常不使用新作用域或独立作用域。如果要访问绑定属性，该怎么做呢？仍然看前面的例子 `<user-details name="test" details="details" on-update="updateIt(times)"></user-details>`，假如不使用独立作用域，该如何获取这些属性的值呢？
- 对于 @ 型的绑定，可以直接通过 attrs 取到它：`attrs.name` 等价于 `name: '@'`。
- 对于 = 型的绑定，我们可以通过 scope.$eval 取到它：`scope.$eval(attrs.details)` 等价于 `details: '='`。
- 对于 & 型的绑定，由代码：`scope.$eval(attrs.onUpdate, {times: 3});`

和 = 型绑定一样，onUpdate 属性在本质上是当前 scope 上的一个表达式。特殊的地方在于，这个表达式是一个函数，$eval 发现它是函数时，就可以传一个参数表（在 Angular中称之为 locals）给它。onUpdate 表达式中可以使用的参数名和它的参数值，都来自这个参数表。

使用的时候，可以在视图中引用这个哈希对象的某个属性作为参数，比如对于刚才的定义，视图中的 on-update="updateIt(times)" 所引用的 times 变量就来自我们刚才在callback 中传入的 times 属性，而 updateIt 函数被调用时将会接收到它，参数值是 3。
```js
$scope.updateIt = function(times) {
  // 这里times的值应该是3，但是这个times不需要跟视图和指令中的名称一致，它叫什么都可以。但视图和指令中的名称必须一致
};
```

在装饰器指令中，其实还有一种细分的分支，它完全不操纵 DOM，只是对当前 scope
进行处理，如添加成员变量、函数等。代码如下：
```js
angular.module('com.ngnice.app').directive('twToggle', function () {
  return {
    restrict: 'A',
    scope: true,
    link: function(scope) {
      scope.$node = {
        folded: false,
        toggle: function() {
          this.folded = !this.folded;
        }
      };
    }
  };
});
```
使用：
```html
<ul>
  <li ng-repeat="item in items" tw-toggle="">
    <span ng-click="$node.toggle()">切换</span>
    <ul ng-if="$node.folded">
      ...
    </ul>
  </li>
</ul>
```
它的作用是在当前元素的作用域上创建一个名为 $node 的哈希对象，这个哈希对象具有一组自定义的属性和方法，可用来封装交互逻辑。上面的代码可以改进为：
```js
angular.module('com.ngnice.app').directive('twToggle', function () {
  return {
    restrict: 'A',
    scope: true,
    controller: function($scope) {
      $scope.folded = false;
      $scope.toggle = function() {
        $scope.folded = !$scope.folded;
      };
    }
  };
});
```
好处是显示指定指令控制器，而不是通过link函数隐式生成控制器。