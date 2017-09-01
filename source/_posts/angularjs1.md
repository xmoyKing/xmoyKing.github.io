---
title: angularjs入门笔记-1-知识点-AngularJS权威教程
categories:
  - fe
tags:
  - fe
  - angularjs
  - links
date: 2017-04-02 09:13:07
updated: 
---

本文将持续更新angularjs相关的知识点或文字，作为入门笔记的总汇。

---
> 真正的ng专家不仅仅是指对诸多技巧了然于胸，能够自如运用到项目中——只要熟悉就够了。
真正的专家需要从大处着手，挖掘这门技术背后隐含的设计思想和哲学，即：需知其所以然，又不偏废细节，锱铢必较每个变量函数的命名格式。使代码臻于完美，并从中提炼出能够推而广之的最佳实践。

> MMVM模式的要点是：以领域Model为中心，遵循“分离关注点”设计原则，这是ng的模型驱动思维与jq的DOM驱动思维的显著差异，所以做ng开发时切忌：
1. 绝不先设计页面，然后用DOM操作改变它
2. 指令不是封装jq代码的“天堂”

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

$apply
$scope.$digest() [(十五)在controller之外修改$scope中的数据，双向绑定特性失效，不能自动刷新](http://blog.csdn.net/aitangyong/article/details/45092271)*手动触发digest循环检测脏值*

[ (十八)angularjs中模块bootstrap后,动态注册新的controller](http://blog.csdn.net/aitangyong/article/details/48135961)*18，19都没懂*

[Think in AngularJS：对比jQuery和AngularJS的不同思维模式](http://damoqiongqiu.iteye.com/blog/1926475)

compile
link
postLink
[angularjs指令中的compile与link函数详解](http://www.jb51.net/article/58229.htm)

config
run [ AngularJS模块详解](http://blog.csdn.net/woxueliuyun/article/details/50962645)*涉及ng的内部实现原理，没懂*

$watch
$apply [理解$watch ，$apply 和 $digest --- 理解数据绑定过程](http://www.angularjs.cn/A0a6)*很清晰很好懂，例子也很适合*

$evalAsync
$applyAsync [[AngularJS面面观] 5. scope中的两个异步方法 - $applyAsync以及$evalAsync](http://blog.csdn.net/dm_vincent/article/details/51607018)*源码分析，没懂*
            [[译]AngularJS $apply, $digest, 和$evalAsync的比较](http://www.cnblogs.com/wancy86/p/ng-digset.html)

$parse [ 浅谈AngularJS的$parse服务 这篇可以让你看明白](http://blog.csdn.net/feiying008/article/details/50222829)


$cacheFactory [AngularJs $cacheFactory 缓存服务](http://www.cnblogs.com/ys-ys/p/4967404.html?utm_source=tuicool&utm_medium=referral)

$q [angularjs系列之轻松使用$q进行异步编程](http://www.cnblogs.com/fliu/articles/5288531.html)*在没有promise的基础上不是很懂，应该需要先对promise有一定基础才行*
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

