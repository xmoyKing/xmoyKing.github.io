---
title: AngularJS入门笔记-1-知识点
categories:
  - AngularJS
tags:
  - AngularJS
  - JavaScript
date: 2017-04-02 09:13:07
updated:
---

本文将持续更新AngularJS相关的知识点或文字，作为入门笔记的总汇。

---
> 真正的ng专家不仅仅是指对诸多技巧了然于胸，能够自如运用到项目中——只要熟悉就够了。
真正的专家需要从大处着手，挖掘这门技术背后隐含的设计思想和哲学，即：需知其所以然，又不偏废细节，锱铢必较每个变量函数的命名格式。使代码臻于完美，并从中提炼出能够推而广之的最佳实践。

> MMVM模式的要点是：以领域Model为中心，遵循“分离关注点”设计原则，这是ng的模型驱动思维与jq的DOM驱动思维的显著差异，所以做ng开发时切忌：
1. 绝不先设计页面，然后用DOM操作改变它
2. 指令不是封装jq代码的“天堂”
---
关于1.x / 2.x / 3.x / 4.x等ng的版本的问题？到底该如何选择？
后续的2,3,4与1.x是不兼容的，语法改变了，甚至部分底层实现完全改变，1.x后的版本使用TypeScript和ES6作为主体语言，同时也抛弃了IE，除了IE11部分支持。
若不确定是否一定能抛弃IE浏览器之前，可以先学习1.x, 同时实际使用中大部分还是1.x， 而1.x也依然会继续维护，所以学习后续版本之前学习1.x是可以的，因为即使语法不同，实现不同，但是编程模型没有太大差异，都是基于MVVM模型，有双向绑定，有相同的设计哲学——利用高内聚的小模块组合最终的程序。

---

