---
title: angularjs入门笔记-1
categories:
  - fe
tags:
  - fe
  - angularjs
date: 2017-04-02 09:13:07
updated: 2017-04-02 09:13:07
---

[跟我学AngularJs:Directive指令用法解读（上）](http://blog.csdn.net/evankaka/article/details/51232895) *博客内还有下篇链接*

[AngularJS中的Provider们：Service和Factory等的区别](https://segmentfault.com/a/1190000003096933)

### AngularJS权威教程 刷第一遍, 翻译的不好，不通顺，而且小错误也挺多

从第8章开始遇到的一些不太清楚的指令或函数：

$injector [AngularJS API之$injector ---- 依赖注入](http://www.cnblogs.com/xing901022/p/4941166.html)


[（三）ng-app的使用困惑和angularJS框架的自动加载](http://blog.csdn.net/aitangyong/article/details/39694579)*关于ng-app的加载问题，后面还有4，5，6*
*第7个中的声明依赖的方式没看懂*

$rootScope [(九)通过几段代码，理清angularJS中的$injector、$rootScope和$scope的概念和关联关系](http://blog.csdn.net/aitangyong/article/details/40267583)*系列博客，博客中对angular，做了类比java web中的一些概念*

[(十一)通过angularjs的ng-repeat指令看scope的继承关系](http://blog.csdn.net/aitangyong/article/details/44086137)
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

$scope.$digest() [(十五)在controller之外修改$scope中的数据，双向绑定特性失效，不能自动刷新](http://blog.csdn.net/aitangyong/article/details/45092271)*手动触发digest循环检测脏值*

[ (十八)angularjs中模块bootstrap后,动态注册新的controller](http://blog.csdn.net/aitangyong/article/details/48135961)*18，19都没懂*

[Think in AngularJS：对比jQuery和AngularJS的不同思维模式](http://damoqiongqiu.iteye.com/blog/1926475)

compile
link
postLink
[angularjs指令中的compile与link函数详解](http://www.jb51.net/article/58229.htm)

run [ AngularJS模块详解](http://blog.csdn.net/woxueliuyun/article/details/50962645)

$watch
$parse
$apply
XSFR令牌
$cacheFactory
config
$q
promise
$resource
Restangular
withCredentials
X-Request-With
mongolab
$evalAsync
$render
$setViewValue


12章之前的一些章节看的不是很明白，有很多超前的知识点或没有解释清楚的概念

18,19章没有看,后面的章节在特定情况下还是很有价值的，但是目前初学ng的情况下没有必要深入。

### AngularJS开发下一代Web应用 第一遍

第三章没有图片，无法理清逻辑，第四章的测试小结需要在熟悉angular练习小项目之后再重新看一遍。

使用Jasmine进行单元测试，以及用angular-mock模拟数据。