[AngularJS知识库](http://lib.csdn.net/base/23?source=blogtop)*一个以ng为中心的知识点集合*

[跟我学AngularJs:Directive指令用法解读（上）](http://blog.csdn.net/evankaka/article/details/51232895) *博客内还有下篇链接*

[AngularJS中的Provider们：Service和Factory等的区别](https://segmentfault.com/a/1190000003096933)

### AngularJS权威教程 刷第一遍, 翻译的不好，不通顺，而且小错误也挺多

从第8章开始遇到的一些不太清楚的指令或函数：

$injector [AngularJS API之$injector ---- 依赖注入](http://www.cnblogs.com/xing901022/p/4941166.html)


[（三）ng-app的使用困惑和angularJS框架的自动加载](http://blog.csdn.net/aitangyong/article/details/39694579)*关于ng-app的加载问题，后面还有4，5，6*
*第7个中的声明依赖的方式没看懂*

$rootScope [(九)通过几段代码，理清angularJS中的$injector、$rootScope和$scope的概念和关联关系](http://blog.csdn.net/aitangyong/article/details/40267583)*系列博客，博客中对angular，做了类比java web中的一些概念*

[(十一)通过AngularJS的ng-repeat指令看scope的继承关系](http://blog.csdn.net/aitangyong/article/details/44086137)
关于js中的引用和基本传值的问题：
```js
var obj = {"name":"aty"};

wrongChangeName(obj);
alert(obj.name);//仍然是aty

rightChangeName(obj);
alert(obj.name);//hehe

function rightChangeName(obj)
{
    obj.name="hehe";
}

function wrongChangeName(obj)
{
    obj = {"name":"hehe"};
}
```
在其他作用域下修改一个对象，应该通过属性的方式修改而不是使用新的对象字面量覆盖

$apply
$scope.$digest() [(十五)在controller之外修改$scope中的数据，双向绑定特性失效，不能自动刷新](http://blog.csdn.net/aitangyong/article/details/45092271)*手动触发digest循环检测脏值*

[ (十八)AngularJS中模块bootstrap后,动态注册新的controller](http://blog.csdn.net/aitangyong/article/details/48135961)*18，19都没懂*

[Think in AngularJS：对比jQuery和AngularJS的不同思维模式](http://damoqiongqiu.iteye.com/blog/1926475)

compile
link
postLink
[AngularJS指令中的compile与link函数详解](http://www.jb51.net/article/58229.htm)

config
run [ AngularJS模块详解](http://blog.csdn.net/woxueliuyun/article/details/50962645)*涉及ng的内部实现原理，没懂*

$watch
$apply [理解$watch ，$apply 和 $digest --- 理解数据绑定过程](http://www.AngularJS.cn/A0a6)*很清晰很好懂，例子也很适合*

$evalAsync
$applyAsync [[AngularJS面面观] 5. scope中的两个异步方法 - $applyAsync以及$evalAsync](http://blog.csdn.net/dm_vincent/article/details/51607018)*源码分析，没懂*
            [[译]AngularJS $apply, $digest, 和$evalAsync的比较](http://www.cnblogs.com/wancy86/p/ng-digset.html)

$parse [ 浅谈AngularJS的$parse服务 这篇可以让你看明白](http://blog.csdn.net/feiying008/article/details/50222829)


$cacheFactory [AngularJs $cacheFactory 缓存服务](http://www.cnblogs.com/ys-ys/p/4967404.html?utm_source=tuicool&utm_medium=referral)

$q [AngularJS系列之轻松使用$q进行异步编程](http://www.cnblogs.com/fliu/articles/5288531.html)*在没有promise的基础上不是很懂，应该需要先对promise有一定基础才行*
  promise [JavaScript Promise迷你书（中文版）](http://liubin.org/promises-book/)

$resource [angular $resource模块](http://blog.csdn.net/yangnianbing110/article/details/43163155)
  Restangular [github: mgonto/restangular](https://github.com/mgonto/restangular) *基于ng，rest风格，promise返回结果，文档全英文，有机会翻译一波*

withCredentials *有关于跨域问题的request头，具体设置根据库或使用场景不同*

X-Request-With [AngularJS与服务器交互](http://bijian1013.iteye.com/blog/2111328) *这篇文章只是涉及到一点X-Request-With*

$render
$setViewValue [浅谈Angular中ngModel的$render](http://www.jb51.net/article/95507.htm)

XSFR令牌

12章之前的一些章节看的不是很明白，有很多超前的知识点或没有解释清楚的概念

18,19章没有看,后面的章节在特定情况下还是很有价值的，但是目前初学ng的情况下没有必要深入。

### AngularJS深度剖析与最佳实践
ngRoute一般用法：
```js
$routeProvider.when('/url',{
  templateUrl: 'path/to/template.html',
  controller: 'SomeCtrl',
});
```
工作原理：
监听$locationChangeSuccess事件，每次URL（包括hash部分）发生变化时触发，更新$routeProvider/$stateProvider中注册的路由表中的URL部分。

#### 总是用ng-model作为输出
在写指令时，有三种方式可以输出操作结果：
1. 写回调函数，当有需要输出的内容时调用，并传入结果
2. 传入哈希对象，然后对它的属性赋值
3. 依赖ngModel指令，并传入值

以分页控件为例，回调函数的方式最直接，缺点是使用不方便，必须在控制器中写一个回调函数来接收结果，并赋值给一个内部变量。

哈希对象的方式，`<pagination page="page">`在指令中，可以对page.index进行赋值，于是传入者的page对象的index属性被修改了。有一些问题：
首先，不能直观的预料到这个指令会怎么进行输出，page属性没有任何特别之处，其他指令也多半不会使用。
其次，page应该有一个范围限制，无法进行校验，除非给page新填一个属性。
最后，若使用者想在其变化时做一些操作，则不得不绑定一个回调函数的属性。

以上的问题通过ngModel都可以解决：
首先，ngModel作为输出的标准方式，一看就知道指令要输出什么。
其次，ngModel有一系列的错误校验机制，可对ngModel的内容进行校验，其实用单独的指令来校验结果更好，能让指令的职责更单一，更内聚。
最后，ngModel有标准的通知时间，也就是ng-change指令，当屡次在input指令上看到ng-change指令时，也需要会认为这个input的通知事件，但实际收ngModel的通知事件，换句话说，有ngMode的地方就可以使用ng-change事件。
```js
directive('pagination', function(){
  return {
    restrict: 'E',
    require: 'ngModel',
    link: function(scope, elm, iAttrs, ngModeController){
      // ...
      someEvent(function(index){ // 当某些事件触发时改变ngModel的值
        ngModelController.$setViewValue(index);
      });
      // ...
    }
  };
});
```

#### 打包代替动态加载
前端有一个非常著名的库叫require.js,用于动态加载js文件，曾经让非常多的人着迷。但是其初衷并不是仅仅动态加载。而是在于模块化，用于弥补js语言的一些缺陷。

不过ng自己内置了模块化系统，所以require.js就不是必须的了。当有一些第三方库很大，确实需要动态加载，则进行局部化的动态加载，比如Highchart等插件，定义一个Highchart指令，当它首次使用时才动态加载highchart.js，加载完毕后调用其中的函数。这样能让整体代码尽量简化同时加快启动速度。


#### 在非独立作用域指令中实现scope绑定
假设有一个指令的使用形式如下：
HTML:
```html
<some-directive name="1+1" value="1+1" on-event="vm.test(age)"></some-directive>
```
自定义指令通过scope表达式进行绑定，JS:
```js
directive('someDirective', function(){
  return {
    // ...
    scope: {
      name: '@', // 绑定字面量，把值作为字符串进行解释
      value: '=', // 绑定变量，把值作为scope上的表达式进行解释
      onEvent: '&', // 绑定事件，调用方式为on-event(value)或on-event($event, value)
    }
  };
});
```
上述自定义指令很简单，但有一个问题，`scope:{}`的形式让这个指令自动具有了独立作用域，而这将导致无法在同一个原生上使用其他需要作用域的指令。

对于装饰器型指令来说，解决的方法时不适用scope绑定表达式，而自己实现类似的效果。

实现绑定name属性非常简单，attrs.name,因为是字面量，只需要通过attrs直接获取字符串即可，结果为1+1。

实现绑定value属性需要一个函数的帮助，`scope.$eval(attrs.value)`, 它会在指令的当前作用域上计算value对于的表达式，结果为2。

实现绑定event属性，表达式如下：`scope.$eval(attrs.onEvent, {$event:event, age:30})`。理解这个表达式需要明白：
scope.$evel是一个函数，它可以接受2个参数，第一个是要计算的表达式，第二个是计算时可访问的上下文对象。比如在使用on-event(age) / on-event($event, age) / on-event(age, $event)等方式调用时，除了可以使用scope中的变量外，还可以访问$event和age这两个变量。
然后，ng在这个scope上把onEvent的值vm.test(age)作为一个表达式进行解释，这个表达式的参数时一个叫age的变量，于是ng就从scope和额外变量上找名为age的属性作为参数传入，结果时vm.test函数所接收到的参数为30